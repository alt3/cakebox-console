<?php
namespace App\Shell;

use App\Lib\CakeboxExecute;
use App\Lib\CakeboxInfo;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Log\Log;

/**
 * Application Shell class extended by all cakebox-console shells.
 */
class AppShell extends Shell
{

    /**
     * Instance of CakeboxInfo available to all Shells.
     *
     * @var \App\Lib\CakeboxInfo
     */
    protected $Info;

    /**
     * Instance of CakeboxExecute available to all Shells.
     *
     * @var \App\Lib\CakeboxExecute
     */
    protected $Execute;

    /**
     * Initialization. Used to disconnect default loggers from consoleIO output
     * and instantiating Cakebox objects.
     *
     * @return void
     */
    public function initialize()
    {
        $this->_io->setLoggers(false);
        $this->Info = new CakeboxInfo();
        $this->Execute = new CakeboxExecute();
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
     * @param string $message Message to be logged.
     * @return void
     */
    public function logStart($message)
    {
        Log::debug(str_repeat("=", 80));
        $this->out($message, 2, Shell::QUIET);
        Log::debug($message);
    }

    /**
     * Only output debug message to screen when script is run with --verbose argument
     *
     * @param string $message  Message to be logged.
     * @return void
     */
    public function logDebug($message)
    {
        log::debug($message);
        $this->out($message, 1, Shell::VERBOSE);
    }

    /**
     * Always output info message to screen (even when using --quiet).
     *
     * @param string|array $message  Message to be logged.
     * @return void
     */
    public function logInfo($message)
    {
        if (is_string($message)) {
            log::info($message);
            $this->out($message, 1, Shell::QUIET);
            return;
        }
        foreach ($message as $entry) {
            $this->out($entry, 1, Shell::QUIET);
        }
    }

    /**
     * Always output warning message to screen (even when using --quiet)
     *
     * @param string $message  Message to be logged.
     * @return void
     */
    public function logWarning($message)
    {
        log::warning($message);
        $this->out($message, 1, Shell::QUIET);
    }

    /**
     * Always output warning message to screen (even when using --quiet)
     *
     * @param string $message  Message to be logged.
     * @return void
     */
    public function logError($message = null)
    {
        if (empty($message)) {
            $message = 'Error';
        }
        log::warning($message);
        $this->out("<error>$message</error>", 1, Shell::QUIET);
        $this->out("<info>See /var/log/cakephp/cakebox.cli.log for details.</info>");
    }

    /**
     * Exit PHP script with exit code 0 to inform bash about success.
     *
     * @param string $message  Message to be logged.
     * @return void
     */
    public function exitBashSuccess($message = null)
    {
        if (count($this->Execute->debug()) != 0) {
            foreach ($this->Execute->debug() as $entry) {
                $this->out($entry, 1, Shell::VERBOSE);
            }
        }
        if ($message) {
            $this->logInfo($message);
        }
        exit (0);
    }

    /**
     * Exit PHP script with exit code 0 to inform bash about success.
     *
     * @param string $message  Message to be logged.
     * @return void
     */
    public function exitBashWarning($message)
    {
        if (count($this->Execute->debug()) != 0) {
            foreach ($this->Execute->debug() as $entry) {
                $this->out($entry, 1, Shell::VERBOSE);
            }
        }
        $this->logInfo($message);
        exit (0);
    }

    /**
     * Show most recent execute log message to user before exiting PHP script
     * with exit code 1 to inform bash about errors.
     *
     * @param string $message  Message to be logged.
     * @return void
     */
    public function exitBashError($message = null)
    {
        if (empty($message)) {
            $message = 'Error';
        }

        if (count($this->Execute->debug()) != 0) {
            foreach ($this->Execute->debug() as $entry) {
                $this->out($entry, 1, Shell::QUIET);
            }
        }
        $this->logError($message);
        exit (1);
    }
}
