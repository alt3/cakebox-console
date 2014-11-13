<?php
namespace App\Shell\Task;

use Cake\Console\Shell;

class SymlinkTask extends Shell {
    public function main() {

    }

    public function create($target, $link) {
        $this->out("Creating symbolic link $link");
        if ($this->exists($link)){
            $this->out("* Skipping: symlink already exist");
            return;
        }
        symlink($target, $link);
    }

    public function exists($link) {
        if (is_link($link)){
            return true;
        }
        return false;
    }

}
