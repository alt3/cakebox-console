<?php
namespace Cake\Shell;

use Cake\Core\Configure;
use Cake\Console\Shell;

/**
* Class overriding Cake\Shell\CommandListShell used for customizing the Shell
* welcome message and limiting the list of available commands.
*/
class CommandListShell extends Shell {

/**
 * Contains tasks to load and instantiate
 *
 * @var array
 */
	public $tasks = ['Command'];

/**
 * Show custom welcome.
 *
 * @return void
 */
	public function startup() {
		$this->hr();
		$this->out(sprintf('<info>CakePHP %s Console</info>', 'v' . Configure::version()));
		$this->hr();
	}

/**
 * List only application shells in a vertical list.
 *
 * @return void
 */
	public function main() {
		$this->out("Available commands:", 2);

		$shellList = $this->Command->getShellList();
		if (empty($shellList)) {
			return;
		}

		# Remove (this) command_list shell from available application shells
		if(($key = array_search('command_list', $shellList['app'])) !== false) {
			unset($shellList['app'][$key]);
		}

		# List available application shells only (no need to show CORE)
		foreach ($shellList['app'] as $shell) {
			$this->out("  <info>cakebox $shell</info>");
		}

		# Point to --help
		$this->out();
		$this->out("To get help on a specific command, type <info>`cakebox [command] --help`</info>");
	}

}
