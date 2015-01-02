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

    /**
     * Disconnect default loggers from consoleIO output.
     *
     * @return void
     */
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
    * Convenience function adds a "hr" splitter element to the logs to easily
    * identify various actions when reading the plain logfile.
    *
    * @param string $message
    * @return void
    */
    public function logStart($message) {
        log::debug(str_repeat("=", 80));
        $this->out($message, 1, Shell::QUIET);
        log::info($message);
    }
    
    /**
     * Only output debug message to screen when script is run with --verbose argument
     *
     * @param string $message
     * @return void
     */
    public function logDebug($message) {
        log::debug($message);
        $this->out($message, 1, Shell::VERBOSE);
    }

    /**
     * Always output info message to screen (even when using --quiet).
     *
     * @param string $message
     * @return void
     */
    public function logInfo($message) {
        log::info($message);
        $this->out($message, 1, Shell::QUIET);
    }

    /**
    * Always output warning message to screen (even when using --quiet)
    *
    * @param string $message
    * @return void
    */
    public function logWarning($message) {
        log::warning($message);
        $this->out($message, 1, Shell::QUIET);
    }

    /**
    * Always output warning message to screen (even when using --quiet)
    *
    * @param string $message
    * @return void
    */
    public function logError($message) {
        log::warning($message);
        $this->out("<error>$message</error>", 1, Shell::QUIET);
    }

}
