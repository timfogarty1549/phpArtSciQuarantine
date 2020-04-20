<?php
namespace App\Models;

class Dice
{
    public $value = 0;
    
    function rollDice() {
        $this->value = random_int(1,6);
        if ($this->value == 6) {
            $this->value = 7;
        }
    }
}

