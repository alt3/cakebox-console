<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;

/**
 * Shell class for managing complete applications.
 */
class ApplicationShell extends Shell {

/**
 * @var array containing tasks used by this shell
 */
	public $tasks = [
		'Installer',
		'Exec',
		'Database'
	];

/**
 * _welcome() override same class in /cakephp/src/Shell/Bakeshell to disable welcome screen
 *
 * @return void
 */
	protected function _welcome() {
	}

/**
 * @var array with installer required information
 */
	public $settings = [
		'apps_dir' => '/home/vagrant/Apps',
		'cakephp2' => [
			'repository' => 'https://github.com/cakephp/cakephp.git',
			'webdir' => 'app/webroot',
			'writable_dirs' => ['app/tmp']
		],
		'cakephp3' => [
			'webdir' => 'webroot'
			]
		];

/**
 * getOptionParser() is used to define shell subcommands, arguments and options
 *
 * @return void
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser->description([__('Easily create fully working applications.')]);

		$parser->addSubcommand('add', [
			'parser' => [
				'description' => [
					__("Installs a fully working application in /home/vagrant/Apps using Nginx and MySQL.")
				],
				'arguments' => [
					'url' => ['help' => __('Fully qualified domain name used to expose the application.'), 'required' => true],
				],
				'options' => [
					'framework' => ['short' => 'f', 'help' => __('PHP framework used by the application.'), 'choices' => ['cakephp'], 'default' => 'cakephp'],
					'majorversion' => ['short' => 'm', 'help' => __('Major version of the PHP framework used by the application.'), 'choices' => ['2', '3'], 'default' => '3'],
					'template' => ['short' => 't', 'help' => __('Template used to generate the application.'), 'choices' => ['cakephp', 'friendsofcake'], 'default' => 'cakephp'],
				]
		]]);
		return $parser;
	}

/**
 * add() installs a PHP framework application using Nginx and MySQL
 *
 * @param string $url containing fqdn used to expose the application's site
 * @return bool true when errors are encoutered, false on success
 */
	public function add($url) {
		# Prevent overwriting default Cakebox site
		if ($url == 'default') {
			$this->out("Error: cannot use 'default' as <url> as this would overwrite the default Cakebox site.");
			exit (1);
		}

		# Check if the target directory meets requirements for git cloning
		#if (!$this->Exec->dirAvailable($this->settings['apps_dir'] . DS . $url)) {
		#	$this->out("Error: target directory is not empty.");
		#	exit (1);
		#}

		# Run framework/version specific installer method
		if (!$this->__runFrameworkInstaller($url, $this->params['framework'], $this->params['majorversion'], $this->params['template'])) {
			$this->out("Error: error running framework specific installer method.");
			exit (0);
		}
	}

/**
 * __runFrameworkInstaller() starts the required framework/version specific installer function.
 *
 * @param string $url containing fqdn used to expose the application's site
 * @param string $framework containing name of the framework to use (e.g. cakephp)
 * @param string $version containing major version of framework to use (e.g. 3)
 * @param string $template containing name of the template used to create the application (e.g. cakephp/friendsofcake)
 * @return bool true on success, false on errors
 */
	private function __runFrameworkInstaller($url, $framework, $version, $template) {
		switch ($framework) {
			case "cakephp":
				if ($template == 'cakephp' && $version == "3") {
					return ($this->__installCake3($url));
				}
				if ($template == 'cakephp' && $version == "2") {
					return ($this->__installCake2($url));
				}
				$this->out("Error: reached undefined cakephp installer.");
				return false;
			default:
				$this->out("Error: reached undefined framework installer.");
				return false;
		}
	}

/**
 * _installCake2() installs and configures a CakePHP 2.x application.
 *
 * @param string $url containing fqdn used to expose the application's site
 * @return bool true on success, false on errors
 */
	private function __installCake2($url) {
		$this->out("Installing CakePHP 2.x application $url");

		# Clone the repository
		$repository = $this->settings['cakephp2']['repository'];
		$targetdir = $this->settings['apps_dir'] . DS . $url;
		if ($this->Exec->Run("git clone $repository $targetdir", 'sudo')) {
			$this->out("Error git cloning $url to $targetdir");
		}

		# Clone DebugKit plugin
		$repository = 'https://github.com/cakephp/debug_kit.git';
		$pluginDir = $targetdir . DS . 'app' . DS . 'Plugin' . DS . 'DebugKit';
		if ($this->Exec->Run("git clone $repository $pluginDir", 'sudo')) {
			$this->out("Error git cloning $url to $targetdir");
		}

		# Create nginx site
		$webroot = $targetdir . DS . $this->settings['cakephp2']['webdir'];
		$this->dispatchShell("site add $url $webroot --force");

		# Create databases
		$this->dispatchShell("database add $url --force");

		# Make required folders writable
		foreach ($this->settings['cakephp2']['writable_dirs'] as $directory) {
			$this->Installer->setFolderPermissions($targetdir . DS . $directory);
		}

		# Replace salt and cipher in core.php
		$coreFile = $targetdir . DS . "app" . DS . "Config" . DS . "core.php";
		$this->Installer->setSecuritySalt($coreFile, 2);
		$this->Installer->setSecurityCipher($coreFile, 2);

		# Enable debugkit in bootstrap.php
		$bootstrapFile = $targetdir . DS . "app" . DS . "Config" . DS . "bootstrap.php";
		$fh = new File($bootstrapFile);
		$fh->append('CakePlugin::loadAll();');
		$this->out("Enabled DebugKit plugin in $bootstrapFile");

		# Create database.php config
		$dbDefault = $targetdir . DS . "app" . DS . "Config" . DS . "database.php.default";
		$dbConfig = $targetdir . DS . "app" . DS . "Config" . DS . "database.php";
		$this->Installer->createConfig($dbDefault, $dbConfig);

		# Update database.php config
		$dbName = $this->Database->normalizeName($url);
		$this->Installer->replaceConfigValue($dbConfig, 'test_database_name', $dbName . '_test');
		$this->Installer->replaceConfigValue($dbConfig, 'database_name', $dbName);
		$this->Installer->replaceConfigValue($dbConfig, 'user', 'cakebox');

		$oldPassword = "'password' => 'password'";
		$newPassword = "'password' => 'secret'";
		$this->Installer->replaceConfigValue($dbConfig, $oldPassword, $newPassword);

		return true;
	}

/**
 * _installCake3() installs and configures a CakePHP 3.x application.
 *
 * @param string $url containing fqdn used to expose the application's site
 * @return bool true on success, false on errors
 */
	private function __installCake3($url) {
		$this->out("Installing CakePHP 3.x application $url");

		# Composer install Cake3 using Application Template
		$targetdir = $this->settings['apps_dir'] . DS . $url;
		if ($this->Exec->Run("composer create-project --prefer-dist -s dev cakephp/app $targetdir", 'vagrant')) {
			$this->out("Error composer installing to $targetdir");
		}

		# Create nginx site
		$webroot = $targetdir . DS . $this->settings['cakephp3']['webdir'];
		$this->dispatchShell("site add $url $webroot --force");

		# Create databases
		$this->dispatchShell("database add $url --force");

		# Update database settings is app.php
		$dbName = $this->Database->normalizeName($url);
		$appConfig = $targetdir . DS . "config" . DS . "app.php";

		$oldUser = "'username' => 'my_app'";
		$newUser = "'username' => 'cakebox'";
		$this->Installer->replaceConfigValue($appConfig, $oldUser, $newUser);
		$this->Installer->replaceConfigValue($appConfig, 'test_myapp', $dbName . '_test');

		$oldDatabase = "'database' => 'my_app'";
		$newDatabase = "'database' => '$dbName'";
		$this->Installer->replaceConfigValue($appConfig, $oldDatabase, $newDatabase);

		return true;
	}

}
