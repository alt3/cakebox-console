<?php
namespace App\Shell;

use Cake\Console\Shell;

/**
 * Shell class for managing various personal configuration settings.
 */
class ConfigShell extends Shell {

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

		$parser->addSubcommand('git', [
			'parser' => [
				'description' => [
					__("Configures git globals user.name and user.email.")
				],
				'options' => [
					'username' => ['short' => 'u', 'help' => __('Git user.name to use globally.'), 'required' => false],
					'email' => ['short' => 'e', 'help' => __('Git user.email to use globally.'), 'required' => false]
				]
		]]);
		return $parser;
	}

/**
 * Set global git user.name and/or user.email for vagrant user.
 *
 * @return bool
 */
	public function git() {
		$this->out("Configuring git globals:");

		if (!isset($this->params['username']) && !isset($this->params['email'])) {
			$this->out ("  => Skipping: no options passed");
			$this->Exec->exitBashSuccess();
		}

		if (isset($this->params['username'])) {
			$username = $this->params['username'];
			$this->out("  => Setting user.name to $username");
			$this->Exec->runCommand("git config --global user.name $username", "vagrant");
		}

		if (isset($this->params['email'])) {
			$email = $this->params['email'];
			$this->out("  => Setting user.email to $email");
			$this->Exec->runCommand("git config --global user.email $email", "vagrant");
		}
		$this->Exec->exitBashSuccess();
	}

}
