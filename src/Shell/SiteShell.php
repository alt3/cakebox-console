<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Filesystem\File;
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
 * @var array Shell Tasks used by this shell.
 */
	public $tasks = [
		'Symlink',
		'Exec',
		'Template'
	];

/**
 * @var array Webserver specific settings.
 */
	public $webservers = [
		'nginx' => [
			'sites_available' => '/etc/nginx/sites-available',
			'sites_enabled' => '/etc/nginx/sites-enabled'
			]
		];

/**
 * Override /cakephp/src/Shell/Bakeshell method to disable welcome screen.
 *
 * @return void
 */
	protected function _welcome() {
	}

/**
 * Define available subcommands, arguments and options.
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
 * Create a new website by generating a virtual host file, creating a symoblic
 * link and reloading the webserver.
 *
 * @param string $url Fully Qualified Domain Name used to expose the site
 * @param string $webroot Full path to the site's webroot directory
 * @return bool
 */
	public function add($url, $webroot) {
		$this->out("Creating Nginx configuration file for $url");

		# Prevent overwriting default Cakebox site
		if ($url == 'default') {
			$this->out("Error: cannot use 'default' as <url> as this would overwrite the default Cakebox site.");
			$this->Exec->exitBashError();
		}

		# Check for existing configuration file
		$file = $this->webservers['nginx']['sites_available'] . "/" . $url;
		if (file_exists($file)) {
			if ($this->params['force'] == false) {
				$this->out("* Skipping: file already exists.");
				$this->Exec->exitBashSuccess();
			}
			$this->out("* Overwriting existing file", 1, Shell::VERBOSE);
		}

		# Render template using viewVars
		$contents = $this->Template->generate('config/vhost_nginx', [
			'url' => $url,
			'webroot' => $webroot
		]);

		# Write to the file
		$fh = new File($file, true);
		$fh->write($contents);

		# Enable site by creating symlink in sites-enabled
		$symlink = $this->webservers['nginx']['sites_enabled'] . "/" . $url;
		$this->Symlink->create($file, $symlink);

		# Reload webserver to effectuate changes
		$this->out("Reloading webserver");
		$this->Exec->runCommand("service nginx reload");

	}

/**
 * Display a list of all "available" websites, highlighting "enabled" websites
 * with an <info> tag.
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
