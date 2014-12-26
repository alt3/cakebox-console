<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;

/**
 * Application Shell class extended by all cakebox-console shells.
 */
class AppShell extends Shell
{

    /**
     * Override Cake\Console\Shell method to return different welcome screen.
     *
     * @return void
     */
    protected function _welcome()
    {
        $this->hr();
        $this->out(sprintf('<info>CakePHP %s Console</info>', 'v' . Configure::version()));
        $this->hr();
    }
}
