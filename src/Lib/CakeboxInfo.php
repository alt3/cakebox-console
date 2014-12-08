<?php
namespace App\Lib;

use Cake\Core\App;
use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\Folder;
use Cake\Filesystem\File;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Memcached;

/**
 * Class library used for retrieving box information
 */
class CakeboxInfo {

/**
 * Connection instance connected to the MySQL server (and not a specific database)
 *
 * @var \Cake\Database\Connection
 */
	protected $_conn;

/**
 * @var array Hash with webserver specific information.
 */
	public $webserverMeta = [
		'nginx' => [
			'sites-available' => '/etc/nginx/sites-available',
			'sites-enabled' => '/etc/nginx/sites-enabled'
		]
	];

/**
 * @var array Hash with database specific information.
 */
	public $databaseMeta = [
		'mysql' => [
			'system_databases' => [	'mysql', 'information_schema', 'performance_schema', 'test' ]
		]
	];

/**
 * Array with links for PHP modules we cannot generate a generic (php.net) link for.
 *
 * @var array Hash
 */
	public $phpModuleMeta = [
		'Core'			=> ['link' => false],
		'Zend OPcache'	=> ['link' => 'http://php.net/manual/en/book.opcache.php'],
		'apcu'			=> ['link' => 'http://pecl.php.net/package/APCu'],
		'bz2'			=> ['link' => 'http://php.net/manual/en/book.bzip2.php'],
		'cgi-fcgi'		=> ['link' => 'http://www.fastcgi.com/drupal/node/5?q=node/10'],
		'redis'			=> ['link' => 'https://github.com/nicolasff/phpredis'],
		'standard'		=> ['link' => false],
		'sysvmsg'		=> ['link' => 'http://php.net/manual/en/book.sem.php'],
		'sysvsem'		=> ['link' => 'http://php.net/manual/en/ref.sem.php'],
		'sysvshm'		=> ['link' => 'http://php.net/manual/en/ref.shmop.php'],
		'xdebug'		=> ['link' => 'http://xdebug.org']
	];

/**
 * Array with links for Nginx modules we cannot generate a generic (nginx.org) link for.
 *
 * @var array Hash
 */
	public $nginxModuleMeta = [
		'auth-pam' 					=> ['link' => 'http://web.iti.upv.es/~sto/nginx'],
		'dav-ext' 					=> ['link' => 'https://github.com/arut/nginx-dav-ext-module'],
		'http_substitutions_filter'	=> ['link' => 'http://wiki.nginx.org/HttpSubsModule']
	];

/**
 * List with installed Ubuntu packages we want to appear on the "Box Software" tab.
 *
 * @var array Hash
 */
	protected $_packages = [
		'git'        => ['link' => 'https://launchpad.net/~git-core'],
		'nginx'      => ['link' => 'https://launchpad.net/nginx'],
		'php'        => ['link' => 'https://launchpad.net/~ondrej/+archive/ubuntu/php5-5.6'],
		'mysql'      => ['link' => 'http://www.percona.com/software/percona-server'],
		'redis'      => [
			'link'  => 'https://launchpad.net/~chris-lea/+archive/ubuntu/redis-server',
			'alias' => 'redis-server'
		],
		'postgresql' => [
			'link'  => 'http://www.postgresql.org',
			'alias' => 'psql'
		],
		'curl'       => ['link' => 'http://curl.haxx.se'],
		'composer'   => ['link' => 'https://getcomposer.org'],
		'phpunit'    => ['link' => 'https://phpunit.de'],
		'phpcs'      => ['link' => 'https://github.com/squizlabs/PHP_CodeSniffer'],
		'heroku'     => ['link' => 'https://toolbelt.heroku.com]'],
		'ruby'		 => ['link' => 'https://www.ruby-lang.org/en'],
		'memcached'	 => ['link' => 'http://memcached.org']
	];


/**
 * Class constructor
 *
 * @return void
 */
	public function __construct() {
		$this->_conn = ConnectionManager::get('default');
	}

/**
 * Return an instance of the database connection.
 *
 * @return \Cake\Database\Connection
 */
	public function getConnection() {
		return $this->_conn;
	}

/**
 * Convenience function used to retrieve basic box info in a single call.
 *
 * @return array Named array
 */
	public function getVmInfo() {
		return ([
			'hostname' => $this->getHostname(),
			'ip_address' => $this->getPrimaryIpAddress(),
			'cpus' => $this->getCpuCount(),
			'memory' => $this->getMemory()
			]);
		}

/**
 * Returns the hostname used by the vm.
 *
 * @return string Hostname
 */
		public function getHostname() {
			return gethostname();
		}

/**
 * Returns the primary (external) IP address used by the vm.
 *
 * @return string Hostname
 */
		public static function getPrimaryIpAddress() {
			return $_SERVER['SERVER_ADDR'];
		}

/**
 * Returns the number of virtual CPUs assigned to the vm.
 *
 * @return int Virtual CPU count
 */
		public static function getCpuCount() {
			$cpuinfo = file_get_contents('/proc/cpuinfo');
			preg_match_all('/^processor/m', $cpuinfo, $matches);
			return count($matches[0]);
		}

/**
 * Returns the amount of virtual memory assigned to the vm in MBs.
 *
 * @return int Virtual memory in MB
 */
		public static function getMemory() {
			$meminfo = file_get_contents('/proc/meminfo');
			preg_match_all('/^MemTotal:\\s++(\\d*) kB/m', $meminfo, $matches);
			return (round($matches[1][0] / 1024));
		}

/**
 * Returns a hash with rich information as displayed by `lsb-release -a`
 *
 * @return array Hash
 */
	public function getOperatingSystem() {
		$lines = file('/etc/lsb-release');
		$specs = [];
		foreach ($lines as $line) {
			$pair = explode("=", $line);
			$specs[$pair[0]] = $pair[1];
		}
		// no need to determine the architecture for our box, just add
		$specs['architecture'] = "64-bit (x86_64)";
		return $specs;
	}

/**
 * Return an simple array with all Nginx site files found in /etc/nginx/sites-available.
 *
 * @return array Simple array with found filenames
 */
	public function getNginxFiles() {
		$dir = new Folder($this->webserverMeta['nginx']['sites-available']);
		return $dir->find('.*', 'sort');
	}
/**
 * Returns an array holding all Nginx site files found in /etc/nginx/sites-available
 * enriched with information.
 *
 * @return array Enriched array with found filenames
 */
	public function getRichNginxFiles() {
		foreach ($this->getNginxFiles() as $file) {
			$result[] = [
				'name' => $file,
				'enabled' => is_link($this->webserverMeta['nginx']['sites-enabled'] . DS . $file),
				'modified' => (new File($this->webserverMeta['nginx']['sites-available'] . DS . $file))->lastChange()
			];
		}
		return $result;
	}

/**
 * Return the exact number of Nginx sites files found in /etc/nginx/sites-available.
 *
 * @return int Number of files
 */
	public function getNginxFileCount() {
		return count($this->getRichNginxFiles());
	}

/**
 * Return a list of databases on the vm excluding system/protected databases.
 *
 * @return array List holding database names
 */
	public function getAppDatabases() {
		try {
			$stmt = $this->_conn->execute('SHOW DATABASES');
			$rows = Hash::extract($stmt->fetchall(), '{n}.{n}');
			$stripped = array_diff($rows, $this->databaseMeta['mysql']['system_databases']);
			return $stripped;
		} catch (\Exception $e) {
			throw new \Exception("Error generating database list: " . $e->getMessage());
		}
	}

/**
 * Return the number of databases on the vm excluding system/protected databases.
 *
 * @return int Database count
 */
	public function getDatabaseCount() {
		return count(self::getAppDatabases());
	}

/**
 * Returns an array with enriched information about installed Ubuntu packages
 * specified in $this->pacakgeMeta.
 *
 * @return array Single dimensional array holding Hashes
 */
	public function getPackages() {
		$result = [];
		foreach ($this->_packages as $package => $details) {
			if ($package == "memcached") {
				$version = $this->_getPackageVersionMemcached();
			} else {
				if (array_key_exists('alias', $details)){
					$version = $this->_getPackageVersionGeneric($details['alias']);
				} else {
					$version = $this->_getPackageVersionGeneric($package);
				}
			}
			$result[] = [
				'name' => $package,
				'version' => $version,
				'link' => $this->_packages[$package]['link']
			];
		}
		sort($result);
		return $result;
	}

/**
 * Returns the version of the selected pacakge by shelling into bash.
 *
 * @return mixed Version of package or false if no version could be determined
 */
	protected function _getPackageVersionGeneric($package) {
		$stdout = `2>&1 $package --version`;
		preg_match('/(\d*\.\d*\.\d*-\d*\.\d*|\d*\.\d*\.\d*-\d*|\d*\.\d*\.\d*|\d*\.\d*-\w+)/m', $stdout, $matches);
		if (!empty($matches[1])) {
			return ($matches[1]);
		}

		// No match on --version so let's try --v
		$stdout = `2>&1 $package -v`;
		preg_match('/(\d*\.\d*\.\d*-\d*\.\d*|\d*\.\d*\.\d*-\d*|\d*\.\d*\.\d*|\d*\.\d*-\w+)/m', $stdout, $matches);
		if (!empty($matches[1])) {
			return ($matches[1]);
		}
		return false;
	}

/**
 * Returns the Memcached version by connecting locally (since Memcached does not
 * support the generic --version method.
 *
 * @return string Installed Memcached version
 */
	protected function _getPackageVersionMemcached() {
		try {
			$m = new Memcached();
			$m->addServer('localhost', 11211);
			$version = $m->getVersion();
			return ($version['localhost:11211']);
		} catch (\Exception $e) {
			return false;
		}
	}

/**
 * Returns an array with enriched information about ALL installed PHP modules.
 *
 * @return array
 */
	public function getPhpModules() {
		$modules = get_loaded_extensions();
		sort($modules);

		// add standard php.net hyperlink except for known deviations
		$result = [];
		foreach ($modules as $module){
			if (array_key_exists($module, $this->phpModuleMeta)) {
				$link = $this->phpModuleMeta[$module]['link'];
			}else{
				$link = "http://php.net/en/$module";
			}
			$result[] = [
				'name' => $module,
				'link' => $link
			];
		}
		return ($result);
	}

/**
* Returns an array with enriched information about ALL installed Nginx modules.
 *
 * Modules can determined by running `nginx -V`and analysing arguments like this:
 * => Core modules:
 *    - argument is prefixed with "--with-"
 *    - argument is suffixed with  "_module"
 *
 * => 3rd party modules:
 *    - argument is prefixed with "--add-module="
 *    - module name found after the last /
 *    - module name always prefixed with either "nginx-" or "ngx_"
 *
 * @return array Single dimensional array with key/value pair collections
 */
	public function getNginxModules() {
		// shell command since no other option seems available
		$stdout = `2>&1 nginx -V | xargs -n1`;
		$lines = explode("\n", $stdout);

		// prepare the result array
		$result = [
			'core' => [],
			'3rdparty' => []
		];

		foreach ($lines as $module) {
			// Extracts core modules
			if (preg_match('/^--with-((.{4})_(.*)_module)/m', $module, $matches)) {
				$module = $matches[1];
				$shortName = $this->_stripNginxModuleName($module);
				$category = $matches[2];

				$result['core'][] = [
					'name' => $module,
					'short_name' => $shortName,
					'category' => $category,
					'link' => "http://nginx.org/en/docs/" . $category . "/ngx_" . $module . ".html"
				];
			}

			// Extracts 3rd party modules
			if (preg_match('/^--add-module=(.*)\/((ngx_|nginx-)(.*))/m', $module, $matches)) {
				$module = $matches[2];
				$shortname = $this->_stripNginxModuleName($module);

				// Either use the known-deviation-link or generate the generic 3rd party wiki-link
				if (array_key_exists($shortname, $this->nginxModuleMeta)) {
					$link = $this->nginxModuleMeta[$shortname]['link'];
				} else {
					$wikiName = preg_replace('/-/', '_', $shortname);
					$wikiName = "http_" . $wikiName . "_module";
					$wikiName = Inflector::classify($wikiName);
					$link = "http://wiki.nginx.org/$wikiName";
				}

				$result['3rdparty'][] = [
				'name' => $module,
				'short_name' => $shortname,
				'link' => $link
				];
			}
		}
		return ($result);
	}

/**
 * Removes redundant information from Nginx module arguments to create short name.
 *
 * @param string Name of the Nginx module as seen in `nginx -V`
 * @return string Short name of the module
 */
	protected static function _stripNginxModuleName($module) {
		$result = preg_replace('/.module$/', '', $module);			// removes trailing -module or _module
		$result = preg_replace("/^\w{4}\_/", '', $result);			// removes leading category identifier (e.g. http or mail) for core modules
		$result = preg_replace("/^nginx-||ngx_/", '', $result);		// removes leading nginx- prefix for 3rd party modules
		return $result;
	}

/**
* Returns an array with enriched information about detected framework applications.
 *
* @return array Single dimensional array holding Hashes
 */
	public function getApps() {
		$result = [];
		foreach ($this->getNginxFiles() as $sitefile) {
			$appname = $this->getAppName($sitefile);
			$appdir = $this->getAppBase($sitefile);

			if ($appdir) {
				$framework_version = $this->getFrameworkVersion($appdir);
				$result[] = [
					'name' => $appname,
					'framework' => $this->getFrameworkName($appdir),
					'framework_major_version' => CakeboxUtility::getMajorVersion($framework_version),
					'framework_version' => $framework_version,
					'appdir' => $appdir,
					'webroot' => $this->getAppWebRoot($sitefile)
				];
			}
		}
		return $result;
	}

/**
 * Get the application name by retrieving it's Nginx "server_name" directive
 *
 * @param string Full path to application's Nginx site configuration file
 * @return mixed Containing application name
 */
	public function getAppName($sitefile) {
		$name = CakeboxUtility::getNginxFileSetting($sitefile, 'server_name');
		if ($name == '_') {
			return "cakebox";
		}
		return $name;
	}

/**
 * Get the application's webroot directory by retrieving it's Nginx "root" directive
 *
 * @param string Full path to application's Nginx site configuration file
 * @return mixed Containing application name
 */
	public function getAppWebroot($sitefile){
		$webroot = CakeboxUtility::getNginxFileSetting($sitefile, 'root');
		return $webroot;
	}

/**
 * Get the application's root/base directory by parsing it's Nginx "root" directive
 *
 * @param string Full path to application's Nginx site configuration file
 * @return mixed Containing application name
 */
	public function getAppBase($sitefile){
		$webroot = $this->getAppWebRoot($sitefile);

		$cake2base = substr($webroot, 0, strrpos( $webroot, '/app/webroot'));
		if (is_dir($cake2base)) {
			return ($cake2base);
		}

		$cake3base = substr($webroot, 0, strrpos( $webroot, '/webroot'));
		if (is_dir($cake3base)) {
			return ($cake3base);
		}

		$laravelbase = substr($webroot, 0, strrpos( $webroot, '/public'));
		if (is_dir($laravelbase)) {
			return ($laravelbase);
		}
		return false;
	}

/**
 * Detect if a directory contains a framework application.
 *
 * @param string Full path of directory to check
 * @return array Single dimensional array with key/value pair collections
 */
	public function isFrameworkDirectory($path){
		if ($this->getFrameworkName($path)){
			return true;
		}
		return false;
	}

 /**
 * Returns the name of the framework for any given application root directory.
 *
 * @param string Full path to the application's root directory
 * @return array|bool Single dimensional array with key/value pair collections, false on fails
 */
	public function getFrameworkName($appdir){
		if (is_dir("$appdir/vendor/cakephp")){
			return ("cakephp");
		}
		if (file_exists("$appdir/lib/Cake/VERSION.txt")){
			return ("cakephp");
		}
		if (is_dir("$appdir/public")) {
			return ("laravel");
		}
		return false;
	}

/**
 * Get the common framework name used by an application by concatenating it's
 * framework name and major version (e.g. cakephp2, cakephp3, laravel4)
 *
 * @param string Full path to the application's root directory
 * @return array|bool Single dimensional array with key/value pair collections, false on fails
 */
	public function getFrameworkCommonName($appdir){
		$framework = $this->getFrameworkName($appdir);
		$majorVersion = CakeboxUtility::getMajorVersion($this->getFrameworkVersion($appdir));
		return $framework . $majorVersion;
	}

/**
 * Retrieve the specific framework version of an application.
 *
 * @todo harden file read (prevent break when not found)
 *
 * @param string Full path to application directory
 * @return string|bool Version of the framework, false if not found
 */
	function getFrameworkVersion($appdir) {
		$cake3file = "$appdir/vendor/cakephp/cakephp/VERSION.txt";
		if (file_exists($cake3file)){
			$lines = file($cake3file);
			return (trim($lines[count($lines)-1]));
		}

		$cake2file = "$appdir/lib/Cake/VERSION.txt";
		if (file_exists($cake2file)){
			$lines = file($cake2file);
			return (trim($lines[count($lines)-1]));
		}

		// Use composer.lock for Laravel
		$laravelfile = "$appdir/composer.lock";
		if (file_exists($laravelfile)){
			return CakeboxUtility::getComposerLockVersion($laravelfile, 'laravel/framework');
		}
		return false;
	}

}
