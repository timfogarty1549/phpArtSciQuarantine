<?php
namespace App\Models;

class GameState
{
    private $game;
    private $obj;

    function __construct($raw=null) {
        if ($raw) {
            $this->obj = json_decode($raw);
        }
    }
    
    public function create($id, $host) {
        $this->obj = [
            'game' => new Game($id, $host),
            'board' => new Board(),
            'players' => [ new Player($host, true)],
            'dice' => new Dice()
            
        ];
        $this->game = Game::create(['raw'=> json_encode($this->obj)]);
    }
    
    public function fetch( $game_id ) {
        $this->game = Game::find($game_id);
        if ($this->game) {
            return new GameState($this->game->raw);
        }
    }
    
    public function save() {
        $this->game->raw = json_encode($this->obj);
        $this->game->save();
    }
}

