<?php
namespace App\Shell;

use App\Lib\CakeboxUtility;
use Cake\Console\Shell;

/**
 * Shell class for managing software updates.
 */
class UpdateShell extends AppShell
{

    /**
     * Define available subcommands, arguments and options.
     *
     * @return parser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->description([__('Manage updates.')]);

        $parser->addSubcommand('self', [
            'parser' => [
                'description' => [
                    __(
                        "Updates your Cakebox Dashboard and Cakebox Commands
                        to the most recent version."
                    )
                ]
            ]
        ]);

        return $parser;
    }

    /**
     * Self-updates the Cakebox Dashboard and Shell commands by updating the
     * cakebox-console Git repository and ALL underlying Composer libraries.
     *
     * @return void
     */
    public function self()
    {
        $this->logStart('Self-updating cakebox');

        // Update Composer
        if (!$this->_updateComposer()) {
            $this->exitBashError();
        }

        // Update Cakebox Commands and Dashboard
        if (!$this->_updateCakeboxConsole()) {
            $this->exitBashError();
        }

        // Update global Squizlabs PHP Code Sniffer
        if (!$this->_updatePhpCodeSniffer()) {
            $this->exitBashError();
        }

        // Update global CakePHP CodeSniffer
        if (!$this->_updateCakephpCodeSniffer()) {
            $this->exitBashError();
        }

        // Box fix HHVM
        if (!$this->_boxFixHhvm()) {
            $this->exitBashError();
        }

        // Box fix Elasticsearch
        if (!$this->_boxFixElasticSearch()) {
            $this->exitBashError();
        }

        // Box fix Composer permissions
        $this->_boxFixComposerPermissions();

        // All done
        $this->exitBashSuccess('Self-update completed successfully');
    }

    /**
     * Self-update global composer to prevent outdated warnings.
     *
     * @return bool True on success
     */
    protected function _updateComposer()
    {
        $this->logInfo('Self-updating Composer');
        $command = 'composer self-update';
        if (!$this->Execute->shell($command, 'root')) {
            return false;
        }

        return true;
    }

    /**
     * Self-update cakebox-console projeect by self-updating Composer (to
     * prevent outdated warning), updating the git repository and finally
     * running composer update.
     *
     * @return bool True on success
     */
    protected function _updateCakeboxConsole()
    {
        $this->logInfo('Updating Cakebox Commands and Dashboard');

        $this->logInfo('* Detecting branch');
        $command = 'cd /cakebox/console; git rev-parse --abbrev-ref HEAD';
        $branch = $this->Execute->getShellOutput($command, 'vagrant');
        if (!$branch) {
            return false;
        }
        $this->logDebug(" * Found branch $branch");

        $this->logInfo('* Updating git repository');
        $command = "cd /cakebox/console; git pull origin $branch";
        if (!$this->Execute->shell($command, 'vagrant')) {
            return false;
        }

        $this->logInfo('* Updating composer packages');
        $command = 'cd /cakebox/console; composer install --prefer-dist --no-dev';
        if (!$this->Execute->shell($command, 'vagrant')) {
            return false;
        }

        return true;
    }

    /**
     * Box-fix: composer update globally installed squizlabs/php_codesniffer
     * by updating version in composer.json (if needed) and then running
     * composer update.
     *
     * @return bool True on success
     */
    protected function _updatePhpCodeSniffer()
    {
        $this->logInfo('Updating Squizlabs PHP Code Sniffer');

        // update package version in composer.json to 2.*
        $path = '/opt/composer-libraries/php_codesniffer';
        $result = CakeboxUtility::updateConfigFile(
            "$path/composer.json",
            [ '2.*' => '^3.0.0' ],
            true // update file as root
        );

        // composer update CakePHP Coding Standard
        $this->logInfo('* Composer updating');
        $command = "composer update --no-dev --working-dir $path";
        if (!$this->Execute->shell($command, 'root')) {
            return false;
        }

        return true;
    }

    /**
     * Box-fix: composer update globally installed cakephp-codesniffer
     * by updating version in composer.json (if needed) and then running
     * composer update.
     *
     * @return bool True on success
     */
    protected function _updateCakephpCodeSniffer()
    {
        $this->logInfo('Updating CakePHP Code Sniffer');

        // update package version in composer.json to 2.*
        $path = '/opt/composer-libraries/cakephp_codesniffer';
        $result = CakeboxUtility::updateConfigFile(
            "$path/composer.json",
            [ '2.*' => '^3.0' ],
            true // update file as root
        );

        // composer update CakePHP Coding Standard
        $this->logInfo('* Composer updating');
        $command = "composer update --no-dev --working-dir $path";
        if (!$this->Execute->shell($command, 'root')) {
            return false;
        }

        return true;
    }

    /**
     * Box-fix: add missing update-rc levels for HHVM so the service
     * automatically starts on startup + corrects default session.save_path
     *
     * @return bool True on success
     */
    protected function _boxFixHhvm()
    {
        $this->logInfo('Updating HHVM configuration');

        $this->logInfo('* Creating system start/stop links');
        $command = 'update-rc.d hhvm defaults';
        if (!$this->Execute->shell($command, 'root')) {
            return false;
        }

        // Repair php.ini only once (idempotent)
        $source = APP . 'Template' . DS . 'Bake' . DS . 'box-fix-hhvm-php-ini';
        $target = '/etc/hhvm/php.ini';
        if (md5_file($source) === md5_file($target)) {
            return true;
        }

        $this->logInfo('* Correcting HHVM session.save_path');
        $command = "cp $source $target";
        if (!$this->Execute->shell($command, 'root')) {
            return false;
        }

        $this->logInfo('* Restarting service');
        $command = 'service hhvm restart';
        if (!$this->Execute->shell($command, 'root')) {
            return false;
        }

        return true;
    }

    /**
     * Box-fix:
     * - add update-rc levels for HHVM so the service runs on startup
     * - corrects default session.save_path
     * - decreases required memory from 1GB to 256MB
     *
     * @return bool True on success
     */
    protected function _boxFixElasticsearch()
    {
        // Check if environment variables script needs updating
        $sourceEnv = APP . 'Template' . DS . 'Bake' . DS . 'box-fix-elasticsearch-env-sh';
        $targetEnv = '/usr/local/etc/elasticsearch/elasticsearch-env.sh';
        if (md5_file($sourceEnv) !== md5_file($targetEnv)) {
            $updateEnv = true;
        } else {
            $updateEnv = false;
        }

        // Check if init script needs replacing
        $sourceInit = APP . 'Template' . DS . 'Bake' . DS . 'box-fix-elasticsearch-init';
        $targetInit = '/etc/init.d/elasticsearch';
        if (md5_file($sourceInit) !== md5_file($targetInit)) {
            $updateInit = true;
        } else {
            $updateInit = false;
        }

        // Do nothing if files have already been updated
        if (!$updateEnv && !$updateInit) {
            return true;
        }
        $this->logInfo('Updating Elasticsearch configuration');

        // Update env
        if ($updateEnv) {
            $this->logInfo('* Decreasing required memory');
            $command = "cp $sourceEnv $targetEnv";
            if (!$this->Execute->shell($command, 'root')) {
                return false;
            }
        }

        // Update init
        if ($updateInit) {
            $this->logInfo('* Updating initialization script');
            $command = "cp $sourceInit $targetInit";
            if (!$this->Execute->shell($command, 'root')) {
                return false;
            }
        }

        // Restart service
        $this->logInfo('* Stopping service');
        $command = 'service elasticsearch stop';
        if (!$this->Execute->shell($command, 'root')) {
            return false;
        }

        $command = 'service elasticsearch start';
        if (!$this->Execute->shell($command, 'root')) {
            return false;
        }

        return true;
    }

    /**
     * Box-fix:
     * - make sure Composer cache permissions are set to vagrant:vagrant
     *
     * @return bool True on success
     */
    protected function _boxFixComposerPermissions()
    {
        $this->logInfo('* Updating Composer cache permissions');
        $command = 'chown vagrant:vagrant /home/vagrant/.composer -R';
        if (!$this->Execute->shell($command, 'root')) {
            return false;
        }

        return true;
    }
}
