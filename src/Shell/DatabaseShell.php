<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
# use Cake\Filesystem\File;
use Cake\Filesystem\Folder;

/**
 * DatabaseShell class is used to create two MySQL databases, one suffixed with '_test'.
 *
 */
class DatabaseShell extends Shell {

/**
 * DatabaseShell uses these tasks
 */
	public $tasks = [
		'Database',
		'Exec'
	];

/**
 * var @array containing database specific settings
 */
	public $dbservers = [
		'mysql' => [
			'sites_available' => '/etc/nginx/sites-available',
			'sites_enabled' => '/etc/nginx/sites-enabled'
			]
		];

/**
 * Define `cakebox database` subcommands and their options and arguments
 *
 * @return void
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();

		$parser->addSubcommand('add', [
			'parser' => [
				'description' => [
					__("Creates two MySQL databases, one suffixed with '_test'.")
				],
				'arguments' => [
					'name' => ['help' => __('Name to be used for the databases.'), 'required' => true]
				],
				'options' => [
					'username' => ['short' => 'u', 'help' => __('Username with localhost database access.'), 'default' => 'cakebox'],
					'password' => ['short' => 'u', 'help' => __('Password for user with localhost access.'), 'default' => 'secret'],
					'force' => ['short' => 'f', 'help' => __('Drop existing database.'), 'boolean' => true]
				]
		]]);
		$parser->addSubcommand('listall', [
			'parser' => [
					'description' => [
						__("Lists all databases.")
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
	public function add($name) {
		$name = $this->Database->normalizeName($name);
		$test_name = $name . "_test";
		$this->out("Creating databases for $name");

		# Prevent processing protected databases
		if ($name == 'information_schema') {
			$this->out("Error: cannot drop protected database '$name'.");
			return (1);
		}

		# Check for existing databases
		if ($this->Database->exists($name)) {
			if ($this->params['force'] == false) {
				$this->out("* Skipping: databases already exists. Use --force to drop.");
				return (0);
			}
			$this->out("* Dropping existing database");
			$this->Database->drop($name);
			$this->Database->drop($test_name);
		}

		# Create new databases
		$this->Database->create($name);
		$this->Database->create($test_name);

		# Set permissions
		$this->Database->grant($name, $this->params['username'], $this->params['password']);
		$this->Database->grant($test_name, $this->params['username'], $this->params['password']);
	}

/**
 * listall() lists all database
 *
 * @return void
 */
	public function listall() {
		$this->out('Databases on this system:');
		$databases = $this->Database->getList();
		var_dump($databases);

#		$files = $dir->find('.*', 'sort');
#		foreach ($files as $file) {
#			if ($this->Symlink->exists($this->webservers['nginx']['sites_enabled'] . "/$file")) {
#				$this->out("  <info>$file</info>");
#			} else {
#				$this->out("  $file");
#			}
#		}
	}

}
