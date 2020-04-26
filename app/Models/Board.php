<?php
namespace App\Models;

class Board
{
    const INNER_LENGTH = 24;
    const BRIDGE_LENGTH = 6;
    const OUTER_LENGTH = 42;
    const MOD_INNER = 4;
    const MOD_OUTER = 7;
    
    /**
     * 
     * @var Position[]
     */
    static private $innerRing;
    
    /**
     *
     * @var Position[]
     */
    static private $bridge;
    
    /**
     *
     * @var Position[]
     */
    static private $outer_ring;
    
    static private function initPositions()
    {
        if (empty(self::$innerRing)) {
            self::$innerRing = [
                new Position(0, 0, Category::PHYS, 3),
                new Position(0, 1, Category::MATH, 3),
                new Position(0, 2, Category::MISC, 2),
                new Position(0, 3, Category::BIO, 2),
                
                new Position(0, 4, Category::TECH, 3),
                new Position(0, 5, Category::BIO, 3),
                new Position(0, 6, Category::MATH, 2),
                new Position(0, 7, Category::CHEM, 2),
                
                new Position(0, 8, Category::MISC, 3),
                new Position(0, 9, Category::CHEM, 3),
                new Position(0, 10, Category::BIO, 2),
                new Position(0, 11, Category::PHYS, 2),
                
                new Position(0, 12, Category::MATH, 3),
                new Position(0, 13, Category::PHYS, 3),
                new Position(0, 14, Category::CHEM, 2),
                new Position(0, 15, Category::TECH, 2),
                
                new Position(0, 16, Category::BIO, 3),
                new Position(0, 17, Category::TECH, 3),
                new Position(0, 18, Category::PHYS, 2),
                new Position(0, 19, Category::MISC, 2),
                
                new Position(0, 20, Category::CHEM, 3),
                new Position(0, 21, Category::MISC, 3),
                new Position(0, 22, Category::TECH, 2),
                new Position(0, 23, Category::MATH, 2),
            ];
            
            self::$bridge = [
                new Position(1, 0, Category::DUEL, 0),
                new Position(1, 1, Category::GENIUS, 0),
                new Position(1, 2, Category::DUEL, 0),
                new Position(1, 3, Category::GENIUS, 0),
                new Position(1, 4, Category::DUEL, 0),
                new Position(1, 5, Category::GENIUS, 0),
            ];
            
            self::$outerRing = [
                new Position(2, 0, Category::MATH, 1),
                new Position(2, 1, Category::PHYS, 1),
                new Position(2, 2, Category::CHEM, 5),
                new Position(2, 3, Category::REPLICATION, 0),
                new Position(2, 4, Category::BIO, 2),
                new Position(2, 5, Category::MISC, 1),
                new Position(2, 6, Category::BIO, 3),
                
                new Position(2, 7, Category::BIO, 1),
                new Position(2, 8, Category::TECH, 1),
                new Position(2, 9, Category::PHYS, 5),
                new Position(2, 10, Category::REROLL, 0),
                new Position(2, 11, Category::CHEM, 2),
                new Position(2, 12, Category::MATH, 1),
                new Position(2, 13, Category::CHEM, 3),
                
                new Position(2, 14, Category::CHEM, 1),
                new Position(2, 15, Category::MISC, 1),
                new Position(2, 16, Category::TECH, 5),
                new Position(2, 17, Category::REPLICATION, 0),
                new Position(2, 18, Category::PHYS, 2),
                new Position(2, 19, Category::BIO, 1),
                new Position(2, 20, Category::PHYS, 3),
                
                new Position(2, 21, Category::PHYS, 1),
                new Position(2, 22, Category::MATH, 1),
                new Position(2, 23, Category::MISC, 5),
                new Position(2, 24, Category::REROLL, 0),
                new Position(2, 25, Category::TECH, 2),
                new Position(2, 26, Category::CHEM, 1),
                new Position(2, 27, Category::TECH, 3),
                
                new Position(2, 28, Category::TECH, 1),
                new Position(2, 29, Category::BIO, 1),
                new Position(2, 30, Category::MATH, 5),
                new Position(2, 31, Category::REPLICATION, 0),
                new Position(2, 32, Category::MISC, 2),
                new Position(2, 33, Category::PHYS, 1),
                new Position(2, 34, Category::MISC, 3),
                
                new Position(2, 35, Category::MISC, 1),
                new Position(2, 36, Category::CHEM, 1),
                new Position(2, 37, Category::BIO, 5),
                new Position(2, 38, Category::REROLL, 0),
                new Position(2, 39, Category::MATH, 2),
                new Position(2, 40, Category::TECH, 1),
                new Position(2, 41, Category::MATH, 3),
            ];
        }
    }

    static public function getPosition($r, $i)
    {
        self::initPositions();
    
        switch($r) {
            case 0: return self::$innerRing($i);
            case 1: return self::$bridge($i);
            case 2: return self::$outerRing($i);
        }
    }
    
    static public function findMoves($r, $p, $dice)
    {
        self::initPositions();
        
        switch ($r) {
            case 0:
                return self::calcFromInnerRing($p, $dice);
            case 1:
                return self::calcFromMiddleRing($p, $dice);
            case 2:
                return self::calcFromOuterRing($p, $dice);
        }
    }
    
    /**
     * Starting on inner ring position $p0, return list of all possible positions of $dice moves
     * @param integer $p0
     * @param integer $dice
     * @return [][];
     */
    static private function calcFromInnerRing($p0, $dice)
    {
        $options = [];
        
        /*
         * forward
         */
        $p = ($p0 + $dice)  %  self::INNER_LENGTH;                          // same ringe
        $options[] = self::$innerRing[$p];
        
        if (($p - 1)  %  self::MOD_INNER == 0) {                            // lands on bridge
            $options[] = self::$bridge[floor($p / self::MOD_INNER)];
        }
        
        for ($i=0; $i<$dice; $i++) {
            $p1 = ($p0 + $i) % self::INNER_LENGTH;
            if ($p1  %  self::MOD_INNER == 0) {                             // next to a bridge
                $b = floor($p1 / self::MOD_INNER);
                $remaining = $dice - $i - 2;
                if ($remaining >= 0) {
                    $p = ($b * self::MOD_OUTER + $remaining) % self::OUTER_LENGTH;
                    $options[] = self::$outerRing[$p];
                    
                    if ($remaining > 0) {
                        $p = ($b * self::MOD_OUTER - $remaining + self::OUTER_LENGTH) % self::OUTER_LENGTH;
                        $options[] = self::$outerRing[$p];
                    }
                }
            }
        }

        /*
         * backwards
         */
        $p = ($p0 - $dice + self::INNER_LENGTH)  %  self::INNER_LENGTH;       // same ring
        $options[] = self::$innerRing[$p];
        
        if ($dice > 1) {
            if (($p + 1)  %  self::MOD_INNER == 0) {                              // lands on bridge
                $options[] = self::$bridge[ceil($p/self::MOD_INNER) % self::BRIDGE_LENGTH];
            }
            
            for ($i=1; $i<$dice; $i++) {
                $p1 = ($p0 - $i + self::INNER_LENGTH) % self::INNER_LENGTH;
                if ($p1  %  self::MOD_INNER == 0) {                             // next to a bridge
                    $b = ceil($p1 / self::MOD_INNER);
                    $remaining = $dice - $i - 2;
                    if ($remaining >= 0) {
                        $p = ($b * self::MOD_OUTER + $remaining) % self::OUTER_LENGTH;
                        $options[] = self::$outerRing[$p];
                        
                        if ($remaining > 0) {
                            $p = ($b * self::MOD_OUTER - $remaining + self::OUTER_LENGTH) % self::OUTER_LENGTH;
                            $options[] = self::$outerRing[$p];
                        }
                    }
                }
            }
        }
        
        return $options;
    }
    
    /**
     * Starting on bridge ring position $p0, return list of all possible positions of $dice moves
     * @param integer $p0
     * @param integer $dice
     * @return [][];
     */
    static private function calcFromMiddleRing($p0, $dice)
    {
        $options = [];
        
        $dice--;                                        // move onto other ring;
        
        $ir = $p0 * self::MOD_INNER;                                  // move onto inner ring
        $options[] = self::$innerRing[($ir + $dice) % self::INNER_LENGTH];
        if ($dice > 0) {
            $options[] = self::$innerRing[($ir - $dice + self::INNER_LENGTH) % self::INNER_LENGTH];
        }
        
        $or = $p0 * self::MOD_OUTER;                                  // move onto outer ring
        $options[] = self::$outerRing[($or + $dice) % self::OUTER_LENGTH];
        if ($dice > 0) {
            $options[] = self::$outerRing[($or - $dice + self::OUTER_LENGTH) % self::OUTER_LENGTH];
        }
        
        if ($dice == 6) {                               // move thru inner ring to next bridge to outer ring
            $options[] = self::$outerRing[(($p0 + 1) % self::BRIDGE_LENGTH)*self::MOD_OUTER];
            $options[] = self::$outerRing[(($p0 - 1 + self::BRIDGE_LENGTH) % self::BRIDGE_LENGTH)*self::MOD_OUTER];
        }
        
        return $options;
    }
    
    /**
     * Starting on outer ring position $p0, return list of all possible positions of $dice moves
     * @param integer $p0
     * @param integer $dice
     * @return [][];
     */
    static private function calcFromOuterRing($p0, $dice)
    {
        $options = [];
        
        /*
         * forward
         */
        $p = ($p0 + $dice)  %  self::OUTER_LENGTH;                          // same ringe
        $options[] = self::$outerRing[$p];
        
        if (($p - 1)  %  self::MOD_OUTER == 0) {                            // lands on bridge
            $options[] = self::$bridge[floor($p / self::MOD_OUTER)];
        }
        
        for ($i=0; $i<$dice; $i++) {
            $p1 = ($p0 + $i) % self::OUTER_LENGTH;
            if ($p1  %  self::MOD_OUTER == 0) {                             // next to a bridge
                $b = floor($p1 / self::MOD_OUTER);
                $remaining = $dice - $i - 2;
                if ($remaining >= 0) {
                    $p = ($b * self::MOD_INNER + $remaining) % self::INNER_LENGTH;
                    $options[] = self::$innerRing[$p];
                    
                    if ($remaining > 0) {
                        $p = ($b * self::MOD_INNER - $remaining + self::INNER_LENGTH) % self::INNER_LENGTH;
                        $options[] = self::$innerRing[$p];
                    }
                }
            }
        }
        
        /*
         * backwards
         */
        $p = ($p0 - $dice + self::OUTER_LENGTH)  %  self::OUTER_LENGTH;       // same ring
        $options[] = self::$outerRing[$p];
        
        if ($dice > 1) {
            if (($p + 1)  %  self::MOD_OUTER == 0) {                              // lands on bridge
                $options[] = self::$bridge[ceil($p/self::MOD_OUTER) % self::BRIDGE_LENGTH];
            }
            
            for ($i=1; $i<$dice; $i++) {
                $p1 = ($p0 - $i + self::OUTER_LENGTH) % self::OUTER_LENGTH;
                if ($p1  %  self::MOD_OUTER == 0) {                             // next to a bridge
                    $b = ceil($p1 / self::MOD_OUTER);
                    $remaining = $dice - $i - 2;
                    if ($remaining >= 0) {
                        $p = ($b * self::MOD_INNER + $remaining) % self::INNER_LENGTH;
                        $options[] = self::$innerRing[$p];
                        
                        if ($remaining > 0) {
                            $p = ($b * self::MOD_INNER - $remaining + self::INNER_LENGTH) % self::INNER_LENGTH;
                            $options[] = self::$innerRing[$p];
                        }
                    }
                }
            }
        }
        
        return $options;
    }
}

