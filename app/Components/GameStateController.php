<?php
namespace App\Components;

use App\Exceptions\UnknownGameException;
use App\Models\GameState;
use App\Models\Game;
use Illuminate\Http\Request;

class GameStateComponent
{
    /**
     * 
     * @var array
     */
    var $params;
    
    /**
     * @var string
     */
    private $gameId;

    /**
     * @var GameState
     */
    private $gameState;
    
    /**
     * @var Game;
     */
    
    private $game;
    
    public function __construct(Request $request)
    {
        $this->gameId = $request->input('id');
        if ($this->gameId) {
            $this->params = $request->input();
            try {
                $this->game = Game::find($this->gameId);
                $this->gameState = $this->game->gameState;
                
            } catch (\Exception $e) {
                dd($e);
                throw new UnknownGameException();
            }
        }
    }
    
    public function getGameState() {
        return $this->gameState;
    }
    
    /**
     * 
     * @param string $status
     * @return array
     */
    static public function status($status, $message=null, $file=null, $line=null)
    {
        return compact('status','file', 'line', 'message');
    }
    
    /**
     * 
     * @return string
     */
    private function save($game_code = null)
    {
        if ($game_code) {
            $this->game = new Game();
            $this->game->game_code = $game_code;
            $this->game->gameState = $this->gameState;
        } else {
            $this->game->gameState = $this->gameState;
        }
        $this->game->save();

        return $this->gameState;
    }
    
    /**
     * 
     * @param string $game_name
     * @param string $host_name
     * @return []
     */
    static public function createGame($game_name, $host_name)
    {
        $game_code = substr(str_shuffle('ABCDEFGHIJKLMNPQRSTUVWXYZ123456789'), 0, 5);
        
        try {
            Game::where(compact('game_code'))->firstOrFail();        // random code already exists
            return self::createGame($game_name, $host_name);        // try again
            
        } catch (\Exception $e) {
            $game = new Game();
            $game->gameState = GameState::create($game_code, $game_name, $host_name);
            $game->game_code = $game_code;
            $game->save();
            
            return $game->gameState;
        }
    }

    /**
     * Request to add a player
     * 
     * @param string $game_code
     * @param string $player
     * @return string
     */
    static public function addPlayer($game_code, $player)
    {
        try {
            $game = Game::where(compact('game_code'))->firstOrFail();
            $gameState = $game->gameState;
            $gameState->addPlayer($player);
            $game->gameState = $gameState;
            $game->save();
            
            return self::status('Request pending');
        } catch (\Exception $e) {
            dd($e);
            return self::status('Unknown game', $e->getMessage(), $e->getFile(), $e->getLine()) ;
        }
    }
    
    /**
     * Accept the add player request
     * 
     * @param integer $player_id
     * @return string
     */
    public function acceptPlayer($player_id)
    {
        $this->gameState->acceptPlayer($player_id);
        return $this->save();
    }
    
    /**
     * Change a player's name
     * 
     * @param string $old
     * @param string $new
     * @return array
     */
    public function changeName($old, $new)
    {
        $this->gameState->changeName($old, $new);
        return $this->save();
    }
    
    /**
     * change the host to a different player
     * 
     * @param integer $player_id
     * @return string
     */
    public function changeHost($player_id)
    {
        $this->gameState->changeHost($host);
        return $this->save();
    }
    
    /**
     * rearrange the order of the players
     * 
     * @param integer[] $newOrder
     * @return string
     */
    public function changeOrder($newOrder)
    {
        $this->gameState->changeOrder($newOrder);
        return $this->save();
    }
    
    public function gameLength($length)
    {
        if ($this->gameState->status == GameState::STATUS_PENDING) {
            $this->gameState->gameLength = (int) $length;
            return $this->save();
        } else {
            return $this->status('WRONG_STATE');
        }
    }
    
    /**
     * each player sets their starting position on board
     * 
     * @param integer $player_id
     * @param integer $ring
     * @param integer $index
     * @return string
     */
    public function startingPosition($player_id, $ring, $index)
    {
        $this->gameState->setInitPosition($player-id, $ring, $index);
        
        return $this->save();
    }
    
    /**
     * host sets the starting position
     * 
     * @param integer $player_id
     * @return string
     */
    public function startingPlayer($player_id)
    {
        $this->gameState->setStartingPlayer($player_id);
        
        return $this->save();
    }
    
    /**
     * each player adds their categories
     * 
     * @param integer $player_id
     * @param string[] $categories
     * @return string
     */
    public function addCategories($player_id, $categories)
    {
        if ($this->gameState->gameLength == 0) {
            return $this->status('SET_LENGTH_FIRST');
        } else {
            $this->gameState->addCategories($player_id, $categories);
            
            return $this->save();
        }
    }
    
    /**
     * Skip the current player's turn
     * 
     * @return string
     */
    public function skipPlayersTurn() {
        $this->gameState->nextPlayer();
        
        return $this->save();
    }
    
    /**
     * role dice
     * 
     * @return string
     */
    public function rollDice()
    {
        $this->gameState->rollDice();
        
        return $this->save();
    }
    
    /**
     * move current player
     * 
     * @param integer $ring
     * @param integer $index
     * @return string
     */
    public function movePlayer($ring, $index, $player_id = 999)
    {
        $this->gameState->movePlayer($player_id, $ring, $index);

        return $this->save();
    }
    
    /**
     * score current player
     * 
     * @param boolean $success
     * @return string
     */
    public function scorePoints($success)
    {
        $this->gameState->scorePoints($success);
        $this->gameState->nextPlayer();

        return $this->save();
    }
    
    public function selectDuel($player1, $player2)
    {
        
    }
    
    public function scoreDuel($player1, $player2, $score1, $score2) {
        
    }
}

