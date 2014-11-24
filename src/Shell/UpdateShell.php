<?php
namespace App\Shell;

use Cake\Console\Shell;

/**
 * Shell class for self-updating cakebox console and management website.
 */
class UpdateShell extends AppShell {

/**
 * @var array Shell Tasks used by this shell.
 */
	public $tasks = [
		'Exec'
	];

/**
 * Update cakebox-console repository and run composer update.
 *
 * @return bool
 */
	public function main() {
		$this->out("Updating cakebox console and management website");

		# Git pull cakebox-console
		$this->out("Updating repository");
		if ($this->Exec->runCommand("cd /cakebox/console; git fetch; git reset --hard origin/master", 'vagrant')) {
			$this->out("Error git pulling cakebox-console");
		}

		# Composer update cakebox-console
		$this->out("Updating composer");
		if ($this->Exec->runCommand("cd /cakebox/console; composer update --prefer-dist --no-dev", 'vagrant')) {
			$this->out("Error composer updating");
		}

		# User feedback
		$this->out("Update completed successfully");
	}

}
