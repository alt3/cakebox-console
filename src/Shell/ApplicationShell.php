<?php
namespace App\Shell;

use App\Lib\CakeboxInfo;
use App\Lib\CakeboxExecute;
use App\Lib\CakeboxUtility;
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
                    'template' => [
                        'short' => 't',
                        'help' => __('Template used to generate the application.'),
                        'choices' => ['cakephp', 'friendsofcake'],
                        'default' => 'cakephp'
                    ],
                    'repository' => [
                        'short' => 'r',
                        'help' => __('Provision using your own application repository, framework will be autodetected.'),
                        'required' => false
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
        # Provide (vagrant provisioning) feedback
        $this->logStart("Creating application http://$url");

        # Prevent overwriting default Cakebox site
        if ($url == 'default') {
            $this->logError("Error: cannot use 'default' as <url> as this would overwrite the default Cakebox site.");
            $this->exitBashError();
        }

        # Use default installation path unless --path option is given
        if (isset($this->params['path'])) {
            $this->path = $this->params['path'];
        } else {
            $this->path = '/home/vagrant/Apps/' . $url;
        }
        $this->logInfo("Installing into $this->path");

        # Check if the target directory meets requirements for git cloning
        # (non-existent or empty). Note: exits with success to allow vagrant
        # re-provisioning.
        if (!CakeboxUtility::dirAvailable($this->path)) {
            $this->logWarning("* Skipping: target directory $this->path not empty.");
            $this->exitBashSuccess();
        }

        # Run user-application installer
        if (isset($this->params['repository'])) {
            if (!$this->__runRepositoryInstaller($url, $this->params['repository'])) {
                $this->logError("Error encoutered while installing user specified repository.");
                $this->exitBashError();
            }
        # Run framework/version specific installer method
        } elseif (!$this->__runFrameworkInstaller($url, $this->params['framework'], $this->params['majorversion'], $this->params['template'])) {
            $this->logError("Error encoutered running framework installer.");
            $this->exitBashError();
        }

        # Provide Vagrant feedback
        $this->logInfo("Application installed successfully");
        $this->exitBashSuccess();
    }

    /**
     * Determine and executes framework specific installer method.
     *
     * @param string $url Fully Qualified Domain Name used to expose the site.
     * @param string $framework Name of the PHP framework (e.g. cakephp, laravel).
     * @param string $version Major version of the PHP framework (e.g. 2, 3).
     * @param string $template Template to use (e.g. cakephp/friendsofcake).
     * @return bool
     */
    private function __runFrameworkInstaller($url, $framework, $version, $template)
    {
        switch ($framework) {
            case "cakephp":
                if ($template == 'cakephp' && $version == "3") {
                    return ($this->__installCake3($url));
                }
                if ($template == 'cakephp' && $version == "2") {
                    return ($this->__installCake2($url));
                }
                $this->logError("Error: reached undefined cakephp installer.");
                return false;
            case "laravel":
                return ($this->__installLaravel($url));
            default:
                $this->logError("Error: reached undefined framework installer.");
                return false;
        }
    }

    /**
     * CakePHP 2.x specific installer.
     *
     * @param string $url Fully Qualified Domain Name used to expose the site.
     * @return bool
     */
    private function __installCake2($url)
    {
        $execute = new CakeboxExecute();
        $cbInfo = new CakeboxInfo();

        # Create target directory with correct ownership if needed
        if (!is_dir($this->path)) {
            $this->logInfo("Creating target directory " . $this->path);
            if ($execute->mkVagrantDir($this->path) == false) {
                $this->logError("Error creating target directory " . $this->path);
                return false;
            }
        }

        # Git clone Cake2 repo
        $this->logInfo("Please wait... git cloning CakePHP 2.x");
        $repository = $cbInfo->frameworkMeta['cakephp2']['repository'];
        if ($execute->gitClone($repository, $this->path) == false) {
            $this->logError("Error git cloning repository");
            return false;
        }

        # Git clone DebugKit plugin
        $repository = 'https://github.com/cakephp/debug_kit.git';
        $pluginDir = $this->path . DS . 'app' . DS . 'Plugin' . DS . 'DebugKit';
        if ($execute->gitClone($repository, $pluginDir) == false) {
            $this->logError("Error git cloning DebugKit plugin");
            return false;
        }

        # Create nginx site
        $this->logInfo("Creating website");
        $webroot = $this->path . DS . $cbInfo->frameworkMeta['cakephp2']['webroot'];
        if ($execute->addSite($url, $webroot, true) == false) {
            $this->logError("Error creating website");
            return false;
        }

        # Create databases
        $this->logInfo("Creating databases");
        if ($execute->addDatabase($url, 'cakebox', 'secret', true) == false) {
            $this->logError("Error creating databases");
            return false;
        }

        # Set permissions
        $this->logInfo("Setting permissions");
        foreach ($cbInfo->frameworkMeta['cakephp2']['writable_dirs'] as $directory) {
            if (CakeboxUtility::setFolderPermissions($this->path . DS . $directory) == false) {
                $this->logError("Error setting permissions on $directory");
                return false;
            }
        }

        # Update configuration (core.php, database.php and bootstrap.php
        $this->logInfo("Updating configuration");
        #$config = $this->path . DS . "config" . DS . "app.php";
        if ($execute->updateCake2Configuration($this->path, $url) == false) {
            $this->logError("Error updating configuration");
            return false;
        }

        # All done, Cake2 app should be up-and-running
        return true;
    }

    /**
     * CakePHP 3.x specific installer using CakePHP Application Skeleton.
     *
     * @param string $url Fully Qualified Domain Name used to expose the site.
     * @return boolean True if the application installed successfully
     */
    private function __installCake3($url)
    {
        $execute = new CakeboxExecute();
        $cbInfo = new CakeboxInfo();

        # Create target directory with correct ownership if needed
        if (!is_dir($this->path)) {
            $this->logInfo("Creating target directory " . $this->path);
            if ($execute->mkVagrantDir($this->path) == false) {
                $this->logError("Error creating target directory " . $this->path);
                return false;
            }
        }

        # Composer install Cake3 using Application Template
        $this->logInfo("Please wait... composer installing CakePHP 3.x app skeleton");
        if ($execute->composerCreateProject('cakephp/app', $this->path) == false) {
            $this->logError("Error composer creating project");
            return false;
        }

        # Create nginx site
        $this->logInfo("Creating website");
        $webroot = $this->path . DS . $cbInfo->frameworkMeta['cakephp3']['webroot'];
        if ($execute->addSite($url, $webroot, true) == false) {
            $this->logError("Error creating website");
            return false;
        }

        # Create databases
        $this->logInfo("Creating databases");
        if ($execute->addDatabase($url, 'cakebox', 'secret', true) == false) {
            $this->logError("Error creating databases");
            return false;
        }

        # Update app.php with database names
        $this->logInfo("Updating configuration");
        $config = $this->path . DS . "config" . DS . "app.php";
        if (CakeboxUtility::updateCake3Configuration($config, $url) == false) {
            $this->logError("Error updating config file");
            return false;
        }

        # All done, cake app should be up-and-running
        return true;
    }

    /**
     * Laravel specific installer.
     *
     * @param string $url Fully Qualified Domain Name used to expose the site.
     * @return bool
     */
    private function __installLaravel($url)
    {
        $execute = new CakeboxExecute();
        $cbInfo = new CakeboxInfo();

        # Create target directory with correct ownership if needed
        if (!is_dir($this->path)) {
            $this->logInfo("Creating target directory " . $this->path);
            if ($execute->mkVagrantDir($this->path) == false) {
                $this->logError("Error creating target directory " . $this->path);
                return false;
            }
        }

        # Composer install Cake3 using Application Template
        $this->logInfo("Please wait... composer installing Laravel 4");
        if ($execute->composerCreateProject('laravel/laravel', $this->path) == false) {
            $this->logError("Error composer creating project");
            return false;
        }

        # Create nginx site
        $this->logInfo("Creating website");
        $webroot = $this->path . DS . $cbInfo->frameworkMeta['laravel']['webroot'];
        if ($execute->addSite($url, $webroot, true) == false) {
            $this->logError("Error creating website");
            return false;
        }

        # Create databases
        $this->logInfo("Creating databases");
        if ($execute->addDatabase($url, 'cakebox', 'secret', true) == false) {
            $this->logError("Error creating databases");
            return false;
        }

        # Set permissions
        $this->logInfo("Setting permissions");
        foreach ($cbInfo->frameworkMeta['laravel']['writable_dirs'] as $directory) {
            if (CakeboxUtility::setFolderPermissions($this->path . DS . $directory) == false) {
                $this->logError("Error setting permissions on $directory");
                return false;
            }
        }

        # All done, Laravel app should be up-and-running
        return true;
    }

    /**
     * User specified repository installer.
     *
     * @param string $url Fully Qualified Domain Name used to expose the site.
     * @param string $repository Full url/git path to the user's repository.
     * @return bool
     */
    private function __runRepositoryInstaller($url, $repository)
    {
        $execute = new CakeboxExecute();
        $cbInfo = new CakeboxInfo();

        # Create target directory with correct ownership if needed
        if (!is_dir($this->path)) {
            $this->logInfo("Creating target directory " . $this->path);
            if ($execute->mkVagrantDir($this->path) == false) {
                $this->logError("Error creating target directory " . $this->path);
                return false;
            }
        }

        # Git clone user repository
        $this->logInfo("Please wait... git cloning $repository");
        if ($execute->gitClone($repository, $this->path) == false) {
            $this->logInfo($execute->debug());
            $this->logError("Error git cloning repository");
            return false;
        }

        # Composer install if needed
        if (file_exists($this->path . DS . 'composer.json')) {
            $this->logInfo("Please wait... composer installing");
            if ($execute->composerInstall($this->path) == false) {
                $this->logInfo($execute->debug());
                $this->logError("Error composer installing");
                return false;
            }
        }

        # Detect framework
        $framework = $cbInfo->getFrameworkCommonName($this->path);
        $this->logInfo("Detected framework $framework");

        # Create nginx site
        $this->logInfo("Creating website");
        $webroot = $cbInfo->getWebrootFromDirectory($this->path);
        if ($execute->addSite($url, $webroot, true) == false) {
            $this->logError("Error creating website");
            return false;
        }

        # Create databases
        $this->logInfo("Creating databases");
        if ($execute->addDatabase($url, 'cakebox', 'secret', true) == false) {
            $this->logError("Error creating databases");
            return false;
        }

        # Set permissions
        $this->logInfo("Setting permissions");
        foreach ($cbInfo->frameworkMeta[$framework]['writable_dirs'] as $directory) {
            if (CakeboxUtility::setFolderPermissions($this->path . DS . $directory) == false) {
                $this->logError("Error setting permissions on $directory");
                return false;
            }
        }

        # All done, provisioning feedback
         $this->logInfo(" => Note: application settings are not automatically configured for user repositories");
         $this->logInfo(" => Note: make sure to manually update your database credentials, plugins, etc.");
        return true;
    }
}
