<?php
namespace App\Lib;

use Cake\Cache\Cache;
use Cake\Core\App;
use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\Network\Http\Client;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Memcached;

/**
 * Class library used for retrieving box information
 */
class CakeboxInfo
{

    /**
     * Connection instance connected to the MySQL server (and not a specific database)
     *
     * @var \Cake\Database\Connection
     */
    protected $conn;

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
            'system_databases' => [ 'mysql', 'information_schema', 'performance_schema', 'test' ]
        ]
    ];

    /**
     * Array with links for PHP modules we cannot generate a generic (php.net) link for.
     *
     * @var array Hash
     */
    public $phpModuleMeta = [
        'Core' => ['link' => false],
        'Zend OPcache' => ['link' => 'http://php.net/manual/en/book.opcache.php'],
        'apcu' => ['link' => 'http://pecl.php.net/package/APCu'],
        'bz2' => ['link' => 'http://php.net/manual/en/book.bzip2.php'],
        'cgi-fcgi' => ['link' => 'http://www.fastcgi.com/drupal/node/5?q=node/10'],
        'redis' => ['link' => 'https://github.com/nicolasff/phpredis'],
        'standard' => ['link' => false],
        'sysvmsg' => ['link' => 'http://php.net/manual/en/book.sem.php'],
        'sysvsem' => ['link' => 'http://php.net/manual/en/ref.sem.php'],
        'sysvshm' => ['link' => 'http://php.net/manual/en/ref.shmop.php'],
        'xdebug' => ['link' => 'http://xdebug.org']
    ];

    /**
     * Array with links for Nginx modules we cannot generate a generic (nginx.org) link for.
     *
     * @var array Hash
     */
    public $nginxModuleMeta = [
        'auth-pam' => ['link' => 'http://web.iti.upv.es/~sto/nginx'],
        'dav-ext' => ['link' => 'https://github.com/arut/nginx-dav-ext-module'],
        'http_substitutions_filter' => ['link' => 'http://wiki.nginx.org/HttpSubsModule']
    ];

    /**
     * List with installed Ubuntu packages we want to appear on the "Box Software" tab.
     *
     * @var array Hash
     */
    protected $packages = [
        'composer' => ['link' => 'https://getcomposer.org'],
        'curl' => ['link' => 'http://curl.haxx.se'],
        'elasticsearch' => ['link' => 'https://www.elasticsearch.org'],
        'git' => ['link' => 'https://launchpad.net/~git-core'],
        'java' => ['link' => 'http://openjdk.java.net'],
        'heroku' => ['link' => 'https://toolbelt.heroku.com'],
        'kibana' => ['link' => 'https://www.elasticsearch.org/overview/kibana'],
        'logstash' => ['link' => 'http://logstash.net'],
        'mysql' => ['link' => 'http://www.percona.com/software/percona-server'],
        'memcached' => ['link' => 'http://memcached.org'],
        'nginx' => ['link' => 'https://launchpad.net/nginx'],
        'php' => ['link' => 'https://launchpad.net/~ondrej/+archive/ubuntu/php5-5.6'],
        'phpunit' => ['link' => 'https://phpunit.de'],
        'phpcs' => ['link' => 'https://github.com/squizlabs/PHP_CodeSniffer'],
        'postgresql' => [
            'link' => 'http://www.postgresql.org',
            'alias' => 'psql'
        ],
        'python' => ['link' => 'https://www.python.org'],
        'redis' => [
            'link' => 'https://launchpad.net/~chris-lea/+archive/ubuntu/redis-server',
            'alias' => 'redis-server'
        ],
        'ruby' => ['link' => 'https://www.ruby-lang.org/en']
    ];

    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->_conn = ConnectionManager::get('default');
    }

    /**
     * Return an instance of the database connection.
     *
     * @return \Cake\Database\Connection
     */
    public function getConnection()
    {
        return $this->_conn;
    }

    /**
     * Convenience function used to retrieve basic box info in a single call.
     *
     * @return array Named array
     */
    public function getVmInfo()
    {
        return ([
            'hostname' => $this->getHostname(),
            'ip_address' => $this->getPrimaryIpAddress(),
            'cpus' => $this->getCpuCount(),
            'memory' => $this->getMemory(),
            'uptime' => $this->getUptime()
        ]);
    }

    /**
     * Returns the hostname used by the vm.
     *
     * @return string Hostname
     */
    public function getHostname()
    {
        return gethostname();
    }

    /**
     * Returns the primary (external) IP address used by the vm.
     *
     * @return string Hostname
     */
    public function getPrimaryIpAddress()
    {
        return (getenv('SERVER_ADDR'));
    }

    /**
     * Returns the number of virtual CPUs assigned to the vm.
     *
     * @return int Virtual CPU count
     */
    public static function getCpuCount()
    {
        $cpuinfo = file_get_contents('/proc/cpuinfo');
        preg_match_all('/^processor/m', $cpuinfo, $matches);
        return count($matches[0]);
    }

    /**
     * Returns the amount of virtual memory assigned to the vm in MBs.
     *
     * @return int Virtual memory in MB
     */
    public static function getMemory()
    {
        $meminfo = file_get_contents('/proc/meminfo');
        preg_match_all('/^MemTotal:\\s++(\\d*) kB/m', $meminfo, $matches);
        return round($matches[1][0] / 1024);
    }

    /**
     * Returns a hash with rich information as displayed by `lsb-release -a`
     *
     * @return array Hash
     */
    public function getOperatingSystem()
    {
        $lines = file('/etc/lsb-release');
        $specs = [];
        foreach ($lines as $line) {
            $pair = explode("=", $line);
            $specs[$pair[0]] = str_replace('"', '', $pair[1]);
        }
     // no need to determine the architecture for our box, just add
        $specs['architecture'] = "64-bit (x86_64)";
        return $specs;
    }

    /**
     * Returns the amount of virtual memory assigned to the vm in MBs.
     *
     * @return int Virtual memory in MB
     */
    public static function getUptime()
    {
        $stdout = `2>&1 cut -d. -f1 /proc/uptime`;
        return [
            'days' => floor($stdout / 60 / 60 / 24),
            'hours' => $stdout / 60 / 60 % 24,
            'minutes' => $stdout / 60 % 60,
            'seconds' => $stdout % 60
        ];
    }

    /**
     * Return an simple array with all Nginx site files found in /etc/nginx/sites-available.
     *
     * @return array Simple array with found filenames
     */
    public function getNginxFiles()
    {
        $dir = new Folder($this->webserverMeta['nginx']['sites-available']);
        return $dir->find('.*', 'sort');
    }
    /**
     * Returns an array holding all Nginx site files found in /etc/nginx/sites-available
     * enriched with information.
     *
     * @return array Enriched array with found filenames
     */
    public function getRichNginxFiles()
    {
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
    public function getNginxFileCount()
    {
        return count($this->getRichNginxFiles());
    }

    /**
     * Return a list of databases on the vm excluding system/protected databases.
     *
     * @return array List holding database names
     */
    public function getAppDatabases()
    {
        try {
            $stmt = $this->_conn->execute('SHOW DATABASES');
            $rows = Hash::extract($stmt->fetchall(), '{n}.{n}');
            $stripped = array_diff($rows, $this->databaseMeta['mysql']['system_databases']);
            $result = [];
            foreach ($stripped as $databaseName) {
                $result[] = ['name' => $databaseName];
            }
            return $result;
        } catch (\Exception $e) {
            throw new \Exception("Error generating database list: " . $e->getMessage());
        }
    }

    /**
     * Return the number of databases on the vm excluding system/protected databases.
     *
     * @return int Database count
     */
    public function getDatabaseCount()
    {
        return count(self::getAppDatabases());
    }

    /**
     * Returns an array with enriched information about installed Ubuntu packages
     * specified in $this->pacakgeMeta.
     *
     * @return array Single dimensional array holding Hashes
     */
    public function getPackages()
    {
        $result = [];
        foreach ($this->packages as $package => $details) {
             // fetch version
            switch($package) {
                case 'memcached':
                    $version = $this->_getPackageVersionMemcached();
                    break;
                case 'elasticsearch':
                    $version = $this->_getPackageVersionElasticsearch();
                    break;
                case 'logstash':
                    $version = $this->_getPackageVersionLogstash();
                    break;
                case 'kibana':
                    $version = $this->_getPackageVersionKibana();
                    break;
                default:
                    if (array_key_exists('alias', $details)) {
                        $version = $this->_getPackageVersionGeneric($details['alias']);
                    } else {
                        $version = $this->_getPackageVersionGeneric($package);
                    }
            }

            $result[] = [
            'name' => $package,
            'version' => $version,
            'link' => $this->packages[$package]['link']
            ];
        }
        sort($result);
        return $result;
    }

    /**
     * Returns the version of the selected pacakge by shelling into bash.
     *
     * @param string $package Name of the package.
     * @return mixed Version of package or false if no version could be determined
     */
    protected function getPackageVersionGeneric($package)
    {
        $stdout = `2>&1 $package --version`;
        preg_match('/(\d*\.\d*\.\d*-\d*\.\d*|\d*\.\d*\.\d*-\d*|\d*\.\d*\.\d*|\d*\.\d*-\w+)/m', $stdout, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }

     // No match on --version so let's try --v
        $stdout = `2>&1 $package -v`;
        preg_match('/(\d*\.\d*\.\d*-\d*\.\d*|\d*\.\d*\.\d*-\d*|\d*\.\d*\.\d*|\d*\.\d*-\w+)/m', $stdout, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }

     // Edge case for e.g. java using -version
        $stdout = `2>&1 $package -version`;
        preg_match('/(\d*\.\d*\.\d*-\d*\.\d*|\d*\.\d*\.\d*-\d*|\d*\.\d*\.\d*|\d*\.\d*-\w+)/m', $stdout, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return false;
    }

    /**
     * Returns the Memcached version by connecting to the service locally since
     * Memcached does not support any of the generic version detection methods.
     *
     * @return string Installed Memcached version
     */
    protected function _getPackageVersionMemcached()
    {
        try {
            $m = new Memcached();
            $m->addServer('localhost', 11211);
            $version = $m->getVersion();
            return $version['localhost:11211'];
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Request the Elasticsearch version directly (since Elasticsearch does not
     * support any of the generic version detection methods.
     *
     * @return string Installed Elasticsearch version
     */
    public function _getPackageVersionElasticsearch()
    {
        try {
            $http = new Client();
            $response = $http->get('http://' . $this->getPrimaryIpAddress() . ':9200');
            $result = json_decode($response->body(), true);
            return $result['version']['number'];
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Return the Logstash version as found in version.rb file since requesting the
     * version using /bin/logstash --version takes way too long (known issue)
     *
     * @return string Installed Kibana version
     */
    protected function _getPackageVersionLogstash()
    {
        $file = '/opt/logstash/server/lib/logstash/version.rb';
        if (!file_exists($file)) {
            return false;
        }
        $lines = file_get_contents($file);
        preg_match('/(\d*\.\d*\.\d*-\d*\.\d*|\d*\.\d*\.\d*-\d*|\d*\.\d*\.\d*|\d*\.\d*-\w+)/m', $lines, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return false;
    }

    /**
     * Return the Kibana version as found in app.js
     *
     * @return string Installed Kibana version
     */
    protected function _getPackageVersionKibana()
    {
        try {
            $http = new Client();
            $response = $http->get('http://localhost:5601');
            preg_match('/window\.KIBANA_VERSION=\'(.*)\'/m', $response->body(), $matches);
            if (!empty($matches[1])) {
                return $matches[1];
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Returns an array with enriched information about ALL installed PHP modules.
     *
     * @return array
     */
    public function getPhpModules()
    {
        $modules = get_loaded_extensions();
        sort($modules);

     // add standard php.net hyperlink except for known deviations
        $result = [];
        foreach ($modules as $module) {
            if (array_key_exists($module, $this->phpModuleMeta)) {
                $link = $this->phpModuleMeta[$module]['link'];
            } else {
                $link = "http://php.net/en/$module";
            }
            $result[] = [
                'name' => $module,
                'link' => $link
            ];
        }
        return $result;
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
    public function getNginxModules()
    {
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
        return $result;
    }

    /**
     * Removes redundant information from Nginx module arguments to create short name.
     *
     * @param string $module Name of the Nginx module as seen in `nginx -V`.
     * @return string Short name of the module
     */
    protected static function _stripNginxModuleName($module)
    {
        // remove trailing -module or _module
        $result = preg_replace('/.module$/', '', $module);
        // remove leading category identifier (e.g. http or mail) for core modules
        $result = preg_replace("/^\w{4}\_/", '', $result);
        // remove leading nginx- prefix for 3rd party modules
        $result = preg_replace("/^nginx-||ngx_/", '', $result);
        return $result;
    }

    /**
     * Returns an array with enriched information about detected framework applications.
     *
     * @return array Single dimensional array holding Hashes
     */
    public function getApps()
    {
        $result = [];
        foreach ($this->getNginxFiles() as $sitefile) {
            $appname = $this->getAppName($sitefile);
            $appdir = $this->getAppBase($sitefile);

            if ($appdir) {
                $frameworkVersion = $this->getFrameworkVersion($appdir);
                $result[] = [
                    'name' => $appname,
                    'framework' => $this->getFrameworkName($appdir),
                    'framework_major_version' => CakeboxUtility::getMajorVersion($frameworkVersion),
                    'frameworkVersion' => $frameworkVersion,
                    'appdir' => $appdir,
                    'webroot' => $this->getWebrootFromSite($sitefile)
                ];
            }
        }
        return $result;
    }

    /**
     * Get the application name by retrieving it's Nginx "server_name" directive.
     *
     * @param string $sitefile Full path to application's Nginx site configuration file.
     * @return mixed Containing application name
     */
    public function getAppName($sitefile)
    {
        $name = CakeboxUtility::getNginxFileSetting($sitefile, 'server_name');
        if ($name == '_') {
            return "cakebox";
        }
        return $name;
    }

    /**
     * Get the application's webroot directory by retrieving it's Nginx "root" directive.
     *
     * @param string $sitefile Full path to application's Nginx site configuration file.
     * @return mixed Containing application name
     */
    public function getWebrootFromSite($sitefile)
    {
        $webroot = CakeboxUtility::getNginxFileSetting($sitefile, 'root');
        return $webroot;
    }

    /**
     * Get an application's webroot by looking for known webroot directories.
     *
     * @param string $appdir Full path to the application's root directory.
     * @return mixed String containing full path to found webroot directory
     */
    public function getWebrootFromDirectory($appdir)
    {
        if (is_dir($appdir . DS . 'webroot')) {
            return $appdir . DS . 'webroot';
        }

        if (is_dir($appdir . DS . 'app' . DS . 'webroot')) {
            return $appdir . DS . 'app' . DS . 'webroot';
        }

        if (is_dir($appdir . DS . 'public')) {
            return $appdir . DS . 'public';
        }

        throw new \Exception('Unable to determine webroot from application directory');
    }

    /**
     * Get the application's root/base directory by parsing it's Nginx "root" directive.
     *
     * @param string $sitefile Full path to application's Nginx site configuration file.
     * @return mixed Containing application name
     */
    public function getAppBase($sitefile)
    {
        $webroot = $this->getWebrootFromSite($sitefile);

        $cake2base = substr($webroot, 0, strrpos($webroot, '/app/webroot'));
        if (is_dir($cake2base)) {
            return $cake2base;
        }

        $cake3base = substr($webroot, 0, strrpos($webroot, '/webroot'));
        if (is_dir($cake3base)) {
            return $cake3base;
        }

        $laravelbase = substr($webroot, 0, strrpos($webroot, '/public'));
        if (is_dir($laravelbase)) {
            return $laravelbase;
        }
        return false;
    }

    /**
     * Detect if a directory contains a framework application.
     *
     * @param string $path Full path of directory to check.
     * @return array Single dimensional array with key/value pair collections
     */
    public function isFrameworkDirectory($path)
    {
        if ($this->getFrameworkName($path)) {
            return true;
        }
        return false;
    }

    /**
     * Returns the name of the framework for any given application root directory.
     *
     * @param string $appdir Full path to the application's root directory.
     * @return array|bool Single dimensional array with key/value pair collections, false on fails
     */
    public function getFrameworkName($appdir)
    {
     // Check for known CakePHP "fingerprint" directories first to keep things fast
        if (is_dir($appdir . DS . 'webroot')) {
            return 'cakephp';
        }
        if (is_dir($appdir . DS . 'app' . DS . 'webroot')) {
            return 'cakephp';
        }

     // Simply detected Laravel by webroot for now
        if (is_dir("$appdir/public")) {
            return "laravel";
        }

     // Initial checks failed, try detecting (legacy) Cake applications by searching for valid VERSION.txt
        if ($this->getCakeVersionFile($appdir)) {
            return 'cakephp';
        }
        return false;
    }

    /**
     * Retrieve the specific framework version of an application.
     *
     * @param string $appdir Full path to application directory.
     * @return string|bool String containing version
     */
    public function getFrameworkVersion($appdir)
    {
     // Look for version in composer.lock first
        $version = $this->getFrameworkVersionFromComposer($appdir);
        if ($version) {
            return $version;
        }

     // Look for CakePHP VERSION.txt next
        $version = $this->getCakeVersionFromFile($appdir);
        if ($version) {
            return $version;
        }
        return false;
    }

    /**
     * Get the framework version frm an application's composer.lock file.
     *
     * @param string $appdir Full path to application directory.
     * @return string|bool String containing version
     */
    public function getFrameworkVersionFromComposer($appdir)
    {
        $lockfile = $appdir . DS . 'composer.lock';
        if (!file_exists($lockfile)) {
            return false;
        }

        $packages = [
            'cakephp/cakephp',
            'pear-pear.cakephp.org',
            'laravel/framework'
        ];

        foreach ($packages as $package) {
            $version = CakeboxUtility::getComposerLockVersion($lockfile, $package);
            if ($version) {
                return $version;
            }
        }
        return false;
    }

    /**
     * Get the CakePHP framework version by parsing the application's VERSION.txt.
     *
     * Checks are done against "fingerprint" locations first to keep things fast
     * since the fallback method recursively searching the complete directory for
     * a (legacy?) VERSION.txt is a serious performance killer.
     *
     * @param string $appdir Full path to the application's root directory.
     * @return string|bool Full path to the CakePHP VERSION.txt if found
     */
    public function getCakeVersionFromFile($appdir)
    {
        $cake3file = $appdir . DS . 'vendor' . DS . 'cakephp' . DS . 'cakephp' . DS . 'VERSION.txt';
        if (file_exists($cake3file)) {
            $lines = file($cake3file);
            return trim($lines[count($lines) - 1]);
        }

        $cake2file = $appdir . DS . 'lib' . DS . 'Cake' . DS . 'VERSION.txt';
        if (file_exists($cake2file)) {
            $lines = file($cake2file);
            return trim($lines[count($lines) - 1]);
        }

     // nothing found, recursively search the application tree for VERSION.txt
        $versionFiles = $this->findVersionFilesRecursive($appdir);
        if (!$versionFiles) {
            return false;
        }

        foreach ($versionFiles as $versionFile) {
            if ($this->isCakeVersionFile($versionFile)) {
                $lines = file($versionFile);
                return trim($lines[count($lines) - 1]);
            }
        }
        return false;
    }

    /**
     * Recursively search an application directory for all VERSION.txt files.
     *
     * @param string $appdir Full path to the application's root directory.
     * @return string|bool Strubg containing full path to VERSION.txt if found
     */
    public function findVersionFilesRecursive($appdir)
    {
        $folder = new Folder($appdir);
        $files = $folder->findRecursive('VERSION.txt');
        if (count($files) != 0) {
            return $files;
        }
        return false;
    }

    /**
     * Checks if the given VERSION.txt file is a valid CakePHP version file.
     *
     * @param string $file Full path to the VERSION.txt file.
     * @return bool True if file is found
     */
    public function isCakeVersionFile($file)
    {
        $fh = new File($file);
        $content = $fh->read();
        if (strpos($content, 'CakePHP') == true) {
            return true;
        }
        $fh->close();
        return false;
    }

    /**
     * Get the common framework name used by an application by concatenating it's
     * framework name and major version (e.g. cakephp2, cakephp3, laravel4)
     *
     * @param string $appdir Full path to the application's root directory.
     * @return array|bool Single dimensional array with key/value pair collections, false on fails
     */
    public function getFrameworkCommonName($appdir)
    {
        $framework = $this->getFrameworkName($appdir);
        $frameworkVersion = $this->getFrameworkVersion($appdir);
        $majorVersion = CakeboxUtility::getMajorVersion($this->getFrameworkVersion($appdir));
        return $framework . $majorVersion;
    }

    /**
     * Fetch contributor statistics for a repository from the Github API.
     *
     * @param string $repository Github repository shortname (owner/repo).
     * @return array Array
     */
    public function getRepositoryContributors($repository)
    {
        $contributors = Cache::read('contributors', 'short');
        if ($contributors) {
            return $contributors;
        }

        $http = new Client();
        $response = $http->get("https://api.github.com/repos/$repository/stats/contributors");
        $result = json_decode($response->body(), true);
        Cache::write('contributors', $result, 'short');
        return $result;
    }

    /**
     * Fetch commits for a repository from the Github API.
     *
     * @param string $repository Github repository shortname (owner/repo).
     * @return array Array
     */
    public function getRepositoryCommits($repository)
    {
        $commits = Cache::read('commits', 'short');
        if ($commits) {
            return $commits;
        }

        $http = new Client();
        $response = $http->get("https://api.github.com/repos/$repository/commits");
        $result = json_decode($response->body(), true);
        Cache::write('commits', $result, 'short');
        return $result;
    }

    /**
     * Get latest commit header from Github api.
     *
     * @return string String containing git sha
     */
    public function getLatestCommitRemote()
    {
        $commits = $this->getRepositoryCommits('alt3/cakebox-console');
        return $commits[0]['sha'];
    }

    /**
     * Get local commit header.
     *
     * @return string String containing git sha
     */
    public function getLatestCommitLocal()
    {
        return file_get_contents('/cakebox/console/.git/refs/heads/master');
    }
}
