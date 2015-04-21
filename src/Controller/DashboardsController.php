<?php
namespace App\Controller;

use App\Lib\CakeboxCheck;
use App\Lib\CakeboxUtility;
use Cake\Filesystem\File;
use Cake\Log\Log;

class DashboardsController extends AppController
{

    /**
     * Dashboard index
     *
     * @return void
     */
    public function index()
    {
        $data = [
            'vm' => $this->Info->getVmInfo(),
            'apps' => $this->Info->getApps(),
            'counters' => [
                'databases' => $this->Info->getDatabaseCount(),
                'sites' => $this->Info->getNginxFileCount()
            ],
            'commits' => $this->Info->getRepositoryCommits('alt3/cakebox-console', 5),
            'contributions' => $this->Info->getContributions(
                [
                    'alt3/cakebox' => 'dev',
                    'alt3/cakebox-console' => 'dev'
                ]
            )
        ];

        $notifications = $this->Info->getNotifications();
        if ($notifications) {
            $data['notifications'] = $notifications;
        }

        $this->set('data', $data);
    }

    /**
     * VM page
     *
     * @return void
     */
    public function vm()
    {
        $data = [
            'vm' => $this->Info->getVmInfo(),
            'yaml' => $this->Info->getRichCakeboxYaml()
        ];
        $this->set('data', $data);
    }

    /**
     * Usage page
     *
     * @return void
     */
    public function usage()
    {
    }

    /**
     * Serve cakebox checks as json
     *
     * @return void
     */
    public function checks()
    {
        $cbc = new CakeboxCheck();
        $this->set([
            'system' => $cbc->getSystemChecks(),
            'application' => $cbc->getApplicationChecks('/cakebox/console'),
            'security' => $cbc->getSecurityChecks(),
            '_serialize' => ['system', 'application', 'security']
        ]);
    }

    /**
     * Serve box software as json
     *
     * @return void
     */
    public function software()
    {
        $packages = $this->Info->getPackages();
        $phpModules = $this->Info->getPhpModules();

        $this->set([
            'operating_system' => $this->Info->getOperatingSystem(),
            'packages' => CakeboxUtility::columnizeArray($packages, 3),
            'php_modules' => CakeboxUtility::columnizeArray($phpModules, 3),
            'nginx_modules' => $this->Info->getNginxModules(),
            '_serialize' => ['operating_system', 'packages', 'php_modules', 'nginx_modules']
        ]);
    }

    /**
     * Serve cakebox.cli.log as enriched json hash
     *
     * @return void
     */
    public function clilog()
    {
         $this->set([
             'log' => $this->Info->getCakeboxCliLog(),
             '_serialize' => ['log']
         ]);
    }

    /**
     * Serve contributors as json
     *
     * @return void
     */
        public function contributors()
        {
            $contributors = $this->Info->getRepositoryContributors('alt3/cakebox-console', 'dev');
            $this->set([
            'contributors' => CakeboxUtility::columnizeArray($contributors, 3),
            '_serialize' => ['contributors']
            ]);
        }

    /**
     * Return LICENSE.TXT as json
     *
     * @return void
     */
        public function license()
        {
            $this->set([
            'fileContent' => CakeboxUtility::getFileContent('/cakebox/console/LICENSE.txt'),
            '_serialize' => ['fileContent']
            ]);
        }
}
