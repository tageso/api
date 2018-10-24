<?php
namespace App\Exceptions;
/**
 * Eine maßgeschneiderte Exceptionklasse definieren
 */
class NotLoggedInException extends HTTPException {

    public $techCode = 2;

    public function __construct($code = 0, Exception $previous = null) {

        $message = "You need to login to perform this action";
        parent::__construct($message, 401, $code, $previous);
    }
}