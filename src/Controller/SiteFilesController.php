<?php
namespace App\Controller;

use App\Lib\CakeboxInfo;

class SiteFilesController extends AppController {

/**
 * Index
 *
 * @return void
 */
    public function index(){
        $this->set('data', [
            'directories' => [
                'sites-available' => '/etc/nginx/sites-available',
                'sites-enabled' => '/etc/nginx/sites-enabled'
            ],
            'sitefiles' => $this->cbi->getRichNginxFiles(),
        ]);
    }

}
