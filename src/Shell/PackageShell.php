<?php
namespace App\Shell;

use Cake\Console\Shell;

/**
 * PackageShell class is used to install additional software from the Ubuntu Pacakge archive.
 *
 */
class PackageShell extends Shell {

/**
 * PackagesShell uses these tasks
 */
	public $tasks = [
		'Exec'
	];

/**
 * Define `cakebox package` subcommands and their arguments and options
 *
 * @return void
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();

		$parser->addSubcommand('add', [
			'parser' => [
				'description' => [
					__("Installs additional software from the Ubuntu Package archive.")
				],
				'arguments' => [
					'name' => ['help' => __('Name of the package as used by `apt-get install`.'), 'required' => true]
				]
		]]);

		return $parser;
	}

/**
 * add() installs a package from the Ubuntu Package archive.
 *
 * @param string $name Name of the package as used by `apt-get install`.
 * @return bool false on success, true when errors are encountered
 */
	public function add($name) {
		$this->log('', 'info');
		$this->log("Installing additional software package $name", 'info');
		$this->Exec->run("DEBIAN_FRONTEND=noninteractive apt-get install -y $name");
		return (0);
	}

}
