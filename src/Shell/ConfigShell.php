<?php
namespace App\Shell;

use Cake\Console\Shell;

/**
 * Shell class for managing various personal configuration settings.
 */
class ConfigShell extends AppShell
{

    /**
     * @var array Shell Tasks used by this shell.
     */
#    public $tasks = [
#        'Exec'
#    ];

    /**
     * Define available subcommands, arguments and options.
     *
     * @return parser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->description([__('Manage various configuration settings.')]);

        $parser->addSubcommand('update', [
            'parser' => [
                'description' => [
                    __("Update cakebox console and management website.")
                ],
            ]
        ]);

        $parser->addSubcommand('git', [
            'parser' => [
                'description' => [
                __("Configures git globals user.name and user.email.")
                ],
                'options' => [
                    'username' => ['short' => 'u', 'help' => __('Git user.name to use globally.'), 'required' => false],
                    'email' => ['short' => 'e', 'help' => __('Git user.email to use globally.'), 'required' => false]
                ]
            ]
        ]);
        return $parser;
    }

    /**
     * Set global git user.name and/or user.email for vagrant user.
     *
     * @return void
     */
    public function git()
    {
        $this->logStart("Configuring git globals");

        if (!isset($this->params['username']) && !isset($this->params['email'])) {
            $this->exitBashWarning("* Skipping: no options passed");
        }

        if (isset($this->params['username'])) {
            $username = $this->params['username'];
            $this->logInfo("* Setting git user.name to $username");
            if (!$this->execute->gitConfig("user.name", $username)) {
                $this->exitBashError("Error updating git config.");
            }
        }

        if (isset($this->params['email'])) {
            $email = $this->params['email'];
            $this->logInfo("* Setting git user.email to $email");
            if (!$this->execute->gitConfig("user.email", $email)) {
                $this->exitBashError("Error updating git config.");
            }
        }
        $this->exitBashSuccess("Git configuration updated successfully");
    }

    /**
     * Self-update cakebox-console by updating git repository first and then
     * running composer update.
     *
     * @return void
     */
    // public function update()
    // {
    //     $this->out("Updating cakebox console and management website");
    //
    //     # Git pull cakebox-console
    //     $this->out("* Updating repository");
    //     if ($this->Exec->runCommand("cd /cakebox/console; git fetch; git reset --hard origin/master", 'vagrant')) {
    //         $this->out("Error git pulling cakebox-console");
    //     }
    //
    //     # Composer update cakebox-console
    //     $this->out("* Updating composer");
    //     if ($this->Exec->runCommand("cd /cakebox/console; composer update --prefer-dist --no-dev", 'vagrant')) {
    //         $this->out("Error composer updating");
    //     }
    // }
}
