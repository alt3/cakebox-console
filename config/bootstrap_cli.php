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
* @since         3.0.0
* @license       http://www.opensource.org/licenses/mit-license.php MIT License
*/
use Cake\Core\Configure;
use Cake\Core\Exception\MissingPluginException;
use Cake\Core\Plugin;
use Cake\Log\Log;
use Monolog\Formatter\LogstashFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
* Additional bootstrapping and configuration for CLI environments should
* be put here.
*/
try {
	Plugin::load('Bake');
} catch (MissingPluginException $e) {
	// Do not halt if the plugin is missing
}

/**
 * Cakebox: use Monolog to create a combined log (with all log levels) to enable
 * Logstash > Elasticsearch forwarding. This logger is different from the one in
 * bootstrap.php in that is uses the 'cli' prefix tag instead of 'app'.
*/
Log::config('default', function () {
	if (is_writable('/var/log/cakephp')) {
		$handler = new StreamHandler('/var/log/cakephp/cakebox.cli.log');
	} else {
		$handler = new StreamHandler( LOGS . DS . 'cakebox.cli.log');
	}

	$formatter = new LogstashFormatter('cakephp');
	$handler->setFormatter($formatter);
	$log = new Logger('cli.cakebox', array($handler));
	return $log;
});

/**
 * Stop using the now redundant default CakePHP file loggers.
 */
Configure::delete('Log.debug');
Configure::delete('Log.error');
