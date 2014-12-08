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
	public function getNginxFileSetting($file, $key) {
		$content = file_get_contents("/etc/nginx/sites-available/" . $file['name']);
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
	public function getComposerLockVersion($lockfile, $package) {
		if (!file_exists($lockfile)) {
			return false;
		}
		// escape / in package name to not break Xpath query
		$package = str_replace('/', '\/', $package);
		$json = json_decode(file_get_contents($lockfile), true);
		return (implode(Hash::extract($json, "packages.{n}[name=/$package/].version")));
	}

}
