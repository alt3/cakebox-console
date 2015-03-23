<?php
namespace App\Shell;

use App\Lib\CakeboxFrameworkInstaller;
use App\Lib\CakeboxUtility;
use Cake\Console\Shell;
use Cake\Utility\Inflector;

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
                    __('Installs a fully working application in /home/vagrant/Apps using Nginx and MySQL.')
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
                        'help' => __('Source used to provision your own application. Provide either the Github shortname to your repository (owner/repository) or the Composer package name (e.g. cakephp/app). Framework will be autodetected.'),
                        'required' => false
                    ],
                    'webroot' => [
                        'help' => __('Webroot as to be used as your Nginx virtual host webroot directive. Required when using custom sources.'),
                        'required' => false
                    ],
                    'ssh' => [
                        'help' => __('Use SSH instead of HTTPS. Only useful in combination with out-of-the-box applications using git repositories.'),
                        'boolean' => true
                    ],
                    'repair' => [
                        'help' => __('Repair an existing installation by installing only missing sources, databases and/or virtual hosts.'),
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
        if ($this->params['repair']) {
            $this->logStart("Repairing application http://$url");
        } else {
            $this->logStart("Creating application http://$url");
        }

        if ($url == 'default') {
            $this->exitBashError("Cannot use 'default' as <url> as this would overwrite the default Cakebox site.");
        }

        # Feed the installer with all required information
        $installer = new CakeboxFrameworkInstaller();
        $this->out('Configuring installer');
        if (!$installer->setup(array_merge(['url' => $url], $this->params))) {
            $this->exitBashError('Error setting up installer.');
        }

        # Check: custom applications require the --webroot option to prevent
        # generating an invalid Nginx vhost preventing Nginx reload and thus
        # breaking yaml-provisioning
        if ($installer->option('framework_short') === 'custom' && (!isset($this->params['webroot']))) {
            $this->exitBashError('Error: custom applications require the --webroot parameter');
        }

        # Only method-detect if target dir is available once (present and/or empty)
        $targetDirAvailable = CakeboxUtility::dirAvailable($installer->option('path'));

        # Check: stop provisioning if the target directory is not
        # available/empty AND and we are not in --repair mode
        if (!$targetDirAvailable && (!$this->params['repair'])) {
            $this->exitBashError('Error: target directory ' . $installer->option('path') . ' contains data');
        }

        # ------------------------------------------------------------
        # Create installation directory if needed
        # ------------------------------------------------------------
        $dirHasData = false;
        $this->out('Creating installation directory');
        if (!$targetDirAvailable) {
            $dirHasData = true;
            $this->out('* Skipping: target directory contains data');
        }

        if ($targetDirAvailable && is_dir($installer->option('path'))) {
            $this->out('* Skipping: directory already created');
        }

        if (!$targetDirAvailable && !$dirHasData) {
            if (!$this->execute->mkVagrantDir($installer->option('path'))) {
                $this->exitBashError('Error creating target directory ' . $installer->option('path'));
            }
        }

        # ------------------------------------------------------------
        # Install sources if needed
        # ------------------------------------------------------------
        if (!$dirHasData) {
            $this->out(Inflector::camelize($installer->option('installation_method')) . ' installing ' . $installer->option('framework_human') . ' application sources');
            if (!$installer->installSources()) {
                $this->exitBashError('Error installing application sources.');
            }
        }

        if (!$dirHasData && $installer->option('installation_method') !== 'composer') {
            if (file_exists($installer->option('path') . DS . 'composer.json')) {
                $this->logInfo('Composer installing detected composer.json');
                if (!$this->execute->composerInstall($installer->option('path'))) {
                    $this->exitBashError('Error Composer installing detected composer.json.');
                }
            }
        }

        # ------------------------------------------------------------
        # Create Nginx virtual host if needed
        # ------------------------------------------------------------
        $this->out('Creating virtual host');

        // remove existing (assumed orphaned) vhost when not in --repair mode
        if (CakeboxUtility::vhostAvailable($url) && !$this->params['repair']) {
            $this->out('* Removing existing (assumed orphaned) virtual host');
            if (!$this->execute->removeSite($url)) {
                $this->exitBashError('Error removing virtual host');
            }
        }

        $vhostAvailable = CakeboxUtility::vhostAvailable($url);
        $vhostEnabled = CakeboxUtility::vhostEnabled($url);

        if ($vhostAvailable && $vhostEnabled) {
            $this->out('* Skipping: virtual host already up and running');
        }

        if ($vhostAvailable && !$vhostEnabled) {
            $this->out('* Skipping: configuration file already exists');
        }

        if (!$vhostAvailable) {
            if (!$this->execute->addSite($url, $installer->option('webroot'), true)) {
                $this->exitBashError('Error creating virtual host');
            } else {
                $this->out('* Successfully created virtual host');
            }
        }

        // recheck symlink since it could have been created above
        if (!CakeboxUtility::vhostEnabled($url)) {
            $this->out('* Enabling virtual host');
            if (!$this->execute->createSiteSymlink($url)) {
                $this->exitBashError('Error creating symbolic link');
            }
            if (!$this->execute->reloadNginx()) {
                $this->exitBashError('Error reloading Nginx');
            }
        }

        # ------------------------------------------------------------
        # Create databases if needed
        # ------------------------------------------------------------
        $this->out('Creating databases');
        $mainDatabase = $installer->option('database');
        $testDatabase = $this->Info->databaseMeta['test_prefix'] . $installer->option('database');

        // remove existing (assumed orphaned) databases when not in --repair mode
        if (CakeboxUtility::databaseExists($mainDatabase) && !$this->params['repair']) {
            $this->out('* Removing existing (assumed orphaned) databases');
            if (!CakeboxUtility::dropDatabase($mainDatabase)) {
                $this->exitBashError("Error dropping main database $mainDatabase");
            }
            if (!CakeboxUtility::dropDatabase($testDatabase)) {
                $this->exitBashError("Error dropping test database $testDatabase");
            }
        }

        if (CakeboxUtility::databaseExists($mainDatabase)) {
            $this->out('* Skipping: main database already exists');
        } else {
            if (!CakeboxUtility::createDatabase($mainDatabase, 'cakebox', 'secret', true)) {
                $this->exitBashError('Error creating main database');
            } else {
                $this->out('* Successfully created main database');
            }
        }

        if (CakeboxUtility::databaseExists($testDatabase)) {
            $this->out('* Skipping: test database already exists');
        } else {
            if (!CakeboxUtility::createDatabase($testDatabase, 'cakebox', 'secret', true)) {
                $this->exitBashError('Error creating test database');
            } else {
                $this->out('* Successfully created test database');
            }
        }

        # ------------------------------------------------------------
        # Configure permissions (for supported frameworks only)
        # ------------------------------------------------------------
        $this->out('Configuring permissions');
        if (!$installer->setPermissions()) {
            $this->exitBashError('Error setting permissions');
        }

        # ------------------------------------------------------
        # Only update config files when not in --repair mode
        # ------------------------------------------------------
        $this->out('Updating configuration files');
        if ($this->params['repair']) {
            $this->out('* Skipping: configuration files are not updated in --repair mode');
        } else {
            if (!$installer->updateConfigs()) {
                $this->exitBashError('Error updating configuration file(s)');
            }
        }

        # ------------------------------------------------------
        # Provide user with feedback
        # ------------------------------------------------------
        if ($this->params['repair']) {
            $this->out('Application repaired using:');
        } else {
            $this->out('Application created using:');
        }

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

        $this->out("\nRemember to update your hosts file with: <info>" . $this->Info->getVmIpAddress() . " http://$url</info>\n");
        $this->exitBashSuccess('Installation completed successfully');
    }
}
