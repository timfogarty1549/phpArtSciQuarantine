<?php
namespace App\Models;

class Player
{
    public $player;
    public $categories;
    public $currentPosition
    
    function __construct($player) {
        $this->player = $player;
        $this->categories = [];
    }
    
    function addCategory($tag) {
        $this->categories[$tag] = new Category($tag);
    }
    
    function markSuccess($tag, $points) {
        $this->categories[$tag]->addPoints($points);
    }
}

