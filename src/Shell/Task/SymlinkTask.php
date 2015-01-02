<?php
namespace App\Shell\Task;

use App\Shell\AppShell;
use Cake\Console\Shell;

/**
 * Task class for managing symbolic links.
 */
class SymlinkTask extends AppShell
{

    /**
     * Create a symbolic link unless it already exists.
     *
     * @param string $target Full path to the existing file.
     * @param string $link Full path where symbolic link will be created.
     * @return void
     */
    public function create($target, $link)
    {
        if ($this->exists($link)) {
            $this->logWarning("* Skipping: symbolic link $link already exist");
            return;
        }
        $this->logInfo("Creating symbolic link $link");
        symlink($target, $link);
    }

    /**
     * Check if a symbolic link already exists.
     *
     * @param string $link Full path to the file/link to check.
     * @return bool
     */
    public function exists($link)
    {
        if (is_link($link)) {
            return true;
        }
        return false;
    }
}
