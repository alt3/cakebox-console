<?php
namespace App\Controller;

use App\Lib\CakeboxCheck;
use App\Lib\CakeboxUtility;
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
            'vm' => $this->cbi->getVmInfo(),
            'apps' => $this->cbi->getApps(),
            'counters' => [
                'databases' => $this->cbi->getDatabaseCount(),
                'sites' => $this->cbi->getNginxFileCount()
            ],
            'commits' => $this->cbi->getRepositoryCommits('alt3/cakebox-console', 5)
        ];

        if ($this->cbi->getLatestCommitLocal() != $this->cbi->getLatestCommitRemote()) {
            $data['update'] = true;
        }

        $this->set('data', $data);
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
        $packages = $this->cbi->getPackages();
        $phpModules = $this->cbi->getPhpModules();

        $this->set([
            'operating_system' => $this->cbi->getOperatingSystem(),
            'packages' => CakeboxUtility::columnizeArray($packages, 3),
            'php_modules' => CakeboxUtility::columnizeArray($phpModules, 3),
            'nginx_modules' => $this->cbi->getNginxModules(),
            '_serialize' => ['operating_system', 'packages', 'php_modules', 'nginx_modules']
        ]);
    }

    /**
     * Serve contributors as json
     *
     * @return void
     */
    public function contributors()
    {
        $contributors = $this->cbi->getRepositoryContributors('alt3/cakebox-console');
        $this->set([
        'contributors' => CakeboxUtility::columnizeArray($contributors, 3),
        '_serialize' => ['contributors']
        ]);
    }
}
