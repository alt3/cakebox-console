<?php
namespace App\Shell;

use Cake\Console\Shell;

/**
 * Shell class for managing software updates.
 */
class UpdateShell extends AppShell
{

    /**
     * Define available subcommands, arguments and options.
     *
     * @return parser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->description([__('Manage updates.')]);

        $parser->addSubcommand('self', [
            'parser' => [
                'description' => [
                __("Self-updates the Cakebox Dashboard and Console commands to the most recent version")
                ]
            ]
        ]);
        return $parser;
    }

    /**
     * Self-updates the Cakebox Dashboard and Console Commands by updating the
     * Git repository and all underlying Composer libraries.
     *
     * @return void
     */
    public function self()
    {
        $this->logStart("Self-updating Cakebox Dashboard and Console Commands");
        $this->out("Please wait... this can take a moment");

        if (!$this->execute->selfUpdate()) {
            $this->exitBashError("Error updating application.");
        }
        $this->exitBashSuccess("Update completed successfully");
    }
}
