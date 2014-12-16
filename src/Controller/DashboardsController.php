<?php
namespace App\Controller;

use App\Lib\CakeboxCheck;

class DashboardsController extends AppController {

/**
 * Initialization hook method.
 *
 * @todo instantiate CakeboxCheck only when needed (e.g. on tab load)
 * @return void
 */
    public function initialize() {
        parent::initialize();
        $this->cbc = new CakeboxCheck();
    }

/**
 * Dashboard index
 *
 * @return void
 */
    public function index(){
        $data = [
            'vm' => $this->cbi->getVmInfo(),
            'apps' => $this->cbi->getApps(),
            'counters' => [
                'databases' => $this->cbi->getDatabaseCount(),
                'sites' => $this->cbi->getNginxFileCount()
            ],
            'operating_system' => $this->cbi->getOperatingSystem(),
            'packages' => $this->cbi->getPackages(),
            'php_modules' => $this->cbi->getPhpModules(),
            'nginx_modules' => $this->cbi->getNginxModules(),
            'contributors' => $this->cbi->getRepositoryContributors('alt3/cakebox-console')
        ];

        if ($this->cbi->getLatestCommitLocal() != $this->cbi->getLatestCommitRemote()) {
            $data['update'] = true;
        }

        $this->set('data', $data);
    }

/**
 * Serve cakebox checks as json
 */
    public function checks() {
        $this->set([
            'system' => $this->cbc->getSystemChecks(),
            'application' => $this->cbc->getApplicationChecks('/cakebox/console'),
            'security' =>$this->cbc->getSecurityChecks(),
            '_serialize' => ['system', 'application', 'security']
        ]);
    }

}
