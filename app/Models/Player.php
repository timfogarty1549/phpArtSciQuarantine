<?php
namespace App\Models;

class Player
{
    const STATUS_PENDING = 'PENDING';
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_PAUSED = 'PAUSED';
    const STATUS_QUIT = 'QUIT';

    /**
     * 
     * @var string
     */
    public $status;
    
    /**
     *
     * @var string
     */
    public $name;
    
    /**
     *
     * @var integer
     */
    public $order;
    
    /**
     *
     * @var string[]
     */
    public $categories;
    
    /**
     *
     * @var Position
     */
    public $currentPosition;
    
    
    function __construct($obj, $order=-1)
    {
        if (is_null($obj)) {
            
        } else if ($order == -1) {
            $this->status = $obj['status'];
            $this->name = $obj['name'];
            $this->order = $obj['order'];
            $this->categories = [];
            foreach ($obj['categories'] as $category) {
                $this->categories[] = new Category($category, 0, 0);
            }
        } else {
            $this->status = self::STATUS_PENDING;
            $this->name = $obj;
            $this->order = $order;
            $this->categories = [];
        }
    }
    
    function accept()
    {
        $this->status = self::STATUS_ACTIVE;
    }
    
    function pause()
    {
        $this->status = self::STATUS_PAUSED;
    }
    
    function unPause()
    {
        $this->status = self::STATUS_ACTIVE;
        
    }
    
    function quit()
    {
        $this->status = self::STATUS_QUIT;
    }
    
    function addCategories($categories, $length)
    {
        $this->categories = [];
        foreach ($categories as $index=>$tag) {
            $this->categories[$tag] = new Category($tag, $index, $length);
        }
    }
    
    function scorePoints($tag, $points) 
    {
        $this->categories[$tag]->addPoints($points);
    }
}

