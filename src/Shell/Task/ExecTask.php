<?php
namespace App\Shell\Task;

use Cake\Console\Shell;

class ExecTask extends Shell {
    public function main() {

    }

    public function run($command, $su = false) {

        if ($su == false){
            $ret = $this->out(exec("$command", $out, $err));
        }else{
            $ret = exec("su vagrant -c \"$command\"", $out, $err);
        }

        if($err){
            $this->out("Error executing command '$command'");
            exit(1);
        }
    }

}
