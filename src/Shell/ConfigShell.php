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
		$parser->description([__('Manage various configuration settings.')]);

		$parser->addSubcommand('update', [
			'parser' => [
				'description' => [
					__("Update cakebox console and management website.")
				],
		]]);

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
		$this->out("Configuring git globals");

		if (!isset($this->params['username']) && !isset($this->params['email'])) {
			$this->out("* Skipping: no options passed");
			$this->Exec->exitBashSuccess();
		}

		if (isset($this->params['username'])) {
			$username = $this->params['username'];
			$this->out("* Setting git user.name to $username");
			$this->Exec->runCommand("git config --global user.name $username", "vagrant");
		}

		if (isset($this->params['email'])) {
			$email = $this->params['email'];
			$this->out("* Setting git user.email to $email");
			$this->Exec->runCommand("git config --global user.email $email", "vagrant");
		}
		$this->Exec->exitBashSuccess();
	}

/**
 * Update cakebox-console repository and run composer update.
 *
 * @return bool
 */
	public function update() {
		$this->out("Updating cakebox console and management website");

		# Git pull cakebox-console
		$this->out("* Updating repository");
		if ($this->Exec->runCommand("cd /cakebox/console; git fetch; git reset --hard origin/master", 'vagrant')) {
			$this->out("Error git pulling cakebox-console");
		}

		# Composer update cakebox-console
		$this->out("* Updating composer");
		if ($this->Exec->runCommand("cd /cakebox/console; composer update --prefer-dist", 'vagrant')) {
			$this->out("Error composer updating");
		}
	}

}
