<?php
namespace App\Controller;

use App\Lib\CakeboxCheck;
use App\Lib\CakeboxUtility;
use Cake\Log\Log;

class ApplicationsController extends AppController
{

    /**
     * Index page
     *
     * @return void
     */
    public function index()
    {
        $data = [
            'apps' => $this->cbi->getApps()
        ];
        $this->set('data', $data);
    }
}
