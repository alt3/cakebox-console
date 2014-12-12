<?php
namespace App\Lib;

use App\Lib\CakeboxInfo;
use App\Lib\CakeboxUtility;
use Cake\Cache\Cache;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Hash;

/**
 * Class library for checking against box requirements, states and conditions.
 */
class CakeboxCheck {

/**
 * CakeboxInfo instance
 *
 * @var App\Lib\CakeboxInfo
 */
	protected $_cbi;

/**
 * Global box requirements. Basically the sum of the most demanding minimal
 * requirements for ANY framework's latest version.
 *
 * @var array Hash
 */
	protected $_boxRequirements = [
		'global' => [
			'php_min_version' => '5.4.16',
			'php_modules' => ['mbstring', 'mcrypt', 'intl', 'pdo_sqlite']
		]
	];

/**
 * @var array Hash containing framework specific requirements.
 */
	protected $_frameworkRequirements = [
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
	public function __construct() {
		$this->_cbi = new CakeboxInfo;
	}

/**
 * Returns an array containing all system wide (non-appliction) platform checks.
 *
 * @return array Named array
 */
	public function getSystemChecks() {
		$result = [];
		$result['php_version'] = $this->validatePhpVersion();
		foreach ($this->_boxRequirements['global']['php_modules'] as $module) {
			$result[$module] = $this->validatePhpModule($module);
		}
		return $result;
	}

/**
* Checks if the PHP version meets the minimum PHP version required for the least demanding framework.
*
* @return array Named array containing "messsage" string and "pass" boolean
*/
	public function validatePhpVersion(){
		$minversion = $this->_boxRequirements['global']['php_min_version'];
		if (version_compare(PHP_VERSION, $minversion, '>=')) {
			return ['message' => "System version of PHP is $minversion or higher", "pass" => true];
		}
		return ['message' => "System version of PHP is too low. We need PHP $minversion or higher to support all frameworks", "pass" => false];
	}

/**
* Checks if a PHP module is loaded.
*
* @param string Containing name of the PHP module
* @return array Named array containing "messsage" string and "pass" boolean
*/
	public function validatePhpModule($module) {
		if (extension_loaded($module)) {
			return ['message' => "System version of PHP has the $module extension loaded", "pass" => true];
		}
		return ['message' => "Our version of PHP does NOT have the $module extension loaded", "pass" => false];
	}

/**
 * Returns an array containing all application checks for a given application.
 *
 * @param string Containing path of the application to validate
 * @return array Named array
 */
	public function getApplicationChecks($appdir) {
		$framework = $this->_cbi->getFrameworkCommonName($appdir);

		$checks = [];
		foreach ($this->_frameworkRequirements[$framework]['writeables'] as $dir) {
			$checks[$dir] = $this->getWriteableDirectoryCheck($appdir . DS . $dir);
		}
		$checks['cache'] = $this->getApplicationCacheCheck();
		$checks['database'] = $this->getApplicationDatabaseCheck();
		return $checks;
	}

/**
 * Check if a directory is writeable.
 *
 * @param string Path to directory
 * @return bool True when writeable
 */
	public function getWriteableDirectoryCheck($path) {
		if (is_writable($path)) {
			return ['message' => "Directory $path is writable", "pass" => true];
		}
		return ['message' => "Directory $path is NOT writable", "pass" => false];
	}

/**
 * Check if an application's caching is properly configured and working.
 *
 * @todo make the check work for any app (now uses cakebox-console context)
 *
 * @return array Named array containing "messsage" string and "pass" boolean
 */
	public function getApplicationCacheCheck(){
		$settings = Cache::config('_cake_core_');
		if (!empty($settings)) {
			return ['message' => "The <em>" . $settings['className'] . " Engine</em> is being used for core caching.", "pass" => true];
		}
		return ['message' => "Our cache is NOT working. Please check the settings in config/app.php", "pass" => false];
	}

/**
 * Check if an application's database is properly configured and connectable.
 *
 * @return array Named array containing "messsage" string and "pass" boolean
 */
	public function getApplicationDatabaseCheck(){
		if ($this->validateDatabaseConnection()) {
			return ['message' => "CakePHP is able to connect to the database", "pass" => true];
		}
		return ['message' => "CakePHP is NOT able to connect to the database", "pass" => false];
	}

/**
 * Test if a connection can be made to a database.
 *
 * @todo make framework/version agnostic (now uses cakebox-console context).
 * @todo fix errorMsg not displaying for failed connections
 *
 * @return bool Success when a connection was made successfully
 */
	public function validateDatabaseConnection(){
		try {
			$connection = ConnectionManager::get('default');
			$connected = $connection->connect();
			$connected = $connection->disconnect();
			return true;
		} catch (Exception $connectionError) {
			$connected = false;
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
	public function getSecurityChecks() {
		$result = [];
		$result['website']['message'] = "Your cakebox management website is NOT using a password!";
		$result['website']['pass'] = false;
		return $result;
	}

}
