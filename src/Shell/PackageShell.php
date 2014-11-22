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
		if ($this->__installed($name)) {
			$this->out("* Skipping: package already installed.");
			$this->Exec->exitBashSuccess();
		}

		# Not installed so install
		$this->out("Please wait... installing additional software package $name.");
		$res = $this->Exec->runCommand("DEBIAN_FRONTEND=noninteractive apt-get install -y $name");
		if (!$res) {
			$this->Exec->exitBashSuccess();
		}
		$this->Exec->exitBashError();
	}

/**
 * Check if a software package is already installed.
 *
 * @param string $name Name of package to check
 * @return bool
 */
	private function __installed($name) {
		$res = $this->Exec->runCommand("dpkg -s $name");
		if ($res) {
			return false;	# package not installed
		} else {
			return true;	# pacakge already installed
		}
	}

}
