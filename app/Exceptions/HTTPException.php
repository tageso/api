<?php
namespace App\Exceptions;

/**
 * Eine maßgeschneiderte Exceptionklasse definieren
 */
class HTTPException extends \Exception
{

    protected $httpCode = 500;

    public function __construct($message, $httpCode = 500, $code = 0, Exception $previous = null)
    {
        // etwas Code

        $this->httpCode = $httpCode;

        // sicherstellen, dass alles korrekt zugewiesen wird
        parent::__construct($message, $code, $previous);
    }

    public function getHttpCode()
    {
        return $this->httpCode;
    }
}
