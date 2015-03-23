<?php
namespace App\Shell;

use Cake\Console\Shell;

/**
 * Shell class for managing website configuration files.
 */
class VhostShell extends AppShell
{

    /**
     * Define available subcommands, arguments and options.
     *
     * @return parser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->description([__('Manage Nginx virtual hosts.')]);

        $parser->addSubcommand('add', [
            'parser' => [
                'description' => [
                __("Create and enable a virtual host.")
                ],
                'arguments' => [
                    'url' => [
                        'help' => __('Fully qualified domain name used for the virtual host.'),
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
                        'help' => __('Use to overwrite an existing virtual host configuration file.'),
                        'boolean' => true
                    ]
                ]
            ]
        ]);

        $parser->addSubcommand('remove', [
            'parser' => [
                'description' => [
                    __("Remove a virtual host by deleting virtual hosts file, unlinking sites-enabled and reloading Nginx.")
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
                 __("List all virtual hosts.")
                ]
            ]
        ]);
        return $parser;
    }

    /**
     * Create a new Nginx virtual host by generating a virtual host file,
     * creating a symoblic link and reloading the webserver.
     *
     * @param string $url Fully Qualified Domain Name used to expose the site.
     * @param string $webroot Full path to the site's webroot directory.
     * @return void
     */
    public function add($url, $webroot)
    {
        $this->logStart("Creating Nginx configuration file for $url");

        # Don't overwrite existing site file without --force option
        $vhostFile = $this->Info->webserverMeta['nginx']['sites-available'] . DS . $url;
        if (file_exists($vhostFile) && !$this->params['force']) {
            $this->exitBashWarning('* Skipping: virtual host already exists. Use --force to overwrite.');
        }

        # Site file either does not exist or --force option used
        if ($this->Execute->addVhost($url, $webroot, true) == false) {
            $this->exitBashError('Error creating virtual host configuration file');
        }
        $this->out("\nRemember to update your hosts file with: <info>" . $this->Info->getVmIpAddress() . " http://$url</info>\n");
        $this->out('Installation completed successfully');
    }

    /**
     * Completely remove an Nginx virtual host by removing virtual hosts file,
     * symbolic link and reloading the webserver.
     *
     * @param string $url Fully Qualified Domain Name used to expose the site.
     * @return void
     */
    public function remove($url)
    {
        $this->logStart("Removing website $url");
        $vhostFile = $this->Info->webserverMeta['nginx']['sites-available'] . DS . $url;
        if (!file_exists($vhostFile)) {
            $this->exitBashWarning('* Skipping: virtual host does not exist.');
        }

        if ($this->Execute->removeVhost($url) == false) {
            $this->exitBashError('Error removing virtual host');
        }
        $this->exitBashSuccess('Virtual host removed successfully.');
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
        foreach ($this->Info->getRichNginxFiles() as $site) {
            if ($site['enabled'] == true) {
                $this->out("  <info>" . $site['name'] . "</info>");
            } else {
                $this->out("  " . $site['name']);
            }
        }
        $this->exitBashSuccess();
    }
}
