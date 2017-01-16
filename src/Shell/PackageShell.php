<?php
namespace App\Shell;

use App\Lib\CakeboxUtility;
use Cake\Console\Shell;

/**
 * Shell class for managing software installation.
 */
class PackageShell extends AppShell
{

    /**
     * Define available subcommands, arguments and options.
     *
     * @return parser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->description([__('Manage Ubuntu software pacakges.')]);

        $parser->addSubcommand('add', [
            'parser' => [
                'description' => [
                __("Installs a software package from the Ubuntu Package archive.")
                ],
                'arguments' => [
                    'name' => [
                        'help' => __('Name of the software package as used by `apt-get install`.'),
                        'required' => true
                    ]
                ]
            ]
        ]);

        return $parser;
    }

    /**
     * Install a software package from the Ubuntu Package archive.
     *
     * @param string $package Name of package to install  as used by `apt-get install`.
     * @return void
     */
    public function add($package)
    {
        $this->logStart("Please wait... installing apt package $package");

        if (CakeboxUtility::packageInstalled($package)) {
            $this->exitBashWarning("* Skipping: package already installed");
        }

        if (!$this->Execute->installPackage($package)) {
            $this->exitBashError('Error installing package.');
        }
        $this->exitBashSuccess("Package installed successfully.");
    }
}
