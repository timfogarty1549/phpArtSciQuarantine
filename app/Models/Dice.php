<?php
namespace App\Models;

class Dice
{
    public $value = 0;
    
    function __construct( $obj=null ) {
        if (!is_null($obj)) {
            $this->value = $obj['value'];
        }
    }
    
    function rollDice() {
        $this->value = random_int(1,6);
        if ($this->value == 6) {
            $this->value = 7;
        }
    }
}

