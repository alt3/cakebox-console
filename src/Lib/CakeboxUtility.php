<?php
namespace App\Lib;

use Cake\Utility\Hash;

/**
 * Class library for box agnostic helper functions
 */
class CakeboxUtility {

/**
 * Retrieve a specific setting from an Ngninx site configuration file.
 *
 * @todo make webserver agnostic and (now hardcoded path to prevent CakeboxInfo coupling)
 *
 * @param string Name of the Nginx site-configuration file
 * @param string Keyname to extract the value for
 * @return mixed String containing value or false when key lookup fails
 */
	public static function getNginxFileSetting($file, $key) {
		$content = file_get_contents("/etc/nginx/sites-available/" . $file);
		preg_match_all("/^\\s++$key\\s++(.*);/m", $content, $matches);
		$lastKey = end($matches);
		$value = end($lastKey);
		if (!empty($value)){
			return $value;
		}
		return false;
	}

/**
 * Retrieve the installed version of a Composer package by parsing composer.lock.
 *
 * @param string Path to the composer.lock file
 * @param string Name of the package to get the version for
 * @return mixed String containing value or false when key lookup fails
 */
	public static function getComposerLockVersion($lockfile, $package) {
		if (!file_exists($lockfile)) {
			return false;
		}
		// escape / in package name to not break Xpath query
		$package = str_replace('/', '\/', $package);
		$json = json_decode(file_get_contents($lockfile), true);
		return implode(Hash::extract($json, "packages.{n}[name=/$package/].version"));
	}

/**
 * Get the major version by returning first digit of a given full-version string.
 *
 * @param string Full version (e.g. 3.0.1-beta, v4.0.1, 1.0)
 * @return int|bool Integer holding major version, false on fail
 */
	public static function getMajorVersion($version) {
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
 * @param string Full path to the directory
 * @return object SplIterator containing directories
 */
	public static function getDirectoriesRecursive($dir) {
		return new \RecursiveIteratorIterator(
			new \ParentIterator(new \RecursiveDirectoryIterator($dir)),
			\RecursiveIteratorIterator::SELF_FIRST);
	}

}
