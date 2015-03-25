<?php
namespace App\Lib;

use Cake\Cache\Cache;
use Cake\Collection\Collection;
use Cake\Core\App;
use Cake\Core\Exception\Exception;
use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\I18n\Time;
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
     * Most recently provisioned Cakebox.yaml converted to an array.
     *
     * @var Array Hash
     */
    protected $_yaml;

    /**
     * @var array Hash with webserver specific information.
     */
    public $cakeboxMeta = [
        'host' => [
            'yaml' => '/home/vagrant/.cakebox/last-known-cakebox-yaml',
            'commit' => '/home/vagrant/.cakebox/last-known-cakebox-commit',
            'box_version' => '/home/vagrant/.cakebox/last-known-box-version',
        ],
        'cli_log' => '/var/log/cakephp/cakebox.cli.log'
    ];

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
        ],
        'test_prefix' => 'test_',
        'default_local_user' => 'cakebox',
        'default_local_password' => 'secret'
    ];

    /**
     * @var array Hash with framework specific information.
     */
    public $frameworkMeta = [
        'cakephp2' => [
            'installation_method' => 'git',
            'source' => 'https://github.com/cakephp/cakephp.git -b 2.7',
            'source_ssh' => 'git@github.com:cakephp/cakephp.git -b 2.7',
            'webroot' => 'app/webroot',
            'writable_dirs' => ['app/tmp'],
            'salt' => 'DYhG93b0qyJfIxfs2guVoUubWwvniR2G0FgaC9mi',
            'cipher' => '76859309657453542496749683645'
        ],
        'cakephp3' => [
            'installation_method' => 'composer',
            'source' => 'cakephp/app',
            'webroot' => 'webroot'
        ],
        'laravel' => [
            'installation_method' => 'composer',
            'source' => 'laravel/laravel',
            'webroot' => 'public',
            'writable_dirs' => ['storage'] // app/storage for Laravel 4
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
    public $packages = [
        'composer' => ['link' => 'https://getcomposer.org'],
        'curl' => ['link' => 'http://curl.haxx.se'],
        'elasticsearch' => ['link' => 'https://www.elasticsearch.org'],
        'git' => ['link' => 'https://launchpad.net/~git-core'],
        'java' => ['link' => 'http://openjdk.java.net'],
        'heroku' => ['link' => 'https://toolbelt.heroku.com'],
        'hhvm' => ['link' => 'http://hhvm.com'],
        'kibana' => ['link' => 'https://www.elasticsearch.org/overview/kibana'],
        'logstash' => ['link' => 'http://logstash.net'],
        'mysql' => ['link' => 'http://www.percona.com/software/percona-server'],
        'memcached' => ['link' => 'http://memcached.org'],
        'mongodb' => [
            'link' => 'https://www.mongodb.org/',
            'alias' => 'mongod'
        ],
        'nginx' => ['link' => 'https://launchpad.net/nginx'],
        'openssl' => ['link' => 'https://www.openssl.org'],
        'php' => ['link' => 'https://launchpad.net/~ondrej/+archive/ubuntu/php5-5.6'],
        'phpunit' => ['link' => 'https://phpunit.de'],
        'phpcs' => ['link' => 'https://github.com/squizlabs/PHP_CodeSniffer'],
        'cakephp-codesniffer' => ['link' => 'https://github.com/cakephp/cakephp-codesniffer'],
        'postgresql' => [
            'link' => 'http://www.postgresql.org',
            'alias' => 'psql'
        ],
        'python' => ['link' => 'https://www.python.org'],
        'redis' => [
            'link' => 'https://launchpad.net/~chris-lea/+archive/ubuntu/redis-server',
            'alias' => 'redis-server'
        ],
        'ruby' => ['link' => 'https://www.ruby-lang.org/en'],
        'xtrabackup' => ['link' => 'http://www.percona.com/doc/percona-xtrabackup']
    ];

    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->_conn = ConnectionManager::get('default');
        $this->_yaml = CakeboxUtility::yamlToArray($this->cakeboxMeta['host']['yaml']);
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
     * Return version of cakebox-console as found in VERSION.txt
     *
     * @return \Cake\Database\Connection
     */
    public function cakeboxVersion()
    {
        $cached = Cache::read('version');
        if ($cached) {
            return $cached;
        }

        $file = ROOT . DS . 'VERSION.txt';
        if (!file_exists($file)) {
            return false;
        }
        $lines = file_get_contents($file);
        preg_match('/(\d*\.\d*\.\d*-\d*\.\d*|\d*\.\d*\.\d*-\d*|\d*\.\d*\.\d*|\d*\.\d*-\w+)/m', $lines, $matches);
        if (empty($matches[1])) {
            return false;
        }
        Cache::write('version', $matches[1]);
        return $matches[1];
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
            'ip_address' => $this->getVmIpAddress(),
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
     * Returns the IP address assigned by Vagrant for external communication
     * by parsing the Vagrant added section in /etc/network/interfaces.
     *
     * @return string IP-address of the vm
     * @throws Cake\Core\Exception\Exception
     */
    public function getVmIpAddress()
    {
        $file = file_get_contents('/etc/network/interfaces');
        preg_match('/address ([0-9]{2,3}.[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3})/', $file, $matches);
        if (empty($matches[1])) {
            throw new Exception('Error determining vm IP address');
        }
        return $matches[1];
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
    public function getRichVhosts()
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
        return count($this->getRichVhosts());
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
        $cached = Cache::read('packages', 'short');
        if ($cached) {
            return $cached;
        }

        $result = [];
        foreach ($this->packages as $package => $details) {
             // fetch version
            switch($package) {
                case 'memcached':
                    $version = $this->_getPackageVersionMemcached();
                    break;
                case 'cakephp-codesniffer':
                    $version = CakeboxUtility::getComposerLockVersion('/opt/composer-libraries/cakephp_codesniffer', $package);
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
        Cache::write('packages', $result, 'short');
        return $result;
    }

    /**
     * Returns the version of the selected pacakge by shelling into bash.
     *
     * @param string $package Name of the package.
     * @return mixed Version of package or false if no version could be determined
     */
    protected function _getPackageVersionGeneric($package)
    {
        $stdout = `2>&1 $package --version`;
        preg_match('/(\d*\.\d*\.\d*-\d*\.\d*|\d*\.\d*\.\d*-\d*|\d*\.\d*\.\d*[a-z]|\d*\.\d*\.\d*|\d*\.\d*-\w+)/m', $stdout, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }

        // No match on --version so let's try --v
        $stdout = `2>&1 $package -v`;
        preg_match('/(\d*\.\d*\.\d*-\d*\.\d*|\d*\.\d*\.\d*-\d*|\d*\.\d*\.\d*[a-z]|\d*\.\d*\.\d*|\d*\.\d*-\w+)/m', $stdout, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }

        // Edge case for e.g. java using -version
        $stdout = `2>&1 $package -version`;
        preg_match('/(\d*\.\d*\.\d*-\d*\.\d*|\d*\.\d*\.\d*-\d*|\d*\.\d*\.\d*[a-z]|\d*\.\d*\.\d*|\d*\.\d*-\w+)/m', $stdout, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }

        // Edge case for e.g. openssl using just "version"
        $stdout = `2>&1 $package version`;
        preg_match('/(\d*\.\d*\.\d*-\d*\.\d*|\d*\.\d*\.\d*-\d*|\d*\.\d*\.\d*[a-z]|\d*\.\d*\.\d*|\d*\.\d*-\w+)/m', $stdout, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }

        // no generic match found
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
    protected function _getPackageVersionElasticsearch()
    {
        try {
            $http = new Client();
            $response = $http->get('http://' . $this->getVmIpAddress() . ':9200');
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
        $cached = Cache::read('php_modules', 'short');
        if ($cached) {
            return $cached;
        }

        $modules = get_loaded_extensions();
        sort($modules, SORT_NATURAL | SORT_FLAG_CASE);

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
        Cache::write('php_modules', $result, 'short');
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
        $cached = Cache::read('nginx_modules', 'short');
        if ($cached) {
            return $cached;
        }

        // shell command since no other option seems available
        $stdout = `2>&1 nginx -V | xargs -n1`;
        $lines = explode("\n", $stdout);
        sort($lines, SORT_NATURAL | SORT_FLAG_CASE);

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
        Cache::write('nginx_modules', $result, 'short');
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
        foreach ($this->getNginxFiles() as $vhostFile) {
            $appname = $this->getAppName($vhostFile);
            if (!$appname) {
                continue;
            }

            $appdir = $this->getAppBase($vhostFile);
            if (!$appdir) {
                continue;
            }

            $framework = $this->getFrameworkName($appdir);
            $frameworkVersion = $this->getFrameworkVersion($appdir);
            $frameworkHuman = Inflector::humanize($framework);
            if ($frameworkHuman == 'Cakephp') {
                $frameworkHuman = 'CakePHP';
            }

            $result[] = [
                'name' => $appname,
                'framework' => $framework,
                'framework_human' => $frameworkHuman,
                'framework_major_version' => CakeboxUtility::getMajorVersion($frameworkVersion),
                'framework_version' => $frameworkVersion,
                'appdir' => $appdir,
                'webroot' => $this->getWebrootFromSite($vhostFile)
            ];
        }

        return $result;
    }

    /**
     * Get the application name by retrieving it's Nginx "server_name" directive.
     *
     * @param string $vhostFile Full path to application's Nginx site configuration file.
     * @return string|bool Containing application name, or false
     */
    public function getAppName($vhostFile)
    {
        $name = CakeboxUtility::getNginxFileSetting($vhostFile, 'server_name');
        if ($name === '_' || $name === false) {
            return false;
        }
        return $name;
    }

    /**
     * Get the application's webroot directory by retrieving it's Nginx "root" directive.
     *
     * @param string $vhostFile Full path to application's Nginx site configuration file.
     * @return mixed Containing application name
     */
    public function getWebrootFromSite($vhostFile)
    {
        $webroot = CakeboxUtility::getNginxFileSetting($vhostFile, 'root');
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
     * @param string $vhostFile Full path to application's Nginx site configuration file.
     * @return mixed Containing application name
     */
    public function getAppBase($vhostFile)
    {
        $webroot = $this->getWebrootFromSite($vhostFile);

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
        if ($this->getCakeVersionFromFile($appdir)) {
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
            $version = CakeboxUtility::getComposerLockVersion($appdir, $package);
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
        $majorVersion = CakeboxUtility::getMajorVersion($this->getFrameworkVersion($appdir));
        return $framework . $majorVersion;
    }

    /**
     * Return Github API statistics the 5 most recent contributions by extracting
     * merged Pull Requests in the provided branch of given repository.
     *
     * - Limit fetch query 10 results assuming no more than 50% is rejected
     * - Merged PRs found by extracting elements with non-empty "merged_at" subkey
     *
     * @param string $repository Github repository shortname (owner/repo).
     * @param string $branch Github branch
     * @return array Array
     */
    public function getRepositoryContributions($repository, $branch)
    {
        $cached = Cache::read('contributions', 'short');
        if ($cached) {
            return $cached;
        }

        $http = new Client();
        try {
            $response = $http->get("https://api.github.com/repos/$repository/pulls?base=$branch&state=closed&page=1&per_page=10");
            if (!$response->isOk()) {
                return [];
            }
        } catch (\Exception $e) {
            return [];
        }

        $avatars = (array)Cache::read('avatars', 'medium');

        $result = collection(json_decode($response->body(), true))
            ->reject(function ($record) {
                return $record['merged_at'] === null;
            })
            ->sortBy('merged_at', SORT_DESC, SORT_STRING)
            ->take(5)
            ->map(function ($record) use ($avatars, $http) {
                if (!empty($record['user']['avatar_data'])) {
                    return $record;
                }

                $url = $record['user']['avatar_url'];
                if (array_key_exists($url, $avatars)) {
                    $record['user']['avatar_data'] = $avatars[$url];
                    return $record;
                }

                try {
                    $response = $http->get($record['user']['avatar_url'] . "&amp;size=40");
                    if (!$response->isOk()) {
                        $record['user']['avatar_data'] = false;
                        return $record;
                    }
                } catch (\Exception $e) {
                    $record['user']['avatar_data'] = null;
                    return $record;
                }

                $avatar = $response->body();
                $avatars[$url] = base64_encode($avatar);
                Cache::write('avatars', $avatars, 'medium');

                $record['user']['avatar_data'] = $avatars[$url];
                return $record;
            })
            ->toArray();

        Cache::write('contributions', $result, 'short');
        return $result;
    }

    /**
     * Gets the branch name of the cakebox Git repository on the local machine
     * by xxxxx.
     *
     * @return string Name of the local cakebox Git branch.
     */
    public function getCakeboxBranch()
    {
         $composerVersion = $this->_yaml['cakebox']['version'];
         $parts = explode('-', $composerVersion);
         return $parts[1];
    }

    /**
     * Gets the branch name of the provisioned cakebox-console Git repository by
     * parsing the Composer packagist version in the most recently provisioned
     * Cakebox.yaml.
     *
     * @return string Name of the provisioned Git branch.
     */
    public function getCakeboxConsoleBranch()
    {
         $composerVersion = $this->_yaml['cakebox']['version'];
         $parts = explode('-', $composerVersion);
         return $parts[1];
    }

    /**
     * Returns hash with lines found in /var/log/cakephp/cakebox.cli.log
     *
     * @return string Array Containing all log entries
     */
    public function getCakeboxCliLog()
    {
        $lines = file($this->cakeboxMeta['cli_log']);
        $result = [];

        // extract timestamp, level and message from Monolog logstash format
        foreach ($lines as $line) {
            preg_match('/\"@timestamp\":\"(.+)\",\"@source.+\"level\":(\d{3}).+\"@message\":\"(.+)\",\"@tags".+/', $line, $matches);

            // parse timestamp so we can split into human readable date and time
            $time = Time::parse($matches[1]);

            // add Monolog/RFC 5424 level names
            //
            // Should ideally be moved into testable logic or... ask Monolog lib.
            $level = $matches[2];
            switch ($level){
                case 100:
                    $levelName = 'debug';
                    break;
                case 200:
                    $levelName = 'info';
                    break;
                case 250:
                    $levelName = 'notice';
                    break;
                case 300:
                    $levelName = 'warning';
                    break;
                case 400:
                    $levelName = 'error';
                    break;
                case 500:
                    $levelName = 'critical';
                    break;
                case 550:
                    $levelName = 'alert';
                    break;
                case 600:
                    $levelName = 'emergency';
                    break;
                default:
                    $levelName = $level;
            }

            // store as rich formatted hash entry
            $result[] = [
              'date' => $time->i18nFormat('YYYY-MM-dd'),
              'time' => $time->i18nFormat('HH:mm:ss'),
              'level' => $level,
              'level_name' => $levelName,
              'message' => $matches[3]
            ];
        }
        return $result;
    }

    /**
     * Returns an array with update notifications but could hold any message.
     *
     * @return array Rich hash with notifications or empty array.
     */
    public function getNotifications()
    {
        $cakeboxConsoleUpdate = $this->_getCakeboxConsoleUpdateNotification();
        $cakeboxUpdate = $this->_getCakeboxUpdateNotification();
        if (!$cakeboxUpdate && !$cakeboxConsoleUpdate) {
            return false;
        }

        $result = [];
        if ($cakeboxConsoleUpdate) {
            $result[] = $cakeboxConsoleUpdate;
        }
        if ($cakeboxUpdate) {
            $result[] = $cakeboxUpdate;
        }
        return $result;
    }

    /**
     * Checks if an update is available for the cakebox project on user's local
     * machine.
     *
     * @return mixed Rich hash if update is available, false if up-to-date
     */
    protected function _getCakeboxUpdateNotification()
    {
        pr($this->_getLatestCakeboxCommitLocal());
        pr($this->_getLatestRemoteCommit('alt3/cakebox', $this->getCakeboxBranch()));

        if ($this->_getLatestCakeboxCommitLocal() === $this->_getLatestRemoteCommit('alt3/cakebox', $this->getCakeboxBranch())) {
            return false;
        }
        return [
            'message' => __("An update is available for the cakebox project on your local machine. Instructions available %s."),
            'link' => [
                'text' => 'here',
                'url' => 'http://cakebox.readthedocs.org/en/latest/tutorials/updating-your-box/#local-update'
            ]
        ];
    }

    /**
     * Checks if an update is available for the cakebox-console project.
     *
     * @return mixed Rich hash if update is available, false if up-to-date
     */
    protected function _getCakeboxConsoleUpdateNotification()
    {
        if ($this->_getLatestCakeboxConsoleCommitLocal() === $this->_getLatestRemoteCommit('alt3/cakebox-console', $this->getCakeboxConsoleBranch())) {
            return false;
        }
        return [
            'message' => __("An update is available for your Cakebox Commands and Dashboard. Instructions available %s."),
            'link' => [
                'text' => 'here',
                'url' => 'http://cakebox.readthedocs.org/en/latest/tutorials/updating-your-box/#self-update'
            ]
        ];
    }

    /**
     * Retrieve most recent cakebox commit by parsing uploaded
     * last-know-cakebox-commit in /home/vagrant/.cakebox
     *
     * @return string String containing git sha
     */
    protected function _getLatestCakeboxCommitLocal()
    {
        $commit = trim(file_get_contents('/home/vagrant/.cakebox/last-known-cakebox-commit'));
        return $commit;
    }

    /**
     * Retrieve most recent local cakebox-console commit by parsing local
     * header file.
     *
     * @return string String containing git sha
     */
    protected function _getLatestCakeboxConsoleCommitLocal()
    {
        $commit = trim(file_get_contents('/cakebox/console/.git/refs/heads/' . $this->getCakeboxConsoleBranch()));
        return $commit;
    }

    /**
     * Fetch most recent remote cakebox-console commit from Github api.
     *
     * @param string $repository Github repository shortname (owner/repo).
     * @param string $branch Defaults to master
     * @return string String containing git sha
     */
    protected function _getLatestRemoteCommit($repository, $branch = 'master')
    {
        $commits = $this->getRepositoryCommits($repository, $branch, 1);
        $commit = $commits[0]['sha'];
        return $commit;
    }

    /**
     * Fetch commits for any given git repository from the Github API.
     *
     * @param string $repository Github repository shortname (owner/repo).
     * @param string $branch Branch to get commits for
     * @param int $limit Number of results to return.
     * @return array Array
     * @throws Cake\Core\Exception\Exception
     */
    public function getRepositoryCommits($repository, $branch = 'master', $limit = null)
    {
        $cacheKey = 'commits_' . str_replace('/', '_', $repository);
        $commits = Cache::read($cacheKey, 'short');
        if ($commits) {
            return $commits;
        }

        if ($limit) {
            if (!is_int($limit)) {
                throw new Exception("Parameter limit must be an integer");
            }
            $limit = "page=1&per_page=$limit";
        }
        $params = "?sha=$branch&$limit";

        try {
            $http = new Client();
            $response = $http->get("https://api.github.com/repos/$repository/commits$params");
            if (!$response->isOk()) {
                return null;
            }
            $result = json_decode($response->body(), true);
            Cache::write($cacheKey, $result, 'short');
            return $result;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Returns rich information for the Cakebox.yaml file.
     *
     * @return array Hash with raw file data and timestamp.
     * @throws Exception
     */
    public function getRichCakeboxYaml()
    {
        try {
            $fileHandle = new File($this->cakeboxMeta['host']['yaml']);
            return [
                'timestamp' => $fileHandle->lastChange(),
                'raw' => $fileHandle->read()
            ];
        } catch (\Exception $e) {
            throw new \Exception("Error reading " . $this->cakeboxMeta['yamlFile'] . ": " . $e->getMessage());
        }
    }

    /**
     * Checks if the Cakebox Dashboard is using HTTPS by parsing the default
     * Nginx catch-all website.
     *
     * @return array boolean True when HTTPS is being used.
     */
    public function dashboardUsesHttps()
    {
        $vhost = file_get_contents($this->webserverMeta['nginx']['sites-available'] . DS . 'default');
        preg_match('/HTTPS/', $vhost, $matches);
        if (!empty($matches)) {
            return true;
        }
        return false;
    }
}
