<?php
namespace App\Shell;

use Cake\Console\Shell;

/**
 * Shell class for managing software installation.
 */
class PackageShell extends Shell {

/**
 * @var array Shell Tasks used by this shell.
 */
	public $tasks = [
		'Exec'
	];

/**
 * Overrides /cakephp/src/Shell/Bakeshell method to disable welcome screen.
 *
 * @return void
 */
	protected function _welcome() {
	}

/**
 * Defines available subcommands, arguments and options.
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
 * Install a software package from the Ubuntu Package archive.
 *
 * @param string $name Name of package to install  as used by `apt-get install`
 * @return bool
 */
	public function add($name) {
		$this->out("Installing additional software package $name", 'info');
		$this->Exec->runCommand("DEBIAN_FRONTEND=noninteractive apt-get install -y $name");
		return (0);
	}

}
