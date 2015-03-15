<?php
namespace App\Shell;

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

        $parser->addSubcommand('remove', [
            'parser' => [
                'description' => [
                    __("Removes an Nginx website by deleting virtual hosts file, unlinking sites-enabled and reloading Nginx.")
                ],
                'arguments' => [
                    'url' => [
                        'help' => __('Fully qualified domain name used to expose the site.'),
                        'required' => true
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

        # Don't overwrite existing site file without --force option
        $siteFile = $this->cbi->webserverMeta['nginx']['sites-available'] . DS . $url;
        if (file_exists($siteFile) && !$this->params['force']) {
            $this->exitBashWarning("* Skipping: site already exists. Use --force to overwrite.");
        }

        # Site file either does not exist or --force option used
        if ($this->execute->addSite($url, $webroot, true) == false) {
            $this->exitBashError("Error creating site file");
        }
        $this->out("\nAdd the following line to your hosts file: <info>" . $this->cbi->getVmIpAddress() . " http://$url</info>\n");
        $this->out("Installation completed successfully");
    }

    /**
     * Remove an Nginx website by removing virtual hosts file, removing symbolic
     * link and reloading the webserver.
     *
     * @param string $url Fully Qualified Domain Name used to expose the site.
     * @return void
     */
    public function remove($url)
    {
        $this->logStart("Removing website $url");
        $siteFile = $this->cbi->webserverMeta['nginx']['sites-available'] . DS . $url;
        if (!file_exists($siteFile)) {
            $this->exitBashWarning("* Skipping: virtual host does not exist.");
        }

        if ($this->execute->removeSite($url) == false) {
            $this->exitBashError("Error removing website");
        }
        $this->exitBashSuccess("Website removed successfully.");
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
        foreach ($this->cbi->getRichNginxFiles() as $site) {
            if ($site['enabled'] == true) {
                $this->out("  <info>" . $site['name'] . "</info>");
            } else {
                $this->out("  " . $site['name']);
            }
        }
        $this->exitBashSuccess();
    }
}
