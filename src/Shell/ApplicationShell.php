<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;

/**
 * Shell class for installing and configuring PHP framework applications.
 */
class ApplicationShell extends Shell {

/**
 * @var array Shell Tasks used by this shell.
 */
	public $tasks = [
		'Installer',
		'Exec',
		'Database'
	];

/**
 * Overrides same class in /cakephp/src/Shell/Bakeshell to disable Console
 * welcome screen.
 *
 * @return void
 */
	protected function _welcome() {
	}

/**
 * @var array Settings used by the various installer methods.
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
 * Defines Shell subcommands, arguments and options
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
 * Installs and configures a PHP framework application using Nginx and MySQL.
 *
 * @param string $url Fully Qualified Domain Name used to expose the site
 * @return bool
 */
	public function add($url) {
		# Provide (vagrant provisioning) feedback
		$this->out("Creating application $url");

		# Prevent overwriting default Cakebox site
		if ($url == 'default') {
			$this->out("Error: cannot use 'default' as <url> as this would overwrite the default Cakebox site.");
			$this->Exec->exitBashError();
		}

		# Check if the target directory meets requirements for git cloning
		# (non-existent or empty). Note: exits with success to allow vagrant
		# re-provisioning.
		$targetdir = $this->settings['apps_dir'] . DS . $url;
		if (!$this->Exec->dirAvailable($targetdir)) {
			$this->out("* Skipping: target directory $targetdir not empty.");
			$this->Exec->exitBashSuccess();
		}

		# Run framework/version specific installer method
		if (!$this->__runFrameworkInstaller($url, $this->params['framework'], $this->params['majorversion'], $this->params['template'])) {
			$this->out("Error: error running framework specific installer method.");
			$this->Exec->exitBashError();
		}

		# Provide Vagrant feedback
		$this->out("Application installation completed successfully");
		$this->Exec->exitBashSuccess();
	}

/**
 * Determines and executes framework specific installer method.
 *
 * @param string $url Fully Qualified Domain Name used to expose the site
 * @param string $framework Name of the PHP framework (e.g. cakephp, laravel)
 * @param string $version Major version of the PHP framework (e.g. 2, 3)
 * @param string $template Template to use (e.g. cakephp/friendsofcake)
 * @return bool
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
 * CakePHP 2.x installer method.
 *
 * @param string $url Fully Qualified Domain Name used to expose the site
 * @return bool
 */
	private function __installCake2($url) {
		$this->out("Installing CakePHP 2.x application $url");

		# Clone the repository
		$repository = $this->settings['cakephp2']['repository'];
		$targetdir = $this->settings['apps_dir'] . DS . $url;
		if ($this->Exec->runCommand("git clone $repository $targetdir", 'vagrant')) {
			$this->out("Error git cloning $url to $targetdir");
		}

		# Clone DebugKit plugin
		$repository = 'https://github.com/cakephp/debug_kit.git';
		$pluginDir = $targetdir . DS . 'app' . DS . 'Plugin' . DS . 'DebugKit';
		if ($this->Exec->runCommand("git clone $repository $pluginDir", 'vagrant')) {
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
 * CakePHP 3.x installer method using CakePHP Application Skeleton.
 *
 * @param string $url Fully Qualified Domain Name used to expose the site
 * @return bool
 */
	private function __installCake3($url) {
		$this->out("Installing CakePHP 3.x application $url");

		# Composer install Cake3 using Application Template
		$targetdir = $this->settings['apps_dir'] . DS . $url;
		if ($this->Exec->runCommand("composer create-project --prefer-dist -s dev cakephp/app $targetdir", 'vagrant')) {
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
