<?php
namespace App\Controller;

use App\Lib\CakeboxCheck;
use App\Lib\CakeboxUtility;

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
            ]
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

/**
 * Serve box software as json
 */
     public function software() {
         $packages = $this->cbi->getPackages();
         $php_modules = $this->cbi->getPhpModules();

         $this->set([
             'operating_system' => $this->cbi->getOperatingSystem(),
             'packages' => CakeboxUtility::columnizeArray($packages, 3),
             'php_modules' => CakeboxUtility::columnizeArray($php_modules, 3),
             'nginx_modules' => $this->cbi->getNginxModules(),
             '_serialize' => ['operating_system', 'packages', 'php_modules', 'nginx_modules']
        ]);
     }

/**
 * Serve contributors as json
 */
    public function contributors() {
    $contributors = $this->cbi->getRepositoryContributors('alt3/cakebox-console');
        $this->set([
            'contributors' => CakeboxUtility::columnizeArray($contributors, 3),
            '_serialize' => ['contributors']
        ]);
    }

}
