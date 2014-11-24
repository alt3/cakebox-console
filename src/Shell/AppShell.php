<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;

/**
 * Application Shell class extended by all cakebox-console shells.
 */
class AppShell extends Shell {

/**
 * Override /cakephp/src/Shell/Bakeshell method to disable welcome screen.
 *
 * @return void
 */
	protected function _welcome() {
	}

}
