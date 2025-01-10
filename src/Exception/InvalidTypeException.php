<?php

namespace App\Exception;

class InvalidTypeException extends \Exception
{
    protected $message = "Invalid type data provided.";
}