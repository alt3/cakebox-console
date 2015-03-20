<?php
namespace App\Error\Exception;

use Cake\Network\Exception\BadRequestException;

/**
 * Exception containing validation errors from the model. Useful for API
 * responses where you need an error code in response
 *
 */
class RestValidationException extends BadRequestException
{

    /**
     * List of validation errors that occurred.
     *
     * @var array
     */
    protected $_validationErrors = [];

    /**
     * Constructor
     *
     * @param array $errors Array with validation errors.
     * @param int $code Status code, defaults to 412
     */
    public function __construct($errors, $code = 412)
    {
        $message = "Data Validation Error";
        $this->_validationErrors = $errors;
        parent::__construct($message, $code);
    }

    /**
     * Getter function used by the ExceptionRenderer to add validation errors
     * to the json response.
     *
     * @return @array Array holding validation errors
     */
    public function getErrors()
    {
        return ['validation_errors' => $this->_validationErrors ];
    }
}
