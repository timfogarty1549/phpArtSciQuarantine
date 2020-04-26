<?php
namespace App\Models;

class Position
{
    /**
     * 
     * @var integer
     */
    public $ring;
    
    /**
     * 
     * @var integer
     */
    public $index;
    
    /**
     * 
     * @var string
     */
    public $category;
    
    /**
     * 
     * @var integer
     */
    public $points;
    
    function __construct( $r, $i=-1, $c=-1, $p=-1 )
    {
        if (is_null($r))  {
            $this->ring = 0;
            $this->index = 0;
            $this->category = '';
            $this->points = 0;
            
        } else if ($i == -1) {
            $this->ring = $r['ring'];
            $this->index = $r['index'];
            $this->category = $r['category'];
            $this->points = $r['points'];
            
        } else {
            $this->ring = $r;
            $this->index = $i;
            $this->category = $c;
            $this->points = $p;
        }
    }
}

