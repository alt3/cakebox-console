<?php
namespace App\Error\Exception;

use Cake\Network\Exception\BadRequestException;

/**
* Exception containing validation errors from the model. Useful for API
* responses where you need an error code in response
*
*/
class RestException extends BadRequestException
{

	/**
 	 * List of validation errors that occurred.
	 *
	 * @var array
	 */
	protected $_errors = [];

	/**
	 * Constructor
	 *
 	 * For a list of supported status codes see:
	 * http://api.cakephp.org/3.0/class-Cake.Network.Response.html#$_statusCodes
	 *
	 * @param array $errors Array with validation errors.
	 * @param int $code Status code, defaults to 412
	 */
	public function __construct($message, $errors = null, $code = 412) {
		if (empty($message)) {
			$message = 'RestFUL Error';
		}
		if (is_array($errors)) {
			$this->_errors = $errors;
		}
		parent::__construct($message, $code);
	}

	/**
	 * Getter function used by the ExceptionRenderer to add validation errors
	 * to the json response.
	 *
	 * return @array Array holding validation errors
	 */
	public function getErrors()
	{
		return $this->_errors;
	}

}
