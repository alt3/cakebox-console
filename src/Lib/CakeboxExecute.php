<?php
namespace App\Lib;

use Cake\Cache\Cache;
use Cake\Datasource\ConnectionManager;
use Cake\FileSystem\File;
use Cake\Log\Log;
use Cake\Utility\String;

/**
 * Class library for shelling commands
 */
class CakeboxExecute
{

    /**
     * Instance of \App\Lib\CakeboxInfo
     *
     * @var Array
     */
    protected $cbi = [];

    /**
     * List with debug information for the most recently executed command.
     *
     * @var Array
     */
    protected $_debug = [];

    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->cbi = new CakeboxInfo();
    }

    /**
     * Execute a system command as user vagrant or provided username.
     *
     * @param string $command Full path to the command with options and arguments.
     * @param string $username User used to execute the command (e.g. `vagrant`).
     * @return boolean True if the command completed successfully
     */
    protected function shell($command, $username)
    {
        // Generate different sudo command based on user
        if ($username == "root") {
            $command = "sudo $command 2>&1";
        } else {
            $command = "sudo su $username -c \"$command\" 2>&1";
        }
        $this->_log("Shelling command `$command` as user `$username`");

        // Execute the command, capture exit code, stdout and stderr
        $ret = exec($command, $stdout, $exitCode);

        // Log command output if any was produced
        if (!empty($stdout)) {
            $this->_log("* Shell output:");
            foreach ($stdout as $line) {
                if (!empty($line)) {
                    $this->_log("  => $line");
                }
            }
        }

        // Command failed if exit code <> 0
        if ($exitCode != 0) {
            $this->_error("* Shelled command exited with non-zero exit code `$exitCode`");
            return false;
        }

        // Still here, command succeeded
        $this->_log('* Shelled command completed successfully');
        return true;
    }

    /**
     * Create a directory as root and change ownership to vagrant user.
     *
     * @param string $path Full path of directory to be created.
     * @return boolean True on success
     */
    public function mkVagrantDir($path)
    {
        $this->flushLogs();
        if ($this->shell("mkdir $path", 'root') == false) {
            return false;
        }
        if ($this->shell("chown vagrant $path -R", 'root') == false) {
            return false;
        }
        return true;
    }

    /**
     * Run a git config command as vagrant user.
     *
     * @param string $path Full path of directory to be created.
     * @return boolean True on success
     */
    public function gitConfig($gitKey, $value)
    {
        log::debug("Updating global git configuration $gitKey to $value");
        if (!$this->shell("git config --global $gitKey $value", 'vagrant')) {
            return false;
        }
        return true;
    }

    /**
     * Self-update cakebox console and dashboard by updating git repository and
     * running composer update.
     *
     * @return boolean True on success
     */
    public function selfUpdate()
    {
        log::debug("Self-updating cakebox-console...");

        Log::debug("* Updating git repository");
        $command = 'cd /cakebox/console; git fetch; git reset --hard origin/master';
        if (!$this->shell($command, 'vagrant')) {
            return false;
        }

        Log::debug("* Updating composer packages");
        $command = 'cd /cakebox/console; composer update --prefer-dist --no-dev';
        if (!$this->shell($command, 'vagrant')) {
            return false;
        }

        Log::debug("* Self-update completed successfully");
        return true;
    }

    /**
     * Run `composer create-project` for a given package as user vagrant.
     *
     * @param string $package Name of the composer package (e.g. `cakephp/app`).
     * @param string $path Full path to the directory to create the project in.
     * @return boolean True on success
     */
    public function composerCreateProject($package, $path)
    {
        $this->flushLogs();
        $command = "composer create-project --prefer-dist --no-interaction -s dev $package $path";
        if ($this->shell($command, 'vagrant') == false) {
            return false;
        }
        return true;
    }

    /**
    * Run `composer install` for a given package as user vagrant.
    *
    * @param string $directory Full path to the directory holding composer.json.
    * @return boolean True on success
    */
    public function composerInstall($directory)
    {
        $this->flushLogs();
        $command = "cd $directory; composer install --prefer-dist --no-interaction";
        if ($this->shell($command, 'vagrant') == false) {
            return false;
        }
        return true;
    }

    /**
     * Git clone a repository.
     *
     * @param string $repository Github shortname (owner/repository).
     * @param string $path Full path to the directory to clone the repo in.
     * @return boolean True on success
     */
    public function gitClone($repository, $path)
    {
        $this->flushLogs();
        $this->_log("Cloning repository");
        $this->_log("* Repository: $repository");
        $this->_log("* Targetdir : $path");

        # Check SSH preconditions before attempting a git clone
        if (substr($repository, 0, 4) == 'git@') {
            if (!$this->sanityCheckSSH()) {
                return false;
            }
        }

        # Execute git clone
        if ($this->shell("git clone $repository $path", 'vagrant') == false) {
            return false;
        }
        return true;
    }



    /**
     * Perform sanity checks against SSH preconditions before git cloning using SSH
     *
     * @return boolean True on success
     */
    protected function sanityCheckSSH() {
        $this->_log("Sanity checking SSH before attempting git clone");

        $this->_log("* Sanity checking SSH Agent forwarded SSH key");
        if ($this->shell("ssh-add -l", 'vagrant') == false) {
            $this->_error("Error: SSH git clone requires a SSH key, none found");
            $this->_log(" => Note: make sure your SSH agent is forwarding the required identity key if this is a private repository");
            $this->_log(" => Note: Windows users MUST use Pageant or SSH Agent Forwarding will simply not work");
            return false;
        }

        $this->_log("* Sanity checking Github user.name");
        if ($this->shell("git config user.name", 'vagrant') == false) {
            return false;
        }
        return true;
    }

    /**
     * Reload nginx webservice (not a restart!)
     *
     * @return boolean True on success
     */
    public function reloadNginx()
    {
        // check config before reload attempt to not break running server
        if ($this->shell("nginx -t", 'root') == false) {
            return false;
        }
        if ($this->shell("service nginx reload", 'root') == false) {
            return false;
        }
        return true;
    }

    /**
     * Create a new Nginx website by generating a virtual host file, creating a
     * symoblic link and reloading the webserver.
     *
     * @param string $url Fully Qualified Domain Name used to expose the site.
     * @param string $webroot Full path to the site's webroot directory.
     * @param bool $force Optional, true to overwrite existing file.
     * @return boolean True on success
     */
    public function addSite($url, $webroot, $force = false)
    {
        $this->flushLogs();

        // Prevent overwriting default Cakebox site
        if ($url == 'default') {
            $this->_error("Using 'default' as <url> is prohibited as this would overwrite the default Cakebox site");
            return false;
        }

        // Check for existing site file
        $siteFile = $this->cbi->webserverMeta['nginx']['sites-available'] . DS . $url;
        $this->_log("Creating virtual host file for $url");
        if (file_exists($siteFile)) {
            if ($force == false) {
                $this->_error("* Site file $siteFile already exists. Use --force to drop.");
                return false;
            }
            $this->_log("* Overwriting existing file");
        }

        // Load template into string, replace placeholders
        $template = APP . 'Template' . DS . 'Bake' . DS . 'vhost_nginx.ctp';
        $config = String::insert(file_get_contents($template), [
        'url' => $url,
        'webroot' => $webroot
        ]);

        // Write generated vhost configuration to file
        if ($this->writeSystemFile($siteFile, $config) == false) {
            $this->_error("Error writing virtual hosts file $siteFile");
            return false;
        }
        $this->_log("* Successfully created $siteFile");

        // Create symbolic link in sites-enabled
        if ($this->createSiteSymlink($url) == false) {
            $this->_error("Error creating symbolic link");
            return false;
        }

        // Reload nginx service to effectuate changes
        $this->_log("Reloading webserver");
        if ($this->reloadNginx() == false) {
            return false;
        }
        return true;
    }

    /**
     * Create a database and acommpanying 'test_' prefixed test database.
     * symoblic link and reloading the webserver.
     *
     * @param string $database Name to be used for the databases.
     * @param string $username User granted local access to (only) this database.
     * @param string $password Password for above user.
     * @param bool $force Optional, true to drop existing database.
     * @return boolean True on success
     */
    public function addDatabase($database, $username, $password, $force = false)
    {
        $this->flushLogs();
        $database = CakeboxUtility::normalizeDatabaseName($database);
        $this->_log("* Normalized database name to $database");

        // do not continue if it concerns a system database
        if ($this->isSystemDatabase($database)) {
            $this->_error("Cannot proceed... `$database` is a protected system database");
            return false;
        }

        // drop existing databases if needed
        if (CakeboxUtility::databaseExists($database)) {
            if ($force == false) {
                $this->_error("* Database $database already exists. Use --force to drop.");
                return false;
            }
            $this->_log("Dropping existing databases");
            if (CakeboxUtility::dropDatabase($database) == false) {
                $this->_error("Error dropping databases");
                return false;
            }
        }

        // create databases
        $this->_log("Creating databases");
        if (CakeboxUtility::createDatabase($database, $username, $password) == false) {
            $this->_error("Error creating databases");
            return false;
        }

        // completed successfully
        return true;
    }

    /**
     * Check if a database name is actually a MySQL system database.
     *
     * @param string $database Name to be used for the databases.
     * @return boolean True if it is a system database
     */
    protected function isSystemDatabase($database)
    {
        if (in_array($database, $this->cbi->databaseMeta['mysql']['system_databases'])) {
            $this->_warn("* $database is a system database");
            return true;
        }
        return false;
    }

    /**
     * Simulates direct writing to a system file by creating the file in /tmp
     * using default credentials, then shelling a `mv` to the desired location
     * as root.
     *
     * @param string $file Full path to the file to write.
     * @param string $content Containing file body.
     * @return boolean True if the write is successful
     */
    protected function writeSystemFile($file, $content)
    {
        $this->_log("Writing system file");
        $tempFile = '/tmp/' . String::uuid();
        $fh = new File($tempFile, true);
        $fh->write($content);

        // Move the tempfile
        if ($this->shell("mv $tempFile $file", 'root') == false) {
            $this->_error("* Error moving $tempFile to $file");
            return false;
        }
        $this->_log("* Successfully wrote $file");
        return true;
    }

    /**
     * Checks if a directory is writable by the vagrant user.
     *
     * @param string $directory Full path to the directory.
     * @return boolean True if writable
     */
    public function isVagrantWritable($directory) {
        log::debug("* Checking if directory is writable by vagrant user");

        if (!is_dir($directory)) {
            log::error("* Directory does not exist");
            return false;
        }

        $testfile = $directory . DS . CakeboxUtility::getSaltCipher('heart-this');
        if (!$this->shell("touch $testfile; rm $testfile", 'vagrant')) {
            log::error("* Directory is NOT writable");
            return false;
        }
        log::debug("* Directory is writable");
        return true;
    }

    /**
     * Create a symbolic link in /etc/nginx/sites-enabled as root.
     *
     * @param string $siteFile Name of the nginx site file witouht leading path.
     * @return boolean True if created successfully
     */
    public function createSiteSymlink($siteFile)
    {
        $this->_log("Creating symbolic link");
        $link = $this->cbi->webserverMeta['nginx']['sites-enabled'] . DS . $siteFile;
        $target = $this->cbi->webserverMeta['nginx']['sites-available'] . DS . $siteFile;

        // Do nothing if the symbolic link already exists
        if (is_link($link)) {
            $this->_warn("* Skipping: symbolic link $link already exists");
            return true;
        }

        // shell `ln` command as root
        if ($this->shell("ln -s $target $link", 'root') == false) {
            $this->_error("Error creating symbolic link");
            return false;
        }
        $this->_log("* Successfully created symbolic link $link");
        return true;
    }

    /**
     * Install a software package from the Ubuntu Package archive.
     *
     * @param string $package Name of package to install  as used by `apt-get install`.
     * @return boolean True if installed successfully
     */
    public function installPackage($package)
    {
        $this->_log("Installing Ubuntu package $package");

        if (CakeboxUtility::packageInstalled($package)) {
            $this->_warn("* Package already installed");
            return false;
        }

        // not installed, shell installation
        if ($this->shell("DEBIAN_FRONTEND=noninteractive apt-get install -y $package", 'root') == false) {
            $this->_error("* Error installing package");
            return false;
        }
        $this->_log("* Package installed successfully");
        return true;
    }

    /**
     * Convenience function to update CakePHP3 app.php configuration file.
     *
     * @param string $appdir Full path to the application directory (APP)
     * @param string $url FQDN used to expose the application.
     * @return boolean True if the file was updated successfully
     */
    public function updateCake3Configuration($appdir, $url)
    {
        $this->_log("Updating config file app.php");
        $appFile = $appdir . DS . "config" . DS . "app.php";
        $database = CakeboxUtility::normalizeDatabaseName($url);

        $result = CakeboxUtility::updateConfigFile($appFile, [
            "'username' => 'my_app'" => "'username' => 'cakebox'",
            "'database' => 'my_app'" => "'database' => '$database'",
            "'database' => 'test_myapp'" => "'database' => 'test_$database'"
            ]);
            if ($result == false) {
                $this->_log("Error updating config file");
                return false;
            }
            return true;
        }

    /**
    * Convenience function to update all required CakePHP2 configuration files.
    *
    * @param string $appdir Full path to the application directory (APP).
    * @param string $url FQDN used to expose the application.
    * @return boolean True if the file was updated successfully
    */
    public function updateCake2Configuration($appdir, $url)
    {
        # Update salt/cipher in core.php
        $this->_log("Updating core.php");
        $coreFile = $appdir . DS . "app" . DS . "Config" . DS . "core.php";

        $res = CakeboxUtility::updateConfigFile($coreFile, [
            $this->cbi->frameworkMeta['cakephp2']['salt'] => CakeboxUtility::getSaltCipher($coreFile),
            $this->cbi->frameworkMeta['cakephp2']['cipher'] => CakeboxUtility::getSaltCipher($coreFile)
        ]);
        if ($res == false) {
            $this->_error("Error updating core file");
            return false;
        }

        // create database.php
        $dbFileSource = $appdir . DS . "app" . DS . "Config" . DS . "database.php.default";
        $dbFileTarget = $appdir . DS . "app" . DS . "Config" . DS . "database.php";
        if (!file_exists($dbFileTarget)) {
            copy($dbFileSource, $dbFileTarget);
            $this->_log("Created database file `$dbFileTarget`");
        }

        # update database.php
        $this->_log("Updating database.php");
        $database = CakeboxUtility::normalizeDatabaseName($url);
        $result = CakeboxUtility::updateConfigFile($dbFileTarget, [
            'test_database_name' => 'test_' . $database,
            'database_name' => $database,
            'user' => 'cakebox',
            "'password' => 'password'" => "'password' => 'secret'"
        ]);
        if ($result == false) {
            $this->_error("Error updating database file");
            return false;
        }

        # Enable debugkit in bootstrap.php
        $this->_log("Enabling DebugKit");
        $bootstrapFile = $appdir . DS . "app" . DS . "Config" . DS . "bootstrap.php";
        $fh = new File($bootstrapFile);
        $fh->append('CakePlugin::loadAll();');

        return true;
    }

    /**
     * Flush log and error buffers.
     *
     * @return void
     */
    protected function flushLogs()
    {
        $this->_debug = [];
    }

    /**
     * Create a debug entry in the application's log AND store the entry in the
     * $_log collection.
     *
     * @param string $message Debug message.
     * @return void
     */
    protected function _log($message)
    {
        Log::debug($message);
        $this->_debug[] = $message;
    }

    /**
     * Create a warning entry in the application's log AND store the entry in the
     * $_log collection.
     *
     * @param string $message Debug message.
     * @return void
     */
    protected function _warn($message)
    {
        Log::warning($message);
        $this->_debug[] = $message;
    }

    /**
     * Create an error entry in the application's log AND store the entry in the
     * $_errors collection.
     *
     * @param string $message Error message.
     * @return void
     */
    protected function _error($message)
    {
        Log::error($message);
        $this->_debug[] = $message;
    }

    /**
     * Return the list of debug entries.
     *
     * @return array Array list with debug entries.
     */
    public function debug()
    {
        return $this->_debug;
    }

    /**
     * Return the list of error entries.
     *
     * @return array Array list with error entries
     */
    public function errors()
    {
        return $this->_errors;
    }
}
