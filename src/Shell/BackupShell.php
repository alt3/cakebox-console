<?php
namespace App\Shell;

use Cake\Console\Shell;

/**
 * Shell class for managing software updates.
 */
class BackupShell extends AppShell
{

    /**
     * Define available subcommands, arguments and options.
     *
     * @return parser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->description([__('Manage backups.')]);

        $parser->addSubcommand('database', [
            'parser' => [
                'description' => [
                    __(
                        "Creates a Percona XtraBackup full (hot) backup of your MySQL server."
                    )
                ]
            ]
        ]);
        return $parser;
    }

    /**
     * Creates a Percona XtraBackup (hot) full backup of the MySQL server
     * in the Vagrant Synced folder /cakebox/backups (inside the box).
     *
     * @return void
     */
    public function database()
    {
        $this->logStart("Creating full (hot) backup of your MySQL server");
        $this->out("Please wait... this can take a moment");

        if (!$this->Execute->backupDatabases()) {
            $this->exitBashError("Error creating backup.");
        }
        $this->exitBashSuccess("Backups created successfully.");
    }
}
