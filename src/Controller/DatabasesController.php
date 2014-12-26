<?php
namespace App\Controller;

use App\Lib\CakeboxInfo;

class DatabasesController extends AppController
{

    /**
     * Index
     *
     * @return void
     */
    public function index()
    {
        $this->set('databases', $this->cbi->getAppDatabases());
    }
}
