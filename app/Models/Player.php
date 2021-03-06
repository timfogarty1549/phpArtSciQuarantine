<?php
namespace App\Models;

class Player
{
    const STATUS_PENDING = 'PENDING';
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_PAUSED = 'PAUSED';
    const STATUS_QUIT = 'QUIT';

    const STATUS_DUEL_WINNER = 'DUEL_WINNER';
    const STATUS_DUEL_LOOSER = 'DUEL_LOOSER';
    const STATUS_GENIUS_WINNER = 'GENIUS_WINNER';
    
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
     * @var string
     */
    public $uuid;
    
    /**
     * 
     * @var string
     */
    public $color;
    
    /**
     * @var string
     */
    public $icon;
    
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
    
    
    function __construct($obj, $uuid=null, $order=-1, $length=0, $color='red', $icon='\uF047')
    {
        if (is_null($obj)) {
            
        } else if ($order == -1) {
            $this->status = $obj['status'];
            $this->name = $obj['name'];
            $this->order = $obj['order'];
            $this->uuid = $obj['uuid'];
            $this->color = $obj['color'];
            $this->icon = $obj['icon'];
            
            $this->categories = [];
            if (!empty($obj['categories'])) {               // should not be necessary if created correctly - find bug
                foreach ($obj['categories'] as $category) {
                    $this->categories[] = new Category($category, 0, 0);
                }
            }
            $this->currentPosition = new Position($obj['currentPosition']);
            
        } else {
            $this->status = self::STATUS_PENDING;
            $this->name = $obj;
            $this->order = $order;
            $this->uuid = $uuid;
            $this->color = $color;
            $this->icon = $icon;
            $this->currentPosition = null;
            $this->categories = [];
            $this->initCategories($length);
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
    
    function initCategories($length) {
        $numCat = Category::NUM_CATS[$length];
        $len = count($this->categories);
        if ($len > $numCat) {
            $this->categories = array_slice($player->categories, 0, $numCat);
        }
        for ($i=0; $i<$numCat; $i++) {
            if ($i < $len) {
                $this->categories->updateGoal($i, $length);
            } else {
                $this->categories[] = new Category('', $i, $length);
            }
        }
        
    }
    
    function scorePoints($points=null) 
    {
        $tag = $this->currentPosition->category;
        if (!$points) {
            $points = $this->currentPosition->points;
        }
        
        foreach ($this->categories as $index=>$category) {
            if ($category->cat == $tag) {
                $this->categories[$index]->addPoints($points);
            }
        }
        
        return $this->isWinner();
    }
    
    function isWinner() {
        foreach ($this->categories as $category) {
            if (!$category->locked) return false;
        }
        
        return true;
    }
}

