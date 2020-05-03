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
    const REROLL = 'Reroll';
    
    const POINTS = [
        [8, 10, 12],
        [6, 8, 10],
        [4, 6, 8],
        [2, 4, 6],
        [0, 2, 4],
        [0, 0, 2]
    ];
    
    const NUM_CATS = [ 4, 5, 6 ];
    
    const REPLICATION_CATEGORIES = [
        self::MATH,
        self::BIO,
        self::CHEM,
        self::PHYS,
        self::TECH,
        '',
        self::MISC
    ];
    
    public $cat;
    public $score;
    public $goal;
    public $locked;
    
    function __construct($cat, $position=-1, $gameLength=-1)
    {
        if (is_null($cat)) {
            $this->cat = '';
            $this->score = 0;
            $this->goal = 0;
            $this->locked = false;
            
        } else if (is_string($cat)) {
            $this->cat = $cat;
            $this->score = 0;
            $this->goal = self::POINTS[$position][$gameLength];
            $this->locked = false;
            
        } else {
            $this->cat = $cat['cat'];
            $this->score = $cat['score'];
            $this->goal = $cat['goal'];           
            $this->locked = $cat['locked'];
        }
    }
    
    function addPoints($score)
    {
        if (!$this->locked) {
            $this->score = max( 0, min($this->score + $score, $this->goal));
        
            $this->locked = $this->score >= $this->goal;
        }
    }
    
    function updateGoal($position, $gameLength)
    {
        $this->goal = self::POINTS[$position][$gameLength];
    }
}

