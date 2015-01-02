<?php
namespace App\Shell\Task;

use App\Shell\AppShell;
use Cake\Console\Shell;

/**
 * Task class for managing system command shelling.
 */
class ExecTask extends AppShell
{

    /**
     * Execute a system command as root or using su with provided username.
     *
     * @param string $command Full path to the command with options and arguments.
     * @param string $username Optional sudo user used to execute the command.
     * @return int $err Exit code of executed command
     */
    public function runCommand($command, $username = "root")
    {
        if ($username == "root") {
            $command = "$command 2>&1";
        } else {
            $command = "su $username -c \"$command\" 2>&1";
        }
        $this->logDebug("Executing as $username: `$command`");

        # Execute the command, capture exit code, stdout and stderr
        $ret = exec($command , $stdout, $exitCode);
        foreach ($stdout as $line) {
            if (!empty($line)) {
                $this->logDebug(" => $line");
            }
        }

        # Return exit-code and write stdout to log if errors occured
        if ($exitCode) {
            $this->logError("Error: shelled command produced non-zero exit code ($exitCode) and error message:");
            foreach ($stdout as $line) {
                if (!empty($line)) {
                    $this->logError(" => $line");
                }
            }
            return $exitCode;
        }
        return false;
    }

    /**
     * Exit PHP script with exit code 0 to inform bash about success.
     *
     * @return void
     */
    public function exitBashSuccess()
    {
        exit (0);
    }

    /**
     * Exit PHP script with exit code 0 to inform bash about success.
     *
     * @return void
     */
    public function exitBashError()
    {
        exit (1);
    }
}
