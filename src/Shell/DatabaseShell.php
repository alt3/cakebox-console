<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Filesystem\Folder;

/**
 * Shell class for managing databases.
 */
class DatabaseShell extends Shell {

/**
 * @var array Shell Tasks used by this shell.
 */
	public $tasks = [
		'Database'
	];

/**
 * Override /cakephp/src/Shell/Bakeshell method to disable welcome screen.
 *
 * @return void
 */
	protected function _welcome() {
	}

/**
 * @var array Database server specific settings.
 */
	public $dbservers = [
		'mysql' => [
			'sites_available' => '/etc/nginx/sites-available',
			'sites_enabled' => '/etc/nginx/sites-enabled'
			]
		];

/**
 * Define available subcommands, arguments and options.
 *
 * @return void
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser->description([__('Easily manage your Cakebox databases.')]);

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
 * Create two databases, one suffixed with '_test.
 *
 * @param string $database Name to be used for the databases
 * @return bool
 */
	public function add($database) {
		$database = $this->Database->normalizeName($database);
		$testDatabase = $database . "_test";
		$this->out("Creating databases for $database");

		# Prevent processing protected databases
		if ($database == 'information_schema') {
			$this->out("Error: cannot drop protected database '$database'.");
			return (1);
		}

		# Check for existing databases
		if ($this->Database->exists($database)) {
			if ($this->params['force'] == false) {
				$this->out("* Skipping: databases already exists. Use --force to drop.");
				exit (0);
			}
			$this->out("* Dropping existing database");
			$this->Database->drop($database);
			$this->Database->drop($testDatabase);
		}

		# Create new databases
		$this->Database->create($database);
		$this->Database->create($testDatabase);

		# Set permissions
		$this->Database->grant($database, $this->params['username'], $this->params['password']);
		$this->Database->grant($testDatabase, $this->params['username'], $this->params['password']);
	}

/**
 * Return an array list containing all databases.
 *
 * @return void
 */
	public function listall() {
		$this->out('Databases on this system:');
		$databases = $this->Database->getList();
		var_dump($databases);

		#$files = $dir->find('.*', 'sort');
		#foreach ($files as $file) {
		#	if ($this->Symlink->exists($this->webservers['nginx']['sites_enabled'] . "/$file")) {
		#		$this->out("  <info>$file</info>");
		#	} else {
		#		$this->out("  $file");
		#	}
		#}
	}

}
