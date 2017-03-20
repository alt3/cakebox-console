<?php
namespace App\Lib;

use App\Lib\CakeboxInfo;
use Cake\Cache\Cache;
use Cake\Core\Exception\Exception;
use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\File;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\Utility\Text;

/**
 * Class library for shelling commands
 */
class CakeboxExecute
{

    /**
     * Instance of \App\Lib\CakeboxInfo
     *
     * @var \App\Lib\CakeboxInfo
     */
    protected $Info;

    /**
     * List with debug information for the most recently executed command.
     *
     * @var Array
     */
    protected $_debug = [];

    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        $this->Info = new CakeboxInfo();
    }

    /**
     * Execute a system command as user vagrant or provided username.
     *
     * @param string $command Full path to the command with options and arguments.
     * @param string $username User used to execute the command (e.g. `vagrant`).
     * @return bool True if the command completed successfully
     */
    public function shell($command, $username = 'vagrant')
    {
        // Generate different sudo command based on user
        if ($username == 'root') {
            $command = "sudo $command 2>&1";
            $this->_log("Shell command as root:`$command`");
        } else {
            $command = "$command 2>&1";
            $this->_log("Shell command as vagrant: $command");
        }

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
     * Execute a system command as user vagrant or provided username.
     *
     * @param string $command Full path to the command with options and arguments.
     * @param string $username User used to execute the command (e.g. `vagrant`).
     * @return bool True if the command completed successfully
     */
    public function getShellOutput($command, $username)
    {
        // Generate different sudo command based on user
        if ($username == "root") {
            $command = "sudo $command";
        } else {
            $command = "sudo su $username -c \"$command\"";
        }
        $this->_log("Shelling command `$command` as user `$username`");

        // Execute the command, capture exit code, stdout and stderr
        exec($command, $stdout, $exitCode);

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
        $this->_log('* Shelled command completed successfully. Returning output');
        if (count($stdout) == 1) {
            return $stdout[0];
        }

        return $stdout;
    }

    /**
     * Create a directory as root and change ownership to vagrant user.
     *
     * @param string $path Full path of directory to be created.
     * @return bool True on success
     */
    public function mkVagrantDir($path)
    {
        $this->_flushLogs();
        if (!$this->shell("mkdir $path", 'root')) {
            return false;
        }

        if (!$this->shell("chown vagrant:vagrant $path -R", 'root')) {
            return false;
        }

        return true;
    }

    /**
     * Run a git config command as vagrant user.
     *
     * @param string $gitKey Name of the git config key (e.g. user.name)
     * @param string $value Value the git key will be set to
     * @return bool True on success
     */
    public function gitConfig($gitKey, $value)
    {
        Log::debug("Updating global git configuration $gitKey to $value");
        if (!$this->shell("git config --global $gitKey $value", 'vagrant')) {
            return false;
        }

        return true;
    }

    /**
     * Run `composer create-project` for a given package as user vagrant.
     *
     * @param string $package Name of the composer package (e.g. `cakephp/app`).
     * @param string $path Full path to the directory to create the project in.
     * @return bool True on success
     */
    public function composerCreateProject($package, $path)
    {
        $this->_flushLogs();
        $command = "composer create-project --prefer-dist --no-interaction $package $path";
        if (!$this->shell($command, 'vagrant')) {
            return false;
        }

        return true;
    }

    /**
     * Run `composer install` for a given package as user vagrant.
     *
     * @param string $directory Full path to the directory holding composer.json.
     * @return bool True on success
     */
    public function composerInstall($directory)
    {
        $this->_flushLogs();
        $command = "cd $directory; composer install --prefer-dist --no-interaction";
        if (!$this->shell($command, 'vagrant')) {
            return false;
        }

        return true;
    }

    /**
     * Git clone a repository.
     *
     * @param string $repository Github shortname (owner/repository).
     * @param string $path Full path to the directory to clone the repo in.
     * @return bool True on success
     */
    public function gitClone($repository, $path)
    {
        $this->_flushLogs();
        $this->_log("Cloning repository");
        $this->_log("* Repository: $repository");
        $this->_log("* Targetdir : $path");

        # Check SSH preconditions before attempting a git clone
        if (substr($repository, 0, 4) == 'git@') {
            if (!$this->_sanityCheckSSH()) {
                return false;
            }
        }

        # Execute git clone
        if (!$this->shell("git clone $repository $path", 'vagrant')) {
            return false;
        }

        return true;
    }

    /**
     * Perform sanity checks against SSH preconditions before git cloning using SSH
     *
     * @return bool True on success
     */
    protected function _sanityCheckSSH()
    {
        $this->_log("Sanity checking SSH before attempting git clone");

        $this->_log("* Sanity checking SSH Agent forwarded SSH key");
        if (!$this->shell("ssh-add -l", 'vagrant')) {
            $this->_error("Error: SSH git clone requires a SSH key, none found");
            $this->_log(" => Note: make sure your SSH agent is forwarding the required identity key if this is a private repository");
            $this->_log(" => Note: Windows users MUST use Pageant or SSH Agent Forwarding will simply not work");

            return false;
        }

        $this->_log("* Sanity checking Github user.name");
        if (!$this->shell("git config user.name", 'vagrant')) {
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
     * @param array $options Hash with options
     * @return bool True on success
     */
    public function addVhost($url, $webroot, array $options = null)
    {
        $this->_flushLogs();
        $this->_logStart("Creating virtual host for $url");

        // Prevent overwriting default Cakebox site
        if ($url == 'default') {
            $this->_error("Using 'default' as <url> is prohibited as this would overwrite the default Cakebox site");

            return false;
        }

        // Check for existing site file
        $vhostFile = $this->Info->webserverMeta['nginx']['sites-available'] . DS . $url;
        if (file_exists($vhostFile)) {
            if (!$options['force']) {
                $this->_error("* Virtual host file $vhostFile already exists. Use --force to drop.");

                return false;
            }
            $this->_log("* Overwriting existing file");
        }

        // Load template into string, replace placeholders
        if ($options['hhvm']) {
            $template = APP . 'Template' . DS . 'Bake' . DS . 'vhost_hhvm.ctp';
        } else {
            $template = APP . 'Template' . DS . 'Bake' . DS . 'vhost_nginx.ctp';
        }

        $config = Text::insert(file_get_contents($template), [
            'url' => $url,
            'webroot' => $webroot
        ]);

        // Write generated vhost configuration to file
        if (!$this->_writeSystemFile($vhostFile, $config)) {
            $this->_error("Error writing virtual hosts file $vhostFile");

            return false;
        }
        $this->_log("* Successfully created $vhostFile");

        // Create symbolic link in sites-enabled
        if (!$this->enableVhost($url)) {
            $this->_error("Error creating symbolic link");

            return false;
        }

        // Reload nginx service to effectuate changes
        if (!$this->reloadNginx()) {
            return false;
        }

        // Reload hhvm service if needed
        if (!$this->reloadHhvm()) {
            return false;
        }

        return true;
    }

    /**
     * Enables an Nginx virtual host by creating a symbolic link in
     * /etc/nginx/sites-enabled as root.
     *
     * @param string $vhostFile Name of the nginx virtual host file without path.
     * @return bool True if created successfully
     */
    public function enableVhost($vhostFile)
    {
        $this->_log("Creating symbolic link");
        $link = $this->Info->webserverMeta['nginx']['sites-enabled'] . DS . $vhostFile;
        $target = $this->Info->webserverMeta['nginx']['sites-available'] . DS . $vhostFile;

        // Do nothing if the symbolic link already exists
        if (is_link($link)) {
            $this->_warn("* Skipping: symbolic link $vhostFile already exists");

            return true;
        }

        // shell `ln` command as root
        if (!$this->shell("ln -s $target $link", 'root')) {
            $this->_error("Error creating symbolic link");

            return false;
        }
        $this->_log("* Successfully created symbolic link $link");

        return true;
    }

    /**
     * Completely removes an Nginx website by removing virtual host
     * configuration file, symbolic link and reloading Nginx.
     *
     * @param string $url Fully Qualified Domain Name used to expose the site.
     * @return bool True on success
     */
    public function removeVhost($url)
    {
        $this->_flushLogs();
        $this->_logStart("Deleting virtual host $url");

        // Prevent removing default Cakebox site
        if ($url == 'default') {
            $this->_error("Removing 'default' as <url> is prohibited as this would destroy the default Cakebox site");

            return false;
        }
        $vhostFile = $this->Info->webserverMeta['nginx']['sites-available'] . DS . $url;
        if (!is_file($vhostFile)) {
            $this->_error("Virtual host file $vhostFile does not exist");

            return false;
        }

        $this->_log("* Deleting virtual host file $vhostFile");
        if (!$this->shell("rm $vhostFile", 'root')) {
            $this->_error("Error deleting file");

            return false;
        }

        $symlink = $this->Info->webserverMeta['nginx']['sites-enabled'] . DS . $url;
        if (!is_link($symlink)) {
            $this->_log("* Skipping unlink... $symlink does not exist");
        } else {
            $this->_log("* Removing symbolic link $symlink");
            if (!$this->shell("unlink $symlink", 'root')) {
                $this->_error("Error removing symlink");

                return false;
            }
        }

        // Reload nginx service to effectuate changes
        if (!$this->reloadNginx()) {
            return false;
        }
        $this->_log("Virtual host removed successully");

        return true;
    }

    /**
     * Reload nginx webservice (not a restart!)
     *
     * @return bool True on success
     */
    public function reloadNginx()
    {
        $this->_log("Reloading Nginx webserver");

        $this->_log("* Checking configuration");
        if (!$this->shell("nginx -t", 'root')) {
            return false;
        }

        $this->_log("* Restarting service");
        if (!$this->shell("service nginx reload", 'root')) {
            return false;
        }

        return true;
    }

    /**
     * Restart HHVM service
     *
     * @return bool True on success
     */
    public function reloadHhvm()
    {
        $this->_log("Reloading HHVM service");
        if (!$this->shell("service hhvm force-reload", 'root')) {
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
     * @return bool True on success
     */
    public function addDatabase($database, $username, $password, $force = false)
    {
        $this->_flushLogs();
        $database = CakeboxUtility::normalizeDatabaseName($database);
        $this->_log("* Normalized database name to $database");

        // do not continue if it concerns a system database
        if ($this->_isSystemDatabase($database)) {
            $this->_error("Cannot proceed... `$database` is a protected system database");

            return false;
        }

        // drop existing databases if needed
        if (CakeboxUtility::databaseExists($database)) {
            if (!$force) {
                $this->_error("* Database $database already exists. Use --force to drop.");

                return false;
            }
            $this->_log("Dropping existing databases");
            if (!CakeboxUtility::dropDatabase($database)) {
                $this->_error("Error dropping databases");

                return false;
            }
        }

        // create databases
        $this->_log("Creating databases");
        if (!CakeboxUtility::createDatabasePair($database, $username, $password)) {
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
     * @return bool True if it is a system database
     */
    protected function _isSystemDatabase($database)
    {
        if (in_array($database, $this->Info->databaseMeta['mysql']['system_databases'])) {
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
     * @return bool True if the write is successful
     */
    protected function _writeSystemFile($file, $content)
    {
        $this->_log("Writing system file");
        $tempFile = '/tmp/' . Text::uuid();
        $fh = new File($tempFile, true);
        $fh->write($content);

        // Move the tempfile
        if (!$this->shell("mv $tempFile $file", 'root')) {
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
     * @return bool True if writable
     */
    public function isVagrantWritable($directory)
    {
        Log::debug("* Checking if directory is writable by vagrant user");

        if (!is_dir($directory)) {
            Log::error("* Directory does not exist");

            return false;
        }

        $testfile = $directory . DS . CakeboxUtility::getSaltCipher('heart-this');
        if (!$this->shell("touch $testfile; rm $testfile", 'vagrant')) {
            Log::error("* Directory is NOT writable");

            return false;
        }
        Log::debug("* Directory is writable");

        return true;
    }

    /**
     * Install a software package from the Ubuntu Package archive.
     *
     * @param string $package Name of package to install  as used by `apt-get install`.
     * @return bool True if installed successfully
     */
    public function installPackage($package)
    {
        $this->_log("Installing Ubuntu package $package");

        if (CakeboxUtility::packageInstalled($package)) {
            $this->_warn("* Package already installed");

            return false;
        }

        // not installed, shell installation
        if (!$this->shell("DEBIAN_FRONTEND=noninteractive apt-get install -y $package", 'root')) {
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
     * @return bool True if the file was updated successfully
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
        if (!$result) {
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
     * @return bool True if the file was updated successfully
     */
    public function updateCake2Configuration($appdir, $url)
    {
        # Update salt/cipher in core.php
        $this->_log("Updating core.php");
        $coreFile = $appdir . DS . "app" . DS . "Config" . DS . "core.php";

        $res = CakeboxUtility::updateConfigFile($coreFile, [
            $this->Info->frameworkMeta['cakephp2']['salt'] => CakeboxUtility::getSaltCipher($coreFile),
            $this->Info->frameworkMeta['cakephp2']['cipher'] => CakeboxUtility::getSaltCipher($coreFile)
        ]);
        if (!$res) {
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
        if (!$result) {
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
     * Creates a Percona XtraBackup (hot) backup of the MySQL server in
     * /tmp before moving it to the Vagrant Synced folder /cakebox/backups
     * (to prevent speed issues on systems with slow synced folders).
     *
     * @return bool Success if the backup completes succesfully
     * @throws Cake\Core\Exception\Exception
     */
    public function backupDatabases()
    {
        // determine paths
        $this->_log("Determining backup folders");

        $timestamp = (new Time)->now()->i18nFormat('YYYY-MM-dd-HH-mm-ss');
        $tempFolder = "/tmp/$timestamp";
        $this->_log("* Temporary folder => $tempFolder");

        $targetFolder = '/cakebox/backups/mysql/';
        if (!file_exists($targetFolder)) {
            $this->_log("* Creating database backup root folder $targetFolder");
            if (!mkdir($targetFolder)) {
                return false;
            };
        }
        $targetFolder .= (new Time)->now()->i18nFormat('YYYY-MM-dd-HH-mm-ss');
        $this->_log("* Destination folder => $targetFolder");

        // shell Percona XtraBackup job as root
        $this->_log("Starting hot backup");
        if (!$this->shell("xtrabackup --backup --target-dir=$tempFolder", 'root')) {
            return false;
        }

        // move backup from /tmp to synced folder
        $this->_log("Moving backup to Synced Folder");
        if (!$this->shell("mv $tempFolder $targetFolder", 'root')) {
            return false;
        }

        return true;
    }

    /**
     * Changes the protocol used by the Cakebox Dashboard by replacing the
     * default catch-all virtual host configuration file with the http or
     * https template found in the Bake directory. Then restarts Nginx to
     * effectuate the new template.
     *
     * @param string $protocol Either `http` or `https`.
     * @return bool True if protocol was changed successfully
     */
    public function setDashboardProtocol($protocol)
    {
        $this->_log("Changing Cakebox Dashboard protocol to $protocol");
        if ($protocol !== 'http' && $protocol !== 'https') {
            $this->_error("* Unsupported protocol");

            return false;
        }

        $default = "/etc/nginx/sites-available/default";
        $template = APP . 'Template' . DS . 'Bake' . DS . "nginx-cakebox-$protocol";

        $this->_log("Replacing vhost $default with $template");
        if (!$this->shell("cp $template $default", 'root')) {
            return false;
        }

        // Reload nginx service to effectuate changes
        if (!$this->reloadNginx()) {
            return false;
        }
        $this->_log("* Dashboard protocol changed successfully");

        return true;
    }

    /**
     * Flush log and error buffers.
     *
     * @return void
     */
    protected function _flushLogs()
    {
        $this->_debug = [];
    }

    /**
     * Convenience function adds a "hr" splitter element to the logs to easily
     * identify various actions when reading the plain logfile.
     *
     * @param string $message As it will appear in the log
     * @return void
     */
    protected function _logStart($message)
    {
        Log::debug(str_repeat("=", 80));
        Log::debug($message);
        $this->_debug[] = $message;
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
