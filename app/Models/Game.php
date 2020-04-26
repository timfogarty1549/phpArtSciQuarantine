<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    const TABLE_NAME = 'games';
    
    protected $table = self::TABLE_NAME;
    protected $primaryKey = 'id';
    protected $fillable = ['game_code', 'raw'];
    protected $hidden = [];
    
    /**
     * @param GameState $value
     */
    public function setGameStateAttribute(GameState $value)
    {
        $this->attributes['raw'] = json_encode($value);
    }
    
    public function getGameStateAttribute($value): GameState
    {
        return new GameState($this->raw);
    }
    
    public function history()
    {
        return $this->hasMany(Journal::class,'game_code');
    }
}

