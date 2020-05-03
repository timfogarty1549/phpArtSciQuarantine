<?php

namespace App\Exceptions;

use Exception;

class ArtScienceException extends Exception
{
   protected $code = 200;
   protected $tag = 'ERROR';
    
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($this->message, 200, $previous);
    }
    
    public function getTag()
    {
        return $this->tag;
    }
    
    public function setTag($tag)
    {
        $this->tag = $tag;
    }
}