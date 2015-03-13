<?php
namespace App\Shell;

use Cake\Console\Shell;

/**
 * Shell class for managing various personal configuration settings.
 */
class ConfigShell extends AppShell
{

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

        $parser->addSubcommand('dashboard', [
            'parser' => [
                'description' => [
                __("Configures the Cakebox Dashboard.")
                ],
                'options' => [
                    'protocol' => ['help' => __('Git user.name to use globally.'), 'required' => false, 'options' => ['http', 'https']]
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
     * Displays the protocol currently used by the Dashboard website. Changes
     * the protocol to HTTP/HTTPS when using the --protocol option.
     *
     * @todo show new url after completion (should use yaml for host and ip)
     *
     * @return void
     */
    public function dashboard()
    {
        // no --protocol parameter given, display current protocol
        if (!isset($this->params['protocol'])) {
            if ($this->cbi->dashboardUsesHttps()) {
                $this->exitBashSuccess("The Cakebox Dashboard is using HTTPS");
            }
            $this->exitBashSuccess("The Cakebox Dashboard is using HTTP");
        }
        $protocol = $this->params['protocol'];

        $this->logStart("Changing Cakebox Dashboard protocol to " . $protocol);

        // do not change if protocol is already active
        if ($protocol === 'https' & $this->cbi->dashboardUsesHttps()) {
            $this->logInfo("* Skipping: website already uses HTTPS");
            $this->exitBashSuccess("Command completed successfully");
        }
        if ($protocol === 'http' & !$this->cbi->dashboardUsesHttps()) {
            $this->logInfo("* Skipping: website already uses HTTP");
            $this->exitBashSuccess("Command completed successfully");
        }

        // enable new protocol by replacing Ngixnx vhost configuration file
        if (!$this->execute->setDashboardProtocol($protocol)) {
            $this->exitBashError("Error changing protocol.");
        }
        $this->exitBashSuccess("Command completed successfully.");
    }
}
