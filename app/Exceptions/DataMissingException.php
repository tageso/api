<?php
namespace App\Exceptions;

/**
 * Eine maßgeschneiderte Exceptionklasse definieren
 */
class DataMissingException extends HTTPException
{

    protected $inputField = "";
    public $techCode = 1;

    public function __construct($inputField, $code = 0, Exception $previous = null)
    {

        $message = "The attribute ".$inputField." is missing";

        $this->inputField = $inputField;

        // sicherstellen, dass alles korrekt zugewiesen wird
        parent::__construct($message, $this->httpCode, $code, $previous);
    }

    public function getMissingInputField()
    {
        return $this->inputField;
    }
}
