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
		'Database',
		'Exec'
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

		# add
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
					'password' => ['short' => 'p', 'help' => __('Password for user with localhost access.'), 'default' => 'secret'],
					'force' => ['short' => 'f', 'help' => __('Drop existing database.'), 'boolean' => true]
				]
		]]);

		# remove
		$parser->addSubcommand('remove', [
			'parser' => [
				'description' => [
					__("Drops database and related '_test' suffixed database.")
				],
				'arguments' => [
					'name' => ['help' => __('Name of database to be dropped.'), 'required' => true]
				]
		]]);

		# listall
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
		$this->out("Creating databases");

		# Prevent processing protected databases
		if ($database == 'information_schema') {
			$this->out("Error: cannot drop protected database '$database'.");
			$this->Exec->exitBashError();
		}

		# Check for existing databases
		if ($this->Database->exists($database)) {
			if ($this->params['force'] == false) {
				$this->out("* Skipping: databases already exists.");
				$this->Exec->exitBashSuccess();
			}
			$this->Database->drop($database);
		}

		# Create databases, set permissions and exit to bash with correct exit code
		$this->Database->create($database);
		$this->Database->setGrants($database, $this->params['username'], $this->params['password']);
		$this->Exec->exitBashSuccess();
	}

/**
 * Remove/drop a database and related test-database.
 *
 * @return void
 */
	public function remove($database) {

		$res = $this->Database->drop($database);
	}

/**
 * Return an array list containing all databases.
 *
 * @return void
 */
	public function listall() {
		$this->out('User databases on this system:');
		$databases = $this->Database->getDatabaseList();

		foreach ($databases as $database) {
			$this->out("  $database");
		}

	}

}
