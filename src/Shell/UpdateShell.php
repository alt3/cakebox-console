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

        // Execute box-image updates
        if (!$this->_updateCakephpCodeSniffer()) {
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
        $this->logInfo('Updating global CakePHP Code Sniffer');

        $path = '/opt/composer-libraries/php_codesniffer';
        $requiredVersion = '2.*';
        $result = CakeboxUtility::updateConfigFile(
            "$path/composer.json",
            [ '2.*@dev' => '2.*' ],
            true // update file as root
        );

        if (!$this->Execute->shell("composer update --working-dir $path", 'root')) {
            return false;
        }
        return true;
    }
}
