<?php

namespace App\Exception;

class ItemNotFoundException extends \Exception
{
    protected $message = "The requested item was not found.";
}