<?php
namespace App\Controller;

use App\Lib\CakeboxInfo;
use Cake\Controller\Controller;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\Network\Exception\NotFoundException;

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
     * @var Helpers available to all views
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
        $this->loadComponent('Security');
        $this->loadComponent('Csrf');
        $this->cbi = new CakeboxInfo;
    }

    /**
     * BeforeFilter
     *
     * @param \Cake\Event\Event $event
     */
    public function beforeFilter(Event $event)
    {
        // Throw 404's for non-ajax connections to ajax_ prefixed actions
        if (substr($this->request->action, 0, 5) == 'ajax_') {
            if (!$this->request->is('ajax')) {
                throw new NotFoundException;
            }
        }
    }

}
