<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Filesystem\File;

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
		$this->out("Please wait... installing additional software package `$name`");

		if ($this->installed($name)) {
			$this->out("* Skipping: already installed.");
			$this->Exec->exitBashSuccess();
		}

		# Not installed so install
		$exitCode = $this->Exec->runCommand("DEBIAN_FRONTEND=noninteractive apt-get install -y $name");
		if ($exitCode) {
			$this->Exec->exitBashError();
		}
		$this->Exec->exitBashSuccess();
	}

/**
 * Check if a software package is already installed. We do not use ExecTask to
 * shell `dpkg -` or `dpkg-query -l` since those generate exit-codes/errors for
 * both non-installed and non-existing packages.
 *
 * @param string $package Name of Ubuntu package to check
 * @return bool
 */
	public function installed($package) {
		$file = "/var/lib/dpkg/info/$package.md5sums";
		if (file_exists($file)) {
			return true;
		}
		return false;
	}

}
