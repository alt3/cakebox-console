<?php
namespace App\Lib;

use App\Lib\CakeboxExecute;
use App\Lib\CakeboxInfo;
use App\Lib\CakeboxUtility;
use Cake\Log\Log;

/**
 * Class library for checking against box requirements, states and conditions.
 */
class CakeboxFrameworkInstaller
{

    /**
     * List with overridable default options required for installations.
     *
     * @var Named Hash
     */
    protected $options = [
        'framework' => 'cakephp',
        'majorversion' => '3',
        'installation_method' => 'git'
    ];

    /**
     * Safety flags to make sure certain "installation phases" are not executed
     * before required preceeding phases have completed successfully.
     *
     * @var array $flags
     */
    protected $flags = [];

    /**
     * List with debug information for the most recently executed command.
     *
     * @var Array
     */
    protected $log = [];

    /**
     * CakeboxInfo instance
     *
     *  @var App\Lib\CakeboxInfo
     */
    protected $cbi;

    /**
     * CakeboxExecute instance
     *
     * @var App\Lib\CakeboxInfo
     */
    protected $execute;

    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->cbi = new CakeboxInfo();
        $this->execute = new CakeboxExecute();
    }

    /**
     * Store information required for installing.
     *
     * @param array $options Installer options
     * @return boolean True when successful
     */
    public function setup(Array $options)
    {
        log::debug("Determining installation settings");
        try {
            $this->mergeOptions($options);
            $this->setPath();
            $this->setFrameworkOptions();
            $this->logOptions();
            $this->flags['configured'] = true;
            return true;
        } catch (\Exception $e) {
            log::error("Setup failed: " . $e->getMessage());
            return false;
        }
        //call_user_func($functionName)
    }

    /**
     * Run framework agnostic preparations (e.g. creating the target directory).
     *
     * @return boolean True when successful
     */
    public function prepare()
    {
        log::debug("Preparing for installation...");
        if (!$this->flags['configured']) {
            log::error("Setup method has not been run");
            return false;
        }

        try {
            $this->prepareDirectory();
            $this->flags['prepared'] = true;
            return true;
        } catch (\Exception $e) {
            log::error("Preparation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
    * Run framework specific installation using either composer or git clone.
    *
    * @return boolean True when successful
    */
    public function install()
    {
        log::debug("Installing application...");
        if (!$this->flags['prepared']) {
            log::error("Prepare method has not been run");
            return false;
        }

        try {
            if ($this->options['installation_method'] == 'composer') {
                $this->composerInstall();
            } else {
                $this->gitInstall();
                $this->runComposer();
            }

            $this->flags['installed'] = true;
            return true;
        } catch (\Exception $e) {
            log::error("Installation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Round up installation. Creates website and databases, sets permissions
     * and updates configuration files if needed.
     *
     * @return boolean True when successful
     */
    public function roundup()
    {
        log::debug("Rounding up installation...");
        if (!$this->flags['installed']) {
            log::error("Install method has not been run");
            return false;
        }

        try {
            # Detect framework settings for user specified application (if any)
            if (isset($this->options['source'])) {
                $this->setCustomOptions();
            }
            $this->createSite();
            $this->createDatabases();
            $this->setPermissions();
            $this->updateConfigs();

            # All done
            log::debug("Installation completed successfully!");
            return true;
        } catch (\Exception $e) {
            log::error("Roundup failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Merges default settings with passed options while removing empty keys.
     *
     * @throws Exception
     * @return void
     */
    protected function mergeOptions($options) {
        $options = array_merge($this->options, $options);
        if (empty($options['url'])) {
            throw new \Exception("Required option `url` is missing.");
        }
        $this->options = array_filter($options);
    }

    /**
     * Return all options used for the installation.
     *
     * @return array
     */
    public function options()
    {
        return $this->options;
    }

    /**
     * Return a single option used for the installation.
     *
     * @param string $key Name key of the option.
     * @return string|false
     */
    public function option($key)
    {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }
        return false;
    }

    /**
     * Write all options to log for easy debugging
     */
    protected function logOptions()
    {
        log::debug("Installation options:");
        foreach($this->options as $key => $value) {
            log::debug("  $key => $value");
        }
    }

    /**
     * Set installation path to /home/vagrant/Apps unless --path was used
     *
     * @return void
     */
    protected function setPath()
    {
        if (isset($this->options['path'])) {
            $this->options['path'] = $this->options['path'];
        } else {
            $this->options['path'] = '/home/vagrant/Apps/' . $this->options['url'];
        }
    }

    /**
     * Set framework specific information.
     *
     * @return boolean True if a valid method could be determined.
     */
     protected function setFrameworkOptions()
     {
         $this->options['database'] = CakeboxUtility::normalizeDatabaseName($this->options['url']);

         # User specified source
         if (isset($this->options['source'])) {
             $this->options['framework_short'] = 'custom';
             $this->options['framework_human'] = 'user specified';
             $this->options['installation_method'] = $this->options['installation_method'];
             $this->options['source'] = $this->options['source'];

             # Unset irrelevant options to keep logs
             unset ($this->options['framework']);
             unset ($this->options['majorversion']);
             unset ($this->options['framework']);
             return true;
        }

        # Out-of-the-box framework installation
        switch ($this->options['framework']) {
            case 'cakephp':
                if ($this->options['majorversion'] == '3' ) {
                    $this->options['framework_short'] = 'cakephp3';
                    $this->options['framework_human'] = 'CakePHP 3.x';
                    $this->options['installation_method'] = $this->cbi->frameworkMeta['cakephp3']['installation_method'];
                    $this->options['source'] = $this->cbi->frameworkMeta['cakephp3']['source'];
                    $this->options['webroot'] = $this->options['path'] . DS . $this->cbi->frameworkMeta['cakephp3']['webroot'];
                }
                if ($this->options['majorversion'] == '2' ) {
                    $this->options['framework_short'] = 'cakephp2';
                    $this->options['framework_human'] = 'CakePHP 2.x';
                    $this->options['installation_method'] = $this->cbi->frameworkMeta['cakephp2']['installation_method'];
                    $this->options['source'] = $this->cbi->frameworkMeta['cakephp2']['source'];
                    $this->options['webroot'] = $this->options['path'] . DS . $this->cbi->frameworkMeta['cakephp2']['webroot'];
                }
                break;

            case 'laravel':
                $this->options['framework_short'] = 'laravel';
                $this->options['framework_human'] = 'Laravel 4.x';
                $this->options['installation_method'] = $this->cbi->frameworkMeta['laravel']['installation_method'];
                $this->options['source'] = $this->cbi->frameworkMeta['laravel']['source'];
                $this->options['webroot'] = $this->options['path'] . DS . $this->cbi->frameworkMeta['laravel']['webroot'];
                unset ($this->options['majorversion']);
                unset ($this->options['template']);
                break;

            default:
                throw new \Exception("Unsupported framework");
        }

        # Attempt changing the source if --ssh option was used.
        if (isset($this->options['ssh'])) {
            log::debug("Trying to change Git source since user passed --ssh option");
            if (isset($this->cbi->frameworkMeta[$this->options['framework_short']]['source_ssh'])) {
                $this->options['source'] = $this->cbi->frameworkMeta[$this->options['framework_short']]['source_ssh'];
                log::debug("* Changed source to " . $this->options['source']);
            } else {
                log::debug("* Skipping: metadata contains no alternative SSH source");
            }
        }
        return true;
     }

     /**
      * Try to detect framework specific settings for user specified applications.
      *
      * @throws Exception
      * @return boolean True if successful
      */
     protected function setCustomOptions() {
         log::Debug("Detecting framework options for custom application");

         # Detect framework first
         $framework = $this->cbi->getFrameworkCommonName($this->options['path']);
         if (empty($framework)) {
             log::debug("* No matching framework detected");
             log::debug("* Setting webroot to application directory");
             $this->options['webroot'] = $this->options['path'];
             unset($this->option['framework_short']);
             return true;
         }
         log::debug("* Detected $framework");
         $this->options['framework_short'] = $framework;

         # Set webroot
         $this->options['webroot'] = $this->options['path'] . DS . $this->cbi->frameworkMeta[$framework]['webroot'];
         return true;
     }

     /**
      * Prepare a directory for installation by the `vagrant` user.
      *
      * @throws Exception
      * @return boolean True if successful
      */
     protected function prepareDirectory() {
         if (!CakeboxUtility::dirAvailable($this->options['path'])) {
             throw new \Exception("Target directory did not pass readiness tests.");
         }

         if (!is_dir($this->options['path'])) {
             log::debug("Creating target directory " . $this->options['path']);
             if (!$this->execute->mkVagrantDir($this->options['path'])) {
                 throw new \Exception("Error creating target directory " . $this->options['path']);
             }
         }
         return true;
     }

     /**
      * Install application using Composer create-project.
      *
      * @throws Exception
      * @return boolean True if successful
      */
     protected function composerInstall() {
         log::Debug("Composer installing " . $this->options['framework_human']);
         if (!$this->execute->composerCreateProject($this->options['source'], $this->options['path'])) {
             throw new \Exception("Error composer installing.");
         }
         return true;
     }

     /**
      * Install public/private repository using Git clone.
      *
      * @throws Exception
      * @return boolean True if successful
      */
     protected function gitInstall() {
         log::Debug("Git installing " . $this->options['framework_human']);
         if (!$this->execute->gitClone($this->options['source'], $this->options['path'])) {
             throw new \Exception("Error git cloning.");
         }
         return true;
    }

    /**
     * Run composer install when detecting composer.json file in the directory.
     *
     * @throws Exception
     * @return boolean True if successful
     */
    protected function runComposer() {
        if (!file_exists($this->options['path'] . DS . 'composer.json')) {
            log::debug("Git cloned repository does not have a composer.json");
            return true;
        }

        log::debug("Detected a composer.json... run composer install");
        if (!$this->execute->composerInstall($this->options['path'])) {
            throw new \Exception("Error composer installing.");
        }
        return true;
    }

    /**
     * Create and enable an Nginx site file.
     *
     * @throws Exception
     * @return boolean True if successful
     */
    protected function createSite() {
        if (!$this->execute->addSite($this->options['url'], $this->options['webroot'], true)) {
            throw new \Exception("Error creating site.");
        }
        return true;
    }

    /**
     * Create databases.
     *
     * @throws Exception
     * @return boolean True if successful
     */
    protected function createDatabases() {
        if (!$this->execute->addDatabase($this->options['database'], 'cakebox', 'secret', true)) {
            throw new \Exception("Error creating databases.");
        }
        return true;
    }

    /**
     * Set permissions on writebale directories for known frameworks.
     *
     * @throws Exception
     * @return boolean True if permissions were skipped OR set succesfully
     */
    protected function setPermissions() {
        log::debug("Updating directory permissions");

        # Skip if no framework was detected
        if (!isset($this->options['framework_short'])) {
            log::debug("* Skipping: unsupported/empty framework");
            return true;
        }

        # Skip if the framework does not use writable directories
        if (!isset($this->cbi->frameworkMeta[$this->options['framework_short']]['writable_dirs'])) {
            log::debug("* Skipping: framework does not use writeable directories");
            return true;
        }

        # Set permissions
        log::debug("* Applying " . $this->options['framework_short'] . " folder permissions");
        foreach ($this->cbi->frameworkMeta[$this->options['framework_short']]['writable_dirs'] as $directory) {
            if (!CakeboxUtility::setFolderPermissions($this->options['path'] . DS . $directory)) {
                throw new \Exception("Error setting permissions.");
            }
        }
        return true;
    }

    /**
     * Update framework specific configuration files if possible.
     *
     * @throws Exception
     * @return boolean True if successful
     */
    protected function updateConfigs() {
        log::debug("Updating configuration files");

        if (isset($this->options['source'])) {
            log::debug("* Skipping: automated configuration updates are not supported for user specified applications");
            return true;
        }

        log::debug("Updating " . $this->options['framework_human'] . " config files");

        if ($this->options['framework_short'] == 'cakephp3') {
            if (!$this->execute->updateCake3Configuration($this->options['path'], $this->options['url'])) {
                throw new \Exception("Error updating config file.");
            }
        }

        if ($this->options['framework_short'] == 'cakephp2') {
            if (!$this->execute->updateCake2Configuration($this->options['path'], $this->options['url'])) {
                throw new \Exception("Error updating config file.");
            }
        }
        return true;
    }
}
