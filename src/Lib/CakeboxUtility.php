<?php
namespace App\Lib;

use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\File;
use Cake\Log\Log;
use Cake\Utility\Hash;

/**
 * Class library for box agnostic helper functions
 */
class CakeboxUtility
{

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
    public function getFileContent($file)
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
    public function columnizeArray($data, $numColumns)
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
            log::error("Error showing databases: " . $e->getMessage());
            return false;
            //$this->out("Error: " . $e->getMessage());
            //$this->Exec->exitBashError();
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
                log::warning("* Skipping: database $database does not exist");
                continue;
            }
            try {
                $connection = ConnectionManager::get('default');
                $connection->execute("DROP DATABASE `$database`");
                log::debug("* Database `$database` dropped successfully");
            } catch (\Exception $e) {
                log::error("Error dropping database `$database`: " . $e->getMessage());
                return false;
            }
        }
        return true;
    }

    /**
     * Create a main database and accompanying 'test_' prefixed test database.
     *
     * @param string $database Name used for the new databases.
     * @param string $username User granted local access to (only) this database.
     * @param string $password Password for above user.
     * @return boolean True if created successfully
     */
    public static function createDatabase($database, $username, $password)
    {
        $database = self::normalizeDatabaseName($database);
        $databases = [ $database, 'test_' . $database ];

        foreach ($databases as $database) {
            if (self::databaseExists($database)) {
                log::warn("* Skipping: database $database already exists");
                continue;
            }
            try {
                $connection = ConnectionManager::get('default');
                $connection->execute("CREATE DATABASE `$database`");
                log::debug("* Database `$database` created successfully");
                self::grantDatabaseRights($database, $username, $password);
            } catch (\Exception $e) {
                log::error("Error creating database: " . $e->getMessage());
                return false;
            }
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
    protected static function grantDatabaseRights($database, $username, $password)
    {
        $database = self::normalizeDatabaseName($database);
        try {
            $connection = ConnectionManager::get('default');
            $connection->execute("GRANT ALL ON `$database`.* to  '$username'@'localhost' identified by '$password'");
            log::debug("* Granted user `$username` localhost access on database `$database`");
        } catch (\Exception $e) {
            log::error("Error granting user `$username` localhost access on database `$database`");
            return false;
        }
        return true;
    }

    /**
     * Replace one or more value pairs in a configuration file.
     *
     * @param string $file Path to the file containing the string to replace.
     * @param array $valuePairs Containing 'old' => 'new' values.
     * @return boolean True if the file was updated successfully
     */
    public static function updateConfigFile($file, $valuePairs)
    {
        log::debug("* Updating config file $file");
        if (!file_exists($file)) {
            log::error("* Cannot replace values in non-existent file $file");
            return false;
        }
        $content = file_get_contents($file);

        # replace and count so we can log meaningfully
        foreach ($valuePairs as $old => $new) {
            $content = str_replace($old, $new, $content, $count);
            if ($count == 0) {
                log::warning("* Nothing to replace, `$old` could not be found");
            } else {
                Log::debug("* Replaced $count occurences of `$old`");
            }
        }

        # Update file
        $result = file_put_contents($file, $content);
        if (!$result) {
            log::error("* Error writing to config file: " . error_get_last());
            return false;
        }
        log::info("* Successfully updated config file");
        return true;
    }

    /**
     * Convenience function to update CakePHP3 app.php configuration file.
     *
     * @param string $file Full path to the app.php file.
     * @param string $url FQDN used to expose the application.
     * @return boolean True if the file was updated successfully
     */
    public static function updateCake3Configuration($file, $url)
    {
        $database = CakeboxUtility::normalizeDatabaseName($url);
        $result = CakeboxUtility::updateConfigFile($file, [
            "'username' => 'my_app'" => "'username' => 'cakebox'",
            "'database' => 'my_app'" => "'database' => '$database'",
            "'database' => 'test_myapp'" => "'database' => 'test_$database'"
            ]);
        if ($result == false) {
            log::error("Error updating CakePHP3 config file");
            return false;
        }
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
        log::debug("Setting permissions on $dir");

        // Change the permissions on a path and output the results.
        $changePerms = function ($path, $perms) {
            // Get current permissions in decimal format so we can bitmask it.
            $currentPerms = octdec(substr(sprintf('%o', fileperms($path)), -4));
            if (($currentPerms & $perms) == $perms) {
                return;
            }

            $res = chmod($path, $currentPerms | $perms);
            if (!$res) {
                log::error('Failed to set permissions on ' . $path);
                return false;
            }
            log::debug('Permissions set on ' . $path);
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
     * Check if a directory is either non-existent or empty. Useful before running
     * commands which require empty directories (e.g. git clone).
     *
     * @param string $directory Full path to directory to check.
     * @return bool
     */
    public function dirAvailable($directory)
    {
        log::debug("Checking if directory is available for framework installation");
        if (!file_exists($directory)) {
            log::debug("* Available: directory does not exist");
            return true;
        }
        if (($files = scandir($directory)) && count($files) <= 2) {
            log::debug("* Available: directory exists but is empty");
            return true;
        }
        log::error("* Directory exists and is NOT empty");
        return false;
    }

    /**
     * Generates a random to be used for replacing default Salt/Cipher in Cake2.
     *
     * @param string $randomText Text used generating the hash.
     * @return string Containg sha256 salt/cipher
     */
    public function genSaltCipher($randomText)
    {
        return hash('sha256', $randomText . php_uname() . microtime(true));
    }
}
