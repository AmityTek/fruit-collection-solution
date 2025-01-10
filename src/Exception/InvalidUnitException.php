<?php

namespace App\Exception;

class InvalidUnitException extends \Exception
{
    protected $message = 'Invalid unit. Only "grams" and "kilograms" are allowed.';
}
