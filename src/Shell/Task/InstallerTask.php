<?php
namespace App\Shell\Task;

use Cake\Console\Shell;

/**
 * Task class for managing application installations.
 */
class InstallerTask extends Shell {

/**
 * Get full path to the home directory of user sudoing the cakebox command.
 *
 * @return string $homepath Full path to the sudo user's home directory
 */
	public function getSudoerHomeDirectory() {
		return "/home/" . env("SUDO_USER");
	}

/**
 * Set globally writable permissions on the "tmp" and "logs" directory.
 *
 * This is not the most secure default, but it gets people up and running quickly.
 *
 * @param string $dir The application's root directory.
 * @return void
 */
	public function setFolderPermissions($dir) {
		// Change the permissions on a path and output the results.
		$changePerms = function ($path, $perms) {
			// Get current permissions in decimal format so we can bitmask it.
			$currentPerms = octdec(substr(sprintf('%o', fileperms($path)), -4));
			if (($currentPerms & $perms) == $perms) {
				return;
			}

			$res = chmod($path, $currentPerms | $perms);
			if ($res) {
				$this->out('Permissions set on ' . $path);
			} else {
				$this->out('Failed to set permissions on ' . $path);
			}
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
	}

/**
 * Update security.salt value in the application's config file.
 *
 * @param string $file Path to the file containing the Salt.
 * @param string $cakeVersion CakePHP major version (2|3)
 * @return void
 */
	public function setSecuritySalt($file, $cakeVersion = '3') {
		$config = $file;
		$content = file_get_contents($config);

		$newKey = hash('sha256', $file . php_uname() . microtime(true));
		if ($cakeVersion == '2') {
			$content = str_replace('DYhG93b0qyJfIxfs2guVoUubWwvniR2G0FgaC9mi', $newKey, $content, $count);
		} else {
			$content = str_replace('__SALT__', $newKey, $content, $count);
		}

		if ($count == 0) {
			$this->out('No Security.salt placeholder to replace.');
			return;
		}

		$result = file_put_contents($config, $content);
		if ($result) {
			$this->out("Updated Security.salt value in $file");
			return;
		}
		$this->out('Unable to update Security.salt value.');
	}

/**
 * Update security.cipher value in the application's config file.
 *
 * @param string $file Path to the file containing the Cipher.
 * @param string $cakeVersion CakePHP major version (2|3)
 * @return void
 */
		public function setSecurityCipher($file, $cakeVersion = '3') {
			$config = $file;
			$content = file_get_contents($config);

			$newKey = hash('sha256', $file . php_uname() . microtime(true));
			if ($cakeVersion == '2') {
				$content = str_replace('76859309657453542496749683645', $newKey, $content, $count);
			} else {
				$content = str_replace('__SALT__', $newKey, $content, $count);
			}

			if ($count == 0) {
				$this->out('No Security.cipher placeholder to replace.');
				return;
			}

			$result = file_put_contents($config, $content);
			if ($result) {
				$this->out("Updated Security.cipher value in $file");
				return;
			}
			$this->out('Unable to update Security.salt value.');
		}

/**
 * Create a config file based on a config.default
 *
 * @param string $default Full path to default configuration file (e.g. database.php.default)
 * @param string $config Full path to the configuration to create
 * @return void
 */
	public function createConfig($default, $config) {
		if (!file_exists($config)) {
			copy($default, $config);
			$this->out("Created config file `$config`");
		}
	}

/**
 * Replace a value in a configuration file.
 *
 * @param string $file Path to the file containing the string to replace.
 * @param string $oldValue String to be replaced
 * @param string $newValue String to use as replacement
 * @return void
 */
		public function replaceConfigValue($file, $oldValue, $newValue) {
			$config = $file;
			$content = file_get_contents($config);

			$content = str_replace($oldValue, $newValue, $content, $count);

			if ($count == 0) {
				$this->out("No `$oldValue` to replace.");
				return;
			}

			$result = file_put_contents($config, $content);
			if ($result) {
				$this->out("Updated `$oldValue` in $file");
				return;
			}
			$this->out("Unable to update `$oldValue` in $file");
		}

/**
 * Check if a directory is either non-existent or empty. Useful before running
 * commands which require empty directories (e.g. git clone).
 *
 * @param string $directory Full path to directory to check
 * @return bool
 */
	public function dirAvailable($directory) {
		if (!file_exists($directory)) {
			return true;
		}
		if (($files = scandir($directory)) && count($files) <= 2) {
			return true;
		}
		return false;
	}

}
