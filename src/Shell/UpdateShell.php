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

        // Update CakePHP Code Sniffer
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
        $this->exitBashSuccess('Self-update completed successfully');
    }


    /**
     * Self-update global composer to prevent outdated warnings.
     *
     * @return boolean True on success
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
     * @return boolean True on success
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
     * Box-fix: composer update globally installed cakephp-codesniffer
     * by updating version in composer.json (if needed) and then running
     * composer update.
     *
     * @return boolean True on success
     */
    protected function _updateCakephpCodeSniffer()
    {
        $this->logInfo('Updating CakePHP Code Sniffer');

        // update package version in composer.json to 2.*
        $path = '/opt/composer-libraries/cakephp_codesniffer';
        $requiredVersion = '2.*';
        $result = CakeboxUtility::updateConfigFile(
            "$path/composer.json",
            [ '2.*@dev' => '2.*' ],
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
     * @return boolean True on success
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
     * Box-fix: add missing update-rc levels for HHVM so the service
     * automatically starts on startup + corrects default session.save_path
     *
     * @return boolean True on success
     */
    protected function _boxFixElasticsearch()
    {
        // Repair init script only once (idempotent)
        $source = APP . 'Template' . DS . 'Bake' . DS . 'box-fix-elasticsearch-init';
        $target = '/etc/init.d/elasticsearch';
        if (md5_file($source) === md5_file($target)) {
            return true;
        }

        $this->logInfo('Updating Elasticsearch configuration');
        $command = "cp $source $target";
        if (!$this->Execute->shell($command, 'root')) {
            return false;
        }

        $this->logInfo('* Restarting service');
        $command = 'service elasticsearch restart';
        if (!$this->Execute->shell($command, 'root')) {
            return false;
        }
        return true;
    }
}
