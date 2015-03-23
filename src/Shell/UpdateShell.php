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

        // Update Cakebox Commands and Dashboard
        $this->logStart('Updating Cakebox Commands and Dashboard');
        $this->LogDebug('* Please wait... this can take a moment');

        if (!$this->execute->selfUpdate()) {
            $this->exitBashError("Error updating application.");
        }
        $this->logInfo('* Update completed successfully');

        // Execute box-image updates
        if (!$this->_updateCakephpCodeSniffer()) {
            $this->exitBashError('Error running composer update');
        }
        $this->logInfo('* composer update completed succesfully');



        $this->exitBashSuccess('Self-update completed successfully');
    }

    /**
     * Box image update: update composer installed global cakephp-codesniffer
     * by updating version in composer.json (if needed) and then running
     * composer update.
     *
     * @return void
     */
    protected function _updateCakephpCodeSniffer()
    {
        $path = '/opt/composer-libraries/php_codesniffer';
        $requiredVersion = '2.*';

        $this->logInfo('Updating global cakephp-codesniffer');
        $result = CakeboxUtility::updateConfigFile(
            "$path/composer.json",
            [ '2.*@dev' => '2.*' ],
            true // update file as root
        );

        if (!$this->execute->shell("cd $path; composer update")) {
            return false;
        }
        return true;
    }
}
