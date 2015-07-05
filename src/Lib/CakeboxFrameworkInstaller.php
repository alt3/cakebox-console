<?php
namespace App\Lib;

use Cake\Core\Exception\Exception;
use Cake\Log\Log;
use Cake\Utility\Hash;

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
     * before required preceding phases have completed successfully.
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
     * @var App\Lib\CakeboxInfo
     */
    protected $Info;

    /**
     * CakeboxExecute instance
     *
     * @var App\Lib\CakeboxInfo
     */
    protected $Execute;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->Info = new CakeboxInfo();
        $this->Execute = new CakeboxExecute();
    }

    /**
     * Store information required for installing.
     *
     * @param array $options Installer options
     * @return boolean True when successful
     * @throws \Cake\Core\Exception\Exception
     */
    public function setup(Array $options)
    {
        Log::debug("Determining installation settings");
        try {
            $this->_mergeOptions($options);
            $this->_setPath();
            $this->_setDatabase();
            $this->_setFrameworkOptions();
            $this->_logOptions();
            $this->flags['configured'] = true;
            return true;
        } catch (Exception $e) {
            Log::error("Setup failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Run framework agnostic preparations (e.g. creating the target directory).
     *
     * @return boolean True when successful
     * @throws \Cake\Core\Exception\Exception
     */
    public function createDirectory()
    {
        Log::debug("Creating installation directory...");
        if (!$this->flags['configured']) {
            Log::error("Setup method has not been run");
            return false;
        }

        try {
            $this->_prepareDirectory();
            $this->flags['prepared'] = true;
            return true;
        } catch (Exception $e) {
            Log::error("Preparation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Run framework specific installation using either composer or git clone.
     *
     * @return boolean True when successful
     * @throws \Cake\Core\Exception\Exception
     */
    public function installSources()
    {
        Log::debug("Installing application...");

        try {
            if ($this->options['installation_method'] == 'composer') {
                $this->_composerInstall();
            } else {
                $this->_gitInstall();
            }
            return true;
        } catch (Exception $e) {
            Log::error("Installation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Merges default settings with passed options while removing empty keys.
     *
     * @param array $options Array with passed installer options.
     * @return void
     * @throws \Cake\Core\Exception\Exception
     */
    protected function _mergeOptions($options)
    {
        $options = array_merge($this->options, $options);
        if (empty($options['url'])) {
            throw new Exception("Required option `url` is missing.");
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
     *
     * @return void
     */
    protected function _logOptions()
    {
        Log::debug("Installation options:");
        foreach ($this->options as $key => $value) {
            Log::debug("  $key => $value");
        }
    }

    /**
     * Set installation path to /home/vagrant/Apps unless --path was used
     *
     * @return void
     */
    protected function _setPath()
    {
        if (isset($this->options['path'])) {
            return;
        }

        $this->options['path'] = '/home/vagrant/Apps/' . $this->options['url'];
    }

    /**
     * Set database name to be used for the application
     *
     * @return void
     */
    protected function _setDatabase()
    {
        $this->options['database'] = CakeboxUtility::normalizeDatabaseName($this->options['url']);
    }

    /**
     * Set framework specific information.
     *
     * @return boolean True if a valid method could be determined.
     * @throws \Cake\Core\Exception\Exception
     */
    protected function _setFrameworkOptions()
    {
         # User specified source
        if (isset($this->options['source'])) {
            $this->options['framework_short'] = 'custom';
            $this->options['framework_human'] = 'user specified';
            $this->options['installation_method'] = $this->detectInstallationMethod($this->options['source']);

            # Unset irrelevant options to keep logs
            unset($this->options['framework']);
            unset($this->options['majorversion']);
            unset($this->options['framework']);
            return true;
        }

        # Out-of-the-box framework installation
        switch ($this->options['framework']) {
            case 'cakephp':
                if ($this->options['majorversion'] == '3') {
                    $this->options['framework_short'] = 'cakephp3';
                    $this->options['framework_human'] = 'CakePHP 3.x';
                    $this->options['installation_method'] = $this->Info->frameworkMeta['cakephp3']['installation_method'];
                    $this->options['source'] = $this->Info->frameworkMeta['cakephp3']['source'];
                    $this->options['webroot'] = $this->options['path'] . DS . $this->Info->frameworkMeta['cakephp3']['webroot'];
                }
                if ($this->options['majorversion'] == '2') {
                    $this->options['framework_short'] = 'cakephp2';
                    $this->options['framework_human'] = 'CakePHP 2.x';
                    $this->options['installation_method'] = $this->Info->frameworkMeta['cakephp2']['installation_method'];
                    $this->options['source'] = $this->Info->frameworkMeta['cakephp2']['source'];
                    $this->options['webroot'] = $this->options['path'] . DS . $this->Info->frameworkMeta['cakephp2']['webroot'];
                }
                break;

            case 'laravel':
                $this->options['framework_short'] = 'laravel';
                $this->options['framework_human'] = 'Laravel 5';
                $this->options['installation_method'] = $this->Info->frameworkMeta['laravel']['installation_method'];
                $this->options['source'] = $this->Info->frameworkMeta['laravel']['source'];
                $this->options['webroot'] = $this->options['path'] . DS . $this->Info->frameworkMeta['laravel']['webroot'];
                unset($this->options['majorversion']);
                unset($this->options['template']);
                break;

            default:
                throw new Exception("Unsupported framework");
        }

        # Attempt changing the source if --ssh option was used.
        if (isset($this->options['ssh'])) {
            Log::debug("Trying to change Git source since user passed --ssh option");
            if (isset($this->Info->frameworkMeta[$this->options['framework_short']]['source_ssh'])) {
                $this->options['source'] = $this->Info->frameworkMeta[$this->options['framework_short']]['source_ssh'];
                Log::debug("* Changed source to " . $this->options['source']);
            } else {
                Log::debug("* Skipping: metadata contains no alternative SSH source");
            }
        }
        return true;
    }

    /**
     * Detect the installation method for user specified sources. Assumes
     * composer if the source does not match a git repository.
     *
     * @param string $source Containing git repository or composer package name.
     * @return boolean True if successful
     */
    public function detectInstallationMethod($source)
    {
        if (substr($source, 0, 8) === 'https://') {
            return 'git';
        }

        if (substr($source, 0, 4) === 'git@') {
            return 'git';
        }
        return 'composer';
    }

    /**
     * Prepare a directory for installation by the `vagrant` user.
     *
     * @return boolean True if successful
     * @throws \Cake\Core\Exception\Exception
     */
    protected function _prepareDirectory()
    {
        if (!CakeboxUtility::dirAvailable($this->options['path'])) {
            throw new Exception("Target directory did not pass readiness tests.");
        }

        if (!is_dir($this->options['path'])) {
            Log::debug("Creating target directory " . $this->options['path']);
            if (!$this->Execute->mkVagrantDir($this->options['path'])) {
                throw new Exception("Error creating target directory " . $this->options['path']);
            }
        }
        return true;
    }

    /**
     * Install application using Composer create-project.
     *
     * @return boolean True if successful
     * @throws Cake\Core\Exception\Exception
     */
    protected function _composerInstall()
    {
        Log::Debug("Composer installing " . $this->options['framework_human']);
        if (!$this->Execute->composerCreateProject($this->options['source'], $this->options['path'])) {
            throw new Exception("Error composer installing.");
        }
        return true;
    }

    /**
     * Install public/private repository using Git clone.
     *
     * @return boolean True if successful
     * @throws Cake\Core\Exception\Exception
     */
    protected function _gitInstall()
    {
        Log::Debug("Git installing " . $this->options['framework_human']);
        if (!$this->Execute->gitClone($this->options['source'], $this->options['path'])) {
            throw new Exception("Error git cloning.");
        }
        return true;
    }

    /**
     * Set permissions on writebale directories for known frameworks.
     *
     * @return boolean True if permissions were skipped OR set succesfully
     * @throws \Cake\Core\Exception\Exception
     */
    public function setPermissions()
    {
        Log::debug("Updating directory permissions");

        # Skip if no framework was detected
        if (!isset($this->options['framework_short'])) {
            Log::debug("* Skipping: unsupported/empty framework");
            return true;
        }

        # Skip if the framework does not use writable directories
        if (!isset($this->Info->frameworkMeta[$this->options['framework_short']]['writable_dirs'])) {
            Log::debug("* Skipping: framework does not use writeable directories");
            return true;
        }

        # Set permissions
        Log::debug("* Applying " . $this->options['framework_short'] . " folder permissions");
        foreach ($this->Info->frameworkMeta[$this->options['framework_short']]['writable_dirs'] as $directory) {
            if (!CakeboxUtility::setFolderPermissions($this->options['path'] . DS . $directory)) {
                throw new Exception("Error setting permissions.");
            }
        }
        return true;
    }

    /**
     * Update framework specific configuration files if possible.
     *
     * @return boolean True if successful
     * @throws \Cake\Core\Exception\Exception
     */
    public function updateConfigs()
    {
        Log::debug("Updating configuration files");

        $knownSources = Hash::extract($this->Info->frameworkMeta, '{s}.source');
        if (!in_array($this->options['source'], $knownSources)) {
            Log::debug("* Skipping: automated configuration updates are not supported for user specified applications");
            return true;
        }

        Log::debug("Updating " . $this->options['framework_human'] . " config files");

        if ($this->options['framework_short'] == 'cakephp3') {
            if (!$this->Execute->updateCake3Configuration($this->options['path'], $this->options['url'])) {
                throw new Exception("Error updating config file.");
            }
        }

        if ($this->options['framework_short'] == 'cakephp2') {
            if (!$this->Execute->updateCake2Configuration($this->options['path'], $this->options['url'])) {
                throw new Exception("Error updating config file.");
            }
        }
        return true;
    }
}
