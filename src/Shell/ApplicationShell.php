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
                        'help' => __('Fully qualified domain name used to expose the application.'),
                        'required' => true
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
                    'hhvm' => [
                        'help' => __('Serve pages using HHVM instead of PHP-FPM.'),
                        'boolean' => true
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
        # available/empty AND and we are NOT in --repair mode
        if (!$targetDirAvailable && (!$this->params['repair'])) {
            $this->exitBashError('Error: target directory ' . $installer->option('path') . ' contains data');
        }

        # ----------------------------------------------------------------
        # New install or --repair: create installation directory if needed
        # ----------------------------------------------------------------
        $this->out('Creating installation directory');

        // Dir exists AND contains data: do nothing
        if (!$targetDirAvailable && is_dir($installer->option('path'))) {
            $this->out('* Skipping: target directory contains data');
        }

        // Dir exists AND does not contain data: do nothing
        if ($targetDirAvailable && is_dir($installer->option('path'))) {
            $this->out('* Skipping: directory already created');
        }

        // Dir does not exist; create it
        if ($targetDirAvailable && !is_dir($installer->option('path'))) {
            if (!$this->Execute->mkVagrantDir($installer->option('path'))) {
                $this->exitBashError('Error creating target directory ' . $installer->option('path'));
            }
        }

        # ------------------------------------------------------------
        # Install sources if needed
        # ------------------------------------------------------------
        if ($targetDirAvailable) {
            $this->out(Inflector::camelize($installer->option('installation_method')) . ' installing ' . $installer->option('framework_human') . ' application sources');
            if (!$installer->installSources()) {
                $this->exitBashError('Error installing application sources.');
            }
        }

        if (!$targetDirAvailable && $installer->option('installation_method') !== 'composer') {
            if (file_exists($installer->option('path') . DS . 'composer.json')) {
                $this->logInfo('Composer installing detected composer.json');
                if (!$this->Execute->composerInstall($installer->option('path'))) {
                    $this->exitBashError('Error Composer installing detected composer.json.');
                }
            }
        }

        # ------------------------------------------------------------
        # Create Nginx virtual host if needed
        # ------------------------------------------------------------
        $this->out('Creating virtual host');
        if ($this->params['hhvm']) {
            $vhostType = 'HHVM';
        } else {
            $vhostType = 'PHP-FPM';
        }

        // remove existing (assumed orphaned) vhost when not in --repair mode
        if (CakeboxUtility::vhostAvailable($url) && !$this->params['repair']) {
            $this->out('* Removing existing (assumed orphaned) virtual host');
            if (!$this->Execute->removeVhost($url)) {
                $this->exitBashError('Error removing virtual host');
            }
        }

        $vhostAvailable = CakeboxUtility::vhostAvailable($url);
        $vhostEnabled = CakeboxUtility::vhostEnabled($url);

        if ($vhostAvailable && $vhostEnabled) {
            $this->out("* Skipping: $vhostType virtual host already up and running");
        }

        if ($vhostAvailable && !$vhostEnabled) {
            $this->out('* Skipping: configuration file already exists');
        }

        if (!$vhostAvailable) {
            if (!$this->Execute->addVhost($url, $installer->option('webroot'), [
                    'force' => true,
                    'hhvm' => $this->params['hhvm']
            ])) {
                $this->exitBashError("Error creating $vhostType virtual host");
            }
            $this->out("* Successfully created $vhostType virtual host");
        }

        // recheck symlink since it could have been created above
        if (!CakeboxUtility::vhostEnabled($url)) {
            $this->out('* Enabling virtual host');
            if (!$this->Execute->enableVhost($url)) {
                $this->exitBashError('Error creating symbolic link');
            }
            if (!$this->Execute->reloadNginx()) {
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
