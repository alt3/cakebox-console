<?php
namespace App\Lib;

use Cake\Core\Exception\Exception;
use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\File;
use Cake\Log\Log;
use Cake\Utility\Hash;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

/**
 * Class library for box agnostic helper functions
 */
class CakeboxUtility
{

    /**
     * Checks if a virtual host file exists in /etc/nginx/sites-available.
     *
     * @param string $url Virtuals host's FQDN
     * @return boolean True if a vhost is found for the given URL
     */
    public static function vhostAvailable($url)
    {
        if (file_exists('/etc/nginx/sites-available/' . $url)) {
            return true;
        }
        return false;
    }

    /**
     * Checks if a virtual host is enabled by checking for symbolic link in
     * /etc/nginx/sites-enabled.
     *
     * @param string $url Virtuals host's FQDN
     * @return boolean True if a symlink is found for the given URL
     */
    public static function vhostEnabled($url)
    {
        if (is_link('/etc/nginx/sites-enabled/' . $url)) {
            return true;
        }
        return false;
    }

    /**
     * Retrieve a specific setting from an Ngninx site configuration file.
     *
     * @param string $file Name of the Nginx site-configuration file.
     * @param string $key Keyname to extract the value for.
     * @return mixed String containing value or false when key lookup fails
     */
    public static function getNginxFileSetting($file, $key)
    {
        $content = file_get_contents("/etc/nginx/sites-available/" . $file);
        preg_match_all("/^\\s++$key\\s++(.*);/m", $content, $matches);
        $lastKey = end($matches);
        $value = end($lastKey);
        if (!empty($value)) {
            return $value;
        }
        return false;
    }

    /**
     * Retrieve the installed version of a Composer package by parsing composer.lock.
     *
     * @param string $path Full path to the directory holding the composer.lock file.
     * @param string $package Name of the package to get the version for.
     * @return mixed String containing value or false when key lookup fails
     */
    public static function getComposerLockVersion($path, $package)
    {
        if (!file_exists("$path/composer.lock")) {
            return false;
        }
        // escape / in package name to not break Xpath query
        $package = str_replace('/', '\/', $package);
        $json = json_decode(file_get_contents("$path/composer.lock"), true);
        return implode(Hash::extract($json, "packages.{n}[name=/$package/].version"));
    }

    /**
     * Get the major version by returning first digit of a given full-version string.
     *
     * @param string $version Full version (e.g. 3.0.1-beta, v4.0.1, 1.0).
     * @return int|bool Integer holding major version, false on fail
     */
    public static function getMajorVersion($version)
    {
        preg_match('/\d/m', $version, $matches);
        if (isset($matches[0])) {
            return $matches[0];
        }
        return false;
    }

    /**
     * Get a (recursive) list of all directories and subdirectories for a given
     * using SPL.
     *
     * @param string $dir Full path to the directory.
     * @return object SplIterator containing directories
     */
    public static function getDirectoriesRecursive($dir)
    {
        return new \RecursiveIteratorIterator(
            new \ParentIterator(new \RecursiveDirectoryIterator($dir)),
            \RecursiveIteratorIterator::SELF_FIRST
        );
    }

    /**
     * Returns the content of a(ny) file as html-escaped string.
     *
     * @param string $file Name of the site file.
     * @return string html-escaped file contents
     */
    public static function getFileContent($file)
    {
        if (!file_exists($file)) {
            return false;
        }
        $fh = new File($file);
        return htmlentities($fh->read());
    }

    /**
     * Divide an array of data equally over a given number of columns.
     *
     * @param array $data Array with data.
     * @param int $numColumns Number of parts to chop the data into.
     * @return array Array
     */
    public static function columnizeArray($data, $numColumns)
    {
        $n = count($data);
        $perColumn = floor($n / $numColumns);
        $rest = $n % $numColumns;

        $columns = array();
        $index = 0;
        for ($i = 0; $i < $numColumns; $i++) {
            // Add an extra item to each column while the column number is less
            // than the remainder.
            $addRest = ($rest && ($i < $rest)) ? 1 : 0;
            $number = $perColumn + $addRest;
            $columns[] = array_slice($data, $index, $number);
            $index += $number;
        }
        return $columns;
    }

    /**
     * Replace unsupported characters in databases name with underscores.
     *
     * @param string $name Dirty database name.
     * @return string $name Cleaned database name
     */
    public static function normalizeDatabaseName($name)
    {
        $name = str_replace('.', '_', $name); // replace dots
        $name = (str_replace('\\', '_', $name)); // replace backslashes
        $name = (str_replace('/', '_', $name)); // replace forward slashes
        return $name;
    }

    /**
     * Check if a database exists.
     *
     * @param string $database Database name.
     * @return boolean True if the database exists
     */
    public static function databaseExists($database)
    {
        $database = self::normalizeDatabaseName($database);
        try {
            $connection = ConnectionManager::get('default');
            if ($connection->execute("SHOW DATABASES LIKE '$database'")->count()) {
                return true;
            };
            return false;
        } catch (\Exception $e) {
            Log::error("Error showing databases: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete existing database and related test_ database.
     *
     * @param string $database Database name.
     * @return boolean True when dropped successfully.
     */
    public static function dropDatabase($database)
    {
        $database = self::normalizeDatabaseName($database);
        $databases = [ $database, 'test_' . $database ];

        foreach ($databases as $database) {
            if (self::databaseExists($database) == false) {
                Log::warning("* Skipping: database $database does not exist");
                continue;
            }
            try {
                $connection = ConnectionManager::get('default');
                $connection->execute("DROP DATABASE `$database`");
                Log::debug("* Database `$database` dropped successfully");
            } catch (\Exception $e) {
                Log::error("Error dropping database `$database`: " . $e->getMessage());
                return false;
            }
        }
        return true;
    }



    /**
     * Creates a MySQL database with specified database user GRANT.
     *
     * @param string $database Name used for the new database.
     * @param string $username User granted local access to (only) this database.
     * @param string $password Password for above user.
     * @return boolean True if created successfully
     * @throws Cake\Core\Exception\Exception
     */
    public static function createDatabase($database, $username, $password)
    {
        $database = self::normalizeDatabaseName($database);
        if (self::databaseExists($database)) {
            Log::warning("* Skipping: database $database already exists");
            return false;
        }

        try {
            $connection = ConnectionManager::get('default');
            $connection->execute("CREATE DATABASE `$database`");
            Log::debug("* Database `$database` created successfully");
            self::grantDatabaseRights($database, $username, $password);
        } catch (Exception $e) {
            Log::error("Error creating database: " . $e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Creates a MySQL database pair with a main database and and a test database.
     *
     * @param string $database Name used for the new databases.
     * @param string $username User granted local access to (only) this database.
     * @param string $password Password for above user.
     * @return boolean True if both databases are created successfully
     */
    public static function createDatabasePair($database, $username, $password)
    {
        $database = self::normalizeDatabaseName($database);
        $databases = [ $database, 'test_' . $database ];
        foreach ($databases as $database) {
            self::createDatabase($database, $username, $password);
        }
        return true;
    }

    /**
     * Grant user localhost access to databases (main and test).
     *
     * @param string $database Database name.
     * @param string $username Name of user to grant localhost access.
     * @param string $password Password for given user.
     * @return boolean True on success
     */
    public static function grantDatabaseRights($database, $username, $password)
    {
        $database = self::normalizeDatabaseName($database);
        try {
            $connection = ConnectionManager::get('default');
            $connection->execute("GRANT ALL ON `$database`.* to  '$username'@'localhost' identified by '$password'");
            Log::debug("* Granted user `$username` localhost access on database `$database`");
        } catch (\Exception $e) {
            Log::error("Error granting user `$username` localhost access on database `$database`");
            return false;
        }
        return true;
    }

    /**
     * Replace one or more value pairs in a configuration file.
     *
     * @param string $file Path to the file containing the string to replace.
     * @param array $valuePairs Containing 'old' => 'new' values.
     * @param boolean $root True to write new file as root
     * @return boolean True if the file was updated successfully
     */
    public static function updateConfigFile($file, $valuePairs, $root = false)
    {
        Log::debug("* Updating config file $file");
        if (!file_exists($file)) {
            Log::error("* Cannot replace values in non-existent file $file");
            return false;
        }
        $content = file_get_contents($file);

        # replace and count so we can log meaningfully
        foreach ($valuePairs as $old => $new) {
            $content = str_replace($old, $new, $content, $count);
            if ($count == 0) {
                Log::warning("* Nothing to replace, `$old` could not be found");
            } else {
                Log::debug("* Replaced $count occurences of `$old`");
            }
        }

        # Update file
        if (!$root) {
            $result = file_put_contents($file, $content);
            if (!$result) {
                Log::error("* Error writing to config file: " . error_get_last());
                return false;
            }
        } else {
            $Execute = new CakeboxExecute();
            if (!$Execute->shell("echo '$content' | sudo tee $file", 'root')) {
                return false;
            }
        }
        Log::info("* Successfully updated config file");
        return true;
    }

    /**
     * Set globally writable permissions on the "tmp" and "logs" directory.
     *
     * This is not the most secure default, but it gets people up and running quickly.
     *
     * @param string $dir The application's root directory.
     * @return boolean True if permissions are updated successfully
     */
    public static function setFolderPermissions($dir)
    {
        Log::info("Setting permissions on $dir to world writable");

        // Change the permissions on a path and output the results.
        $changePerms = function ($path, $perms) {
            // Get current permissions in decimal format so we can bitmask it.
            $currentPerms = octdec(substr(sprintf('%o', fileperms($path)), -4));
            if (($currentPerms & $perms) == $perms) {
                Log::debug('* Skipping: desired permissions already set for ' . $path);
                return true;
            }

            $res = chmod($path, $currentPerms | $perms);
            if (!$res) {
                Log::error('Failed to set permissions on ' . $path);
                return false;
            }
            Log::debug('* Successfully updated permissions for ' . $path);
        };

        $walker = function ($dir, $perms) use (&$walker, $changePerms) {
            $files = array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                $path = $dir . '/' . $file;

                if (!is_dir($path)) {
                    continue;
                }

                $changePerms($path, $perms);
                $walker($path, $perms);
            }
        };
        $worldWritable = bindec('0000000111');
        $changePerms($dir, $worldWritable);
        $walker($dir, $worldWritable);
        return true;
    }

    /**
     * Check if an Ubuntu software package is already installed. We do not use
     * ExecTask to shell `dpkg -` or `dpkg-query -l` since those generate
     * exit-codes/errors for both non-installed and non-existing packages.
     *
     * @param string $package Name of Ubuntu package to check.
     * @return bool
     */
    public static function packageInstalled($package)
    {
        $file = "/var/lib/dpkg/info/$package.md5sums";
        if (file_exists($file)) {
            return true;
        }
        return false;
    }

    /**
     * Run readiness test to see if a directory can be used for composer/git
     * installations.
     *
     * @param string $directory Full path to directory to check.
     * @return bool
     */
    public function dirAvailable($directory)
    {
        Log::debug("Checking installation directory readiness");

        # Directory does not exist
        Log::debug("* Checking if installation directory exists");
        if (!file_exists($directory)) {
            Log::debug("* Pass: directory does not exist");
            return true;
        }

        # Directory exists but is not empty
        Log::debug("* Checking if existing directory is empty");
        $files = scandir($directory);
        if (count($files) > 2) {
            Log::warning("* Fail: directory exists but is NOT empty");
            return false;
        }

        # Check if the directory is writable by vagrant user
        $Execute = new CakeboxExecute();
        if (!$Execute->isVagrantWritable($directory)) {
            return false;
        }
        Log::debug("* Pass: directory is writable");
        return true;
    }

    /**
     * Generates a random to be used for replacing default Salt/Cipher in Cake2.
     *
     * @param string $randomText Text used generating the hash.
     * @return string Containg sha256 salt/cipher
     */
    public function getSaltCipher($randomText)
    {
        return hash('sha256', $randomText . php_uname() . microtime(true));
    }

    /**
     * Returns the content of a yaml file as an array.
     *
     * @param string $yaml Full path to the yaml file
     * @return string Hash
     * @throws Symfony\Component\Yaml\Exception\ParseException
     */
    public static function yamlToArray($yaml)
    {
        try {
            return (new Parser)->parse(file_get_contents($yaml));
        } catch (ParseException $e) {
            printf("Unable to parse YAML file $yaml: %s", $e->getMessage());
        }
    }
}
