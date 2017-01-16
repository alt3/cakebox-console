<?php
namespace App\Lib;

use App\Lib\CakeboxInfo;
use Cake\Cache\Cache;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;

/**
 * Class library for checking against box requirements, states and conditions.
 */
class CakeboxCheck
{

    /**
     * CakeboxInfo instance
     *
     * @var App\Lib\CakeboxInfo
     */
    protected $Info;

    /**
     * Global box requirements. Basically the sum of the most demanding minimal
     * requirements for ANY framework's latest version.
     *
     * @var array Hash
     */
    protected $boxRequirements = [
        'global' => [
            'php_min_version' => '5.4.16',
            'php_modules' => ['mbstring', 'openssl', 'mcrypt', 'intl', 'pdo_sqlite']
        ]
    ];

    /**
     * @var array Hash containing framework specific requirements.
     */
    protected $frameworkRequirements = [
        'cakephp3' => [
            'writeables' => ['tmp', 'logs']
        ],
        'laravel' => [
            'writables' => ['/todo/']
        ]
    ];

    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->Info = new CakeboxInfo;
    }

    /**
     * Returns an array containing all system wide (non-appliction) platform checks.
     *
     * @return array Named array
     */
    public function getSystemChecks()
    {
        // php version
        $result[] = $this->validatePhpVersion();

        // php modules
        foreach ($this->boxRequirements['global']['php_modules'] as $module) {
            $result[] = $this->validatePhpModule($module);
        }

        return $result;
    }

    /**
     * Checks if the PHP version meets the minimum PHP version required for the least demanding framework.
     *
     * @return array Named array containing "messsage" string and "pass" boolean
     */
    public function validatePhpVersion()
    {
        $minversion = $this->boxRequirements['global']['php_min_version'];
        if (version_compare(PHP_VERSION, $minversion, '>=')) {

            return [
                'name' => 'php_version',
                'message' => "System version of PHP is $minversion or higher",
                'pass' => true
            ];
        }

        return [
            'name' => 'php_version',
            'message' => "System version of PHP is too low. We need PHP $minversion or higher to support all frameworks",
            'pass' => false
        ];
    }

    /**
     * Checks if a PHP module is loaded.
     *
     * @param string $module Containing name of the PHP module.
     * @return array Named array containing "messsage" string and "pass" boolean
     */
    public function validatePhpModule($module)
    {
        if (extension_loaded($module)) {
            return [
                'name' => "php_module_$module",
                'message' => "System version of PHP has the $module extension loaded",
                'pass' => true
            ];
        }

        return [
            'name' => "php_module_$module",
            'message' => "System version of PHP does NOT have the $module extension loaded",
            'pass' => false
        ];
    }

    /**
     * Returns an array containing all application checks for a given application.
     *
     * @param string $appdir Containing path of the application to validate.
     * @return array Named array
     */
    public function getApplicationChecks($appdir)
    {
        $framework = $this->Info->getFrameworkCommonName($appdir);

        // writebale directories
        foreach ($this->frameworkRequirements[$framework]['writeables'] as $dir) {
            $result[] = $this->getWriteableDirectoryCheck($appdir . DS . $dir);
        }

        // cache and database
        $result[] = $this->getApplicationCacheCheck();
        $result[] = $this->getApplicationDatabaseCheck();

        return $result;
    }

    /**
     * Check if a directory is writeable.
     *
     * @param string $path Path to directory.
     * @return bool True when writeable
     */
    public function getWriteableDirectoryCheck($path)
    {
        if (is_writable($path)) {
            return [
                'name' => 'writeable_' . str_replace('/', '_', $path),
                'message' => "Application directory $path is writable",
                'pass' => true
            ];
        }

        return [
            'name' => 'writeable_' . str_replace('/', '_', $path),
            'message' => "Application directory $path is NOT writable",
            'pass' => false
        ];
    }

    /**
     * Check if an application's caching is properly configured and working.
     *
     * Note: this check now uses cakebox-console context but should ideally be
     * update so it can be used for any app (thus enriching the dashboard)
     *
     * @return array Named array containing "messsage" string and "pass" boolean
     */
    public function getApplicationCacheCheck()
    {
        $settings = Cache::config('_cake_core_');
        if (!empty($settings)) {
            return [
                'name' => 'cache_engine',
                'message' => 'Application uses the <em>' . $settings['className'] . ' Engine</em> for core caching',
                'pass' => true
            ];
        }

        return [
            'name' => 'cache_engine',
            'message' => 'Application cache is NOT working. Please check the settings in config/app.php',
            'pass' => false
        ];
    }

    /**
     * Check if an application's database is properly configured and connectable.
     *
     * @return array Named array containing "messsage" string and "pass" boolean
     */
    public function getApplicationDatabaseCheck()
    {
        if ($this->validateDatabaseConnection()) {
            return [
                'name' => 'database_connection',
                'message' => 'Application is able to connect to the database',
                'pass' => true
            ];
        }

        return [
            'name' => 'database_connection',
            'message' => 'Application is NOT able to connect to the database',
            'pass' => false
        ];
    }

    /**
     * Test if a connection can be made to a database.
     *
     * Note: this function now uses cakebox-console context but should ideally
     * be made framework/version agnostic so that it could perform the check
     * for any app (and then be used to enrich dashboard application info).
     *
     * Note: errorMsg is currently not displaying for failed connections.
     *
     * @return bool Success when a connection was made successfully
     */
    public function validateDatabaseConnection()
    {
        try {
            $connection = ConnectionManager::get('default');
            $connection->connect();
            $connection->disconnect();

            return true;
        } catch (Exception $connectionError) {
            $errorMsg = $connectionError->getMessage();
            if (method_exists($connectionError, 'getAttributes')) {
                $attributes = $connectionError->getAttributes();
                if (isset($errorMsg['message'])) {
                    $errorMsg .= '<br />' . $attributes['message'];
                }
            }
        }

        return false;
    }

    /**
     * Returns an array containing all system wide (non-appliction) security checks.
     *
     * @return array Named array
     */
    public function getSecurityChecks()
    {
        return [
            [
                'name' => 'dashboard_password',
                'message' => 'Your cakebox dashboard is NOT using a password!',
                'pass' => false
            ],
            [
                'name' => 'ssh_keypair',
                'message' => 'SSH access to your cakebox is protected by your personal key pair',
                'pass' => true
            ],
            [
                'name' => 'database_root',
                'message' => 'Your database server is using the default root password!',
                'pass' => false
            ],
            [
                'name' => 'database_remote',
                'message' => 'Your remote database user is using the default password!',
                'pass' => false
            ]
        ];
    }
}
