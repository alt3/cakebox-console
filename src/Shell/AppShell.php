<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Log\Log;

/**
 * Application Shell class extended by all cakebox-console shells.
 */
class AppShell extends Shell
{

    public function initialize() {
        $this->_io->setLoggers(false);
        parent::initialize();
    }

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

    /**
     * Only output message to screen when script is run with --verbose argument
     *
     * @param string $message
     * @return void
     */
    public function debug($message) {
        log::debug($message);
        $this->out($message, 1, Shell::VERBOSE);
    }

    /*
     * Always output info message to screen (even when using --quiet)
     *
     * @param string $message
     * @return void
     */
    public function info($message) {
        log::info($message);
        $this->out($message, 1, Shell::QUIET);
    }

    /*
    * Always output warning message to screen (even when using --quiet)
    *
    * @param string $message
    * @return void
    */
    public function warning($message) {
        log::warning($message);
        $this->out($message, 1, Shell::QUIET);
    }

    /*
    * Always output warning message to screen (even when using --quiet)
    *
    * @param string $message
    * @return void
    */
    public function fatal($message) {
        log::warning($message);
        $this->out($message, 1, Shell::QUIET);
    }

}
