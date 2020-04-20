<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    const TABLE_NAME = 'games';
    
    protected $table = self::TABLE_NAME;
    protected $primaryKey = 'id';
    protected $fillable = ['raw'];
    protected $hidden = [];
    
    public $gameObj;
    
    public function fetch($id)
    {
        $this->find($id);
        $this->game = json_decode($this->raw);
        return $this;
    }
    
    public function store()
    {
        $this->raw = json_encode($this->gameObj);
        return $this->save();
    }
    
    
    public function history()
    {
        return $this->hasMany(Journal::class,'game_id');
    }
}

