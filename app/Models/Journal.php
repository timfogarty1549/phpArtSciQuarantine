<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    const TABLE_NAME = 'journal';
    
    protected $table = self::TABLE_NAME;
    protected $primaryKey = 'id';
    protected $fillable = ['game_id','raw'];
    protected $hidden = [];
    
    public function game()
    {
        return $this->belongsToMany(GameState::class);
    }
}

