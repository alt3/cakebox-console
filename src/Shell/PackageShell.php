<?php
namespace App\Shell;

use Cake\Console\Shell;

/**
 * Shell class for managing software installation.
 */
class PackageShell extends Shell {

/**
 * @var array containing tasks used by this shell
 */
	public $tasks = [
		'Exec'
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
		$parser->description([__('Manage Ubuntu software pacakges.')]);

		$parser->addSubcommand('add', [
			'parser' => [
				'description' => [
					__("Installs a software package from the Ubuntu Package archive.")
				],
				'arguments' => [
					'name' => ['help' => __('Name of the software package as used by `apt-get install`.'), 'required' => true]
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
		$this->out("Installing additional software package $name", 'info');
		$this->Exec->runCommand("DEBIAN_FRONTEND=noninteractive apt-get install -y $name");
		return (0);
	}

}
