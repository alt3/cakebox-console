<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use App\Lib\CakeboxInfo;
use Cake\Controller\Controller;
use Cake\Datasource\ConnectionManager;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {

/**
 * @var Our Controllers do not use tables
 */
	var $uses = false;

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
	public function initialize() {
		$this->loadComponent('Flash');
		$this->loadComponent('RequestHandler');
		$this->cbi = new CakeboxInfo;
	}

}
