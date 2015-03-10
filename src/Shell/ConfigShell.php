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
}
