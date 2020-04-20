<?php
namespace App\Models;

class Category
{
    const BIO = 'Biology';
    const CHEM = 'Chemistry';
    const MATH = 'Math';
    const MISC = 'Miscelanious';
    const PHYS = 'Physics';
    const TECH = 'Technology';
    
    const DUEL = 'Duel';
    const GENIUS = 'Genius';
    const REPLICATION = 'Replication';
    const REROLL = 'Rerole';
    
    const POINTS = [
        [8, 10, 12],
        [6, 8, 10],
        [4, 6, 8],
        [2, 4, 6],
        [0, 2, 4],
        [0, 0, 2]
    ];
    
    public $cat;
    public $score;
    public $goal;
    
    function __construct($cat, $position, $gameLength) {
        $this->cat = $cat;
        $this->score = 0;
        $this->goal = self::POINTS[$position][$gameLength];
    }
    
    function addPoints($score) {
        $this->score = max($this->score+$score, $this->goal);
    }
}

