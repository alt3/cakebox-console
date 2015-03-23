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
        // Update Cakebox Commands and Dashboard
        $this->logStart('Updating Cakebox Commands and Dashboard');
        $this->LogDebug('* Please wait... this can take a moment');

        #if (!$this->execute->selfUpdate()) {
        #    $this->exitBashError("Error updating application.");
        #}
        $this->logInfo('* Update completed successfully');

        // Execute box-image updates
        $this->_updateCakephpCodeSniffer();





        $this->exitBashSuccess('Update completed successfully');
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
        $composerFile = '/opt/composer-libraries/php_codesniffer/composer.json';
        $requiredVersion = '2.*';

        $this->logInfo('Updating global cakephp-codesniffer');
        $result = CakeboxUtility::updateConfigFile(
            $composerFile,
            [ '2.*@dev' => '2.*' ],
            true // update file as root
        );
    }
}
