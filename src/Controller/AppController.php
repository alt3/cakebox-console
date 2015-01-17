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
    * @var Components available to all views
    */
    public $components = [
        'Flash',
        'RequestHandler',
        'Security',
        'Csrf',
        'BootstrapUI.Flash'
    ];

    /**
     * @var Helpers available to all views
     */
    public $helpers = [
        'Cakebox',
        'BootstrapUI.Form'
    ];

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
        $this->cbi = new CakeboxInfo;
    }

    /**
     * BeforeFilter
     *
     * @param \Cake\Event\Event $event
     */
    public function beforeFilter(Event $event)
    {
		// set cakebox version
		$this->set(['version' => $this->cbi->cakeboxVersion()]);


        // Throw 404's for non-ajax connections to ajax_ prefixed actions
        if (substr($this->request->action, 0, 5) == 'ajax_') {
            if (!$this->request->is('ajax')) {
                throw new NotFoundException;
            }
        }
    }

}
