<?php
namespace App\Exceptions;

class DailyLimitExceededException extends \Exception
{
    protected $message = 'The daily API request limit has been exceeded';
}