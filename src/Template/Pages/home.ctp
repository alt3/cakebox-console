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
 * @since         0.10.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Error\Debugger;
use Cake\Network\Exception\NotFoundException;

$this->layout = false;

if (!Configure::read('debug')):
	throw new NotFoundException();
endif;

$cakeDescription = 'CakePHP: the rapid development php framework';
?>
<!DOCTYPE html>
<html>
<head>
	<?= $this->Html->charset() ?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>
		<?= $cakeDescription ?>
	</title>
	<?= $this->Html->meta('icon') ?>
	<?= $this->Html->css('base.css') ?>
	<?= $this->Html->css('cake.css') ?>
</head>
<body class="home">
	<header>
		<div class="header-image">
			<?= $this->Html->image('http://cakephp.org/img/cake-logo.png') ?>
			<h1>Welcome to your Cakebox!</h1>
			<p>With a little imagination this landing page could:</p>
			<ul>
				<li>be a CakePHP showcase app</li>
				<li>be an extremely user-friendly web-based wrapper around the console Shells/Tasks</li>
				<li>integrate some of the "cool(est)" tools around like (Logstash, etc ?)</li>
				<li>be a dashboard showing all databases, apps, sites, etc</li>
				<li>basically blow all "other" framework boxes away</li>
				<li>win over new users... up-and-running using best practices in 2 minutes</li>
				<li>be highly marketable</li>
			</ul>
		</div>
	</header>
	<div id="content">

	</div>
	<footer>
	</footer>
</body>
</html>
