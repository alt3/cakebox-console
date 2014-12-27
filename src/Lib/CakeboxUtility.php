<?php
namespace App\Lib;

use Cake\Filesystem\File;
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
         // Add an extra item to each column while the column number is less than the
         // remainder.
            $addRest = ($rest && ($i < $rest)) ? 1 : 0;
            $number = $perColumn + $addRest;
            $columns[] = array_slice($data, $index, $number);
            $index += $number;
        }
        return $columns;
    }
}
