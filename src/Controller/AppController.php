<?php
namespace App\Controller;

use App\Lib\CakeboxInfo;
use Cake\Controller\Controller;
use Cake\Datasource\ConnectionManager;

/**
 * Application Controller
 *
 * Application-wide methods inherited by all controllers.
 */
class AppController extends Controller
{

    /**
     * @var Our Controllers do not use tables
     */
    public $uses = false;

    /**
     * @var Helpers made available to all Views
     */
    public $helpers = ['Cakebox'];

    /**
     * CakeboxInfo instance available to all Controllers
     *
     * @var \App\Lib\CakeboxInfo
     */
    public $cbi;

    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize()
    {
        $this->loadComponent('Flash');
        $this->loadComponent('RequestHandler');
        $this->cbi = new CakeboxInfo;
    }
}
