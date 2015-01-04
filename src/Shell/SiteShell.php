<?php
namespace App\Shell;

use App\Lib\CakeboxExecute;
use App\Lib\CakeboxInfo;
use Cake\Console\Shell;

/**
 * Shell class for managing website configuration files.
 */
class SiteShell extends AppShell
{

    /**
     * Define available subcommands, arguments and options.
     *
     * @return parser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->description([__('Manage Nginx site configuration files.')]);

        $parser->addSubcommand('add', [
            'parser' => [
                'description' => [
                __("Creates and enables an Nginx site configuration file.")
                ],
                'arguments' => [
                    'url' => [
                        'help' => __('Fully qualified domain name used to expose the site.'),
                        'required' => true
                    ],
                    'webroot' => [
                        'help' => __('Full path to the directory serving the web pages.'),
                        'required' => true
                    ]
                ],
                'options' => [
                    'force' => [
                        'short' => 'f',
                        'help' => __('Overwrite existing configuration file.'),
                        'boolean' => true
                    ]
                ]
            ]
        ]);

        $parser->addSubcommand('listall', [
            'parser' => [
                'description' => [
                 __("Lists all available nginx site configuration files.")
                ]
            ]
        ]);
        return $parser;
    }

    /**
     * Create a new website by generating a virtual host file, creating a symoblic
     * link and reloading the webserver.
     *
     * @param string $url Fully Qualified Domain Name used to expose the site.
     * @param string $webroot Full path to the site's webroot directory.
     * @return void
     */
    public function add($url, $webroot)
    {
        $this->logStart("Creating Nginx configuration file for $url");
        $execute = new CakeboxExecute();

        # Will fail on existing vhost file without --force parameter
        if ($this->params['force'] == false) {
            if ($execute->addSite($url, $webroot) == false) {
                $this->logInfo($execute->debug());
                $this->logError("Error creating site file");
                $this->exitBashError();
            }
        }

        # Option --force passed
        if ($execute->addSite($url, $webroot, true) == false) {
            $this->logInfo($execute->debug());
            $this->logError("Error creating site file");
            $this->exitBashError();
        }
        $this->logInfo("Website created successfully");
        $this->out("<info>Don't forget to update your hosts file</info>");
        $this->exitBashSuccess();
    }

    /**
     * Display a list of all "available" websites, highlighting "enabled" websites
     * with an <info> tag.
     *
     * @return void
     */
    public function listall()
    {
        $this->out('Enabled websites highlighted:');

        $siteFiles = (new CakeboxInfo)->getRichNginxFiles();
        foreach ($siteFiles as $site) {
            if ($site['enabled'] == true) {
                $this->out("  <info>" . $site['name'] . "</info>");
            } else {
                $this->out("  " . $site['name']);
            }
        }
    }
}
