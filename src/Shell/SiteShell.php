<?php
namespace App\Shell;

use Cake\Console\Shell;
# use Cake\Filesystem\File;
use Cake\Filesystem\Folder;

/**
 * Shell class for managing website configuration files.
 *
 * NOTE: not using File class right now due to error "Call to undefined method
 * App\Shell\SiteShell::clearStatCache() in
 * /cakebox/commands/vendor/cakephp/cakephp/src/Filesystem/File.php on line 403.
 */
class SiteShell extends Shell {

/**
 * @var array containing tasks used by this shell
 */
	public $tasks = [
		'Symlink',
		'Exec',
		'Template'
	];

/**
 * var @array containing webserver specific settings
 */
	public $webservers = [
		'nginx' => [
			'sites_available' => '/etc/nginx/sites-available',
			'sites_enabled' => '/etc/nginx/sites-enabled'
			]
		];

/**
 * _welcome() overrides the identical function found in core class /cakephp/src/Shell/Bakeshell
 * and is used to disable the welcome screen.
 *
 * @return void
 */
	protected function _welcome() {
	}

/**
 * getOptionParser() is used to define shell subcommands, arguments and options
 *
 * @return void
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser->description([__('Manage Nginx site configuration files.')]);

		$parser->addSubcommand('add', [
			'parser' => [
				'description' => [
					__("Creates and enables an Nginx site configuration file.")
				],
				'arguments' => [
					'url' => ['help' => __('Fully qualified domain name used to expose the site.'), 'required' => true],
					'webroot' => ['help' => __('Full path to the directory serving the web pages.'), 'required' => true]
				],
				'options' => [
					'force' => ['short' => 'f', 'help' => __('Overwrite existing configuration file.'), 'boolean' => true]
				]
		]]);

		$parser->addSubcommand('listall', [
			'parser' => [
					'description' => [
						__("Lists all available nginx site configuration files.")
					]
		]]);

		return $parser;
	}

/**
 * add() generates, enables and loads a site configuration file.
 *
 * @param string $url containing fqdn used to expose the site
 * @param string $webroot containing full path to site's webroot directory
 * @return bool false on success, true when errors are encountered
 */
	public function add($url, $webroot) {
		$this->out("Creating site configuration file:");

		# Prevent overwriting default Cakebox site
		if ($url == 'default') {
			$this->out("Error: cannot use 'default' as <url> as this would overwrite the default Cakebox site.");
			return (1);
		}

		# Check for existing configuration file
		$file = $this->webservers['nginx']['sites_available'] . "/" . $url;
		if (file_exists($file)) {
			if ($this->params['force'] == false) {
				$this->out("* Skipping: $file already exists. Use --force to overwrite.");
				exit (0);
			}
			$this->out("* Overwriting existing file");
		}

		# Set viewVars for the template
		$this->Template->set([
			'url' => $url,
			'webroot' => $webroot
			]);

		# Write generated template to file
		$contents = $this->Template->generate('config', 'vhost_nginx');
		$this->createFile($file, $contents);

		# Enable site by creating symlink in sites-enabled
		$this->out("Enabling site");
		$symlink = $this->webservers['nginx']['sites_enabled'] . "/" . $url;
		$this->Symlink->create($file, $symlink);

		# Reload webserver to effectuate changes
		$this->out("Reloading webserver");
		$this->Exec->run("service nginx reload");
	}

/**
 * listall() returns a list of all "available" site configuration files, enclosing
 * "enabled" sites with an <info> tag,
 *
 * @return void
 */
	public function listall() {
		$this->out('Enabled nginx sites are highlighted:');
		$dir = new Folder($this->webservers['nginx']['sites_available']);
		$files = $dir->find('.*', 'sort');
		foreach ($files as $file) {
			if ($this->Symlink->exists($this->webservers['nginx']['sites_enabled'] . "/$file")) {
				$this->out("  <info>$file</info>");
			} else {
				$this->out("  $file");
			}
		}
	}

}
