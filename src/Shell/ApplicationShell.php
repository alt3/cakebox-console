<?php
namespace App\Shell;

use App\Lib\CakeboxUtility;
use App\Lib\CakeboxFrameworkInstaller;
use Cake\Console\Shell;

/**
 * Shell class for installing and configuring PHP framework applications.
 */
class ApplicationShell extends AppShell
{

    /**
     * @var string Full path to the installation directory.
     */
    public $path;

    /**
     * Define available subcommands, arguments and options.
     *
     * @return parser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->description([__('Easily create fully working applications.')]);

        $parser->addSubcommand('add', [
            'parser' => [
                'description' => [
                    __("Installs a fully working application in /home/vagrant/Apps using Nginx and MySQL.")
                ],
                'arguments' => [
                    'url' => [
                        'help' => __('Fully qualified domain name used to expose the application.'), 'required' => true
                    ],
                ],
                'options' => [
                    'path' => [
                        'short' => 'p',
                        'help' => __('Full path to installation directory. Defaults to ~/Apps of the user sudo-executing the cakebox command.'),
                        'required' => false
                    ],
                    'framework' => [
                        'short' => 'f',
                        'help' => __('PHP framework to use for the application.'),
                        'choices' => ['cakephp', 'laravel'],
                        'default' => 'cakephp'
                    ],
                    'majorversion' => [
                        'short' => 'm',
                        'help' => __('Major CakePHP version to use for the application.'),
                        'choices' => ['2', '3'],
                        'default' => '3'
                    ],
                    'source' => [
                        'short' => 's',
                        'help' => __('Source used to provision your own application. Provide either the Github shortname to your repository (owner/repository) or the Composer package name (e.g. cakephp/app). Framework will be autodetected.'),
                        'required' => false
                    ],
                    'ssh' => [
                        'help' => __('Use SSH instead of HTTPS. Only useful in combination with out-of-the-box applications using git repositories.'),
                        'boolean' => true
                        ]
                ]
            ]
        ]);
        return $parser;
    }

    /**
     * Install and configure a PHP framework application using Nginx and MySQL.
     *
     * @param string $url Fully Qualified Domain Name used to expose the site.
     * @return void
     */
    public function add($url)
    {
        $this->logStart("Creating application http://$url");

        if ($url == 'default') {
            $this->exitBashError("Cannot use 'default' as <url> as this would overwrite the default Cakebox site.");
        }

        # Feed the installer with required information
        $installer = new CakeboxFrameworkInstaller();
        $this->out("Setting up installer");
        if (!$installer->setup(array_merge( ['url' => $url], $this->params ))) {
            $this->exitBashError("Error setting up installer.");
        }

        # Check if the target directory meets requirements for git cloning
        # (non-existent or empty). Note: exits with success here to allow
        # vagrant re-provisioning.
        if (!CakeboxUtility::dirAvailable($installer->option('path'))) {
            $this->exitBashWarning("* Skipping: target directory did not pass readiness tests.\n<info>See cakebox log for details.</info>");
        }

        # Prepare the installation
        $this->out("Preparing for installation");
        if (!$installer->prepare()) {
            $this->exitBashError("Error preparing for installation.");
        }

        # Provide some feedback
        $installerMethod = $installer->detectInstallationMethod($installer->option('source'));
        $this->out("Please wait... $installerMethod installing " . $installer->option('framework_human') . " application");

        # Install the application
        if (!$installer->install()) {
            $this->exitBashError("Error installing application.");
        }

        # Round up
        $this->out("Rounding up installation");
        if (!$installer->roundup()) {
            $this->exitBashError("Error rounding up application.");
        }

        # Provision success message
        $this->out("Finished installation using:");
        $options = $installer->options();
        ksort($options);
        foreach ($options as $key => $value) {
            $this->out("  $key => $value");
        }
        if (isset($this->params['source'])) {
            $this->out("Please note:");
            $this->out("  => Configuration files are not automatically updated for user specified applications.");
            $this->out("  => Make sure to manually update your database credentials, plugins, etc.");
        }

        $this->out("\nAdd the following line to your hosts file: <info>" . $this->cbi->getVmIpAddress() . " http://$url</info>\n");
        $this->out("Installation completed successfully");
        return true;
    }




}
