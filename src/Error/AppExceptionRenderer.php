<?php
namespace App\Error;

use Cake\Error\ExceptionRenderer;
use Cake\Core\Configure;

class AppExceptionRenderer extends \Cake\Error\ExceptionRenderer
{

	/**
	 * Override of the same method found in \Cake\Error\ExceptionRenderer so we
	 * can generate json responses with additional information for all custom
	 * Exceptions that support the getErrors() method.
	 *
	 * @return \Cake\Network\Response The response to be sent.
	 */
	public function render() {
		$exception = $this->error;
		$code = $this->_code($exception);
		$method = $this->_method($exception);
		$template = $this->_template($exception, $method, $code);

		$isDebug = Configure::read('debug');
		if (($isDebug || $exception instanceof HttpException) &&
		method_exists($this, $method)
		) {
			return $this->_customMethod($method, $exception);
		}

		$message = $this->_message($exception, $code);
		$url = $this->controller->request->here();

		if (method_exists($exception, 'responseHeader')) {
			$this->controller->response->header($exception->responseHeader());
		}
		$this->controller->response->statusCode($code);

		// Add errors to the view vars if the (custom App) Exception supportsd
		// the getErrors() method.
		if (method_exists($exception, 'getErrors')) {
			 $this->controller->set(array(
			 	'message' => $message,
			 	'url' => h($url),
			 	'error' => $exception,
				'errors' => $exception->getErrors(),
			 	'code' => $code,
			 	'_serialize' => array('message', 'errors', 'url', 'code')
			 ));
		} else {
			 $this->controller->set(array(
			 	'message' => $message,
			 	'url' => h($url),
			 	'error' => $exception,
			 	'code' => $code,
			 	'_serialize' => array('message', 'url', 'code')
			 ));
		}

		if ($exception instanceof CakeException && $isDebug) {
			$this->controller->set($this->error->getAttributes());
		}
		return $this->_outputMessage($template);
	}



}
