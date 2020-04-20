<?php
namespace Components;

use App\Models\GameState;

class GameStateController
{
    const UNKNOWN_NAME = ['status'=>'unknown_game'];
    
    private $game_id;
    private $gameState;
    
    public function __construct($game_id) {
        $this->game_id = $request->input('game');
        if ($this->game_id) {
            $this->gameState = GameState::fetch($this->game_id);
        }
    }

    public function addPlayer($player) {
        if ($this->gameState) {
            $this->gameState->addPlayer($player);
            return $this->game_id;
        } else {
            return self::UNKNOWN_GAME;
        }
    }
    
    public function acceptPlayer($player) {
        if ($this->gameState) {
            
        }
    }
}

