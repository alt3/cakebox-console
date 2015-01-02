<?php
namespace App\Shell\Task;

use App\Shell\AppShell;

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
        $this->out("Executing system command as $username", 1, Shell::VERBOSE);
        if ($username == "root") {
            $command = "$command 2>&1";
        } else {
            $command = "su $username -c \"$command\" 2>&1";
        }
        $this->out(" => $command", 1, Shell::VERBOSE);

     # Execute the command, capture exit code, stdout and stderr
        $ret = exec($command, $stdout, $exitCode);
        foreach ($stdout as $line) {
            if (!empty($line)) {
                $this->out(" => $line", 1, Shell::VERBOSE);
            }
        }

     # Return exit-code and write stdout to log if errors occured
        if ($exitCode) {
            $this->out("Error: Non-zero exit code ($exitCode)");
            foreach ($stdout as $line) {
                if (!empty($line)) {
                    $this->out(" => $line");
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
