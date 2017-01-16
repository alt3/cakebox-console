<?php
namespace App\Error;

use Cake\Core\Configure;
use Cake\Error\ExceptionRenderer;

class AppExceptionRenderer extends \Cake\Error\ExceptionRenderer
{

    /**
     * Override of the same method found in \Cake\Error\ExceptionRenderer so we
     * can generate json responses with additional information for all custom
     * Exceptions that support the getErrors() method.
     *
     * @return \Cake\Network\Response The response to be sent.
     */
    public function render()
    {
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

        // Prepare additional error information if the custom Exception supports
        // the getErrors() method AND returns at least one error.
        if (method_exists($exception, 'getErrors')) {
            $errors = $exception->getErrors();
            if (count($errors)) {
                $errorKey = key($errors);
                $errors = $errors[$errorKey];
            } else {
                unset($errors);
            }
        }

        if (isset($errors)) {
             $this->controller->set([
                'message' => $message,
                'url' => h($url),
                'error' => $exception,
                $errorKey => $errors,
                'code' => $code,
                '_serialize' => ['message', 'url', 'code', $errorKey]
             ]);
        } else {
             $this->controller->set([
                'message' => $message,
                'url' => h($url),
                'error' => $exception,
                'code' => $code,
                '_serialize' => ['message', 'url', 'code']
             ]);
        }

        if ($exception instanceof CakeException && $isDebug) {
            $this->controller->set($this->error->getAttributes());
        }
        return $this->_outputMessage($template);
    }
}
