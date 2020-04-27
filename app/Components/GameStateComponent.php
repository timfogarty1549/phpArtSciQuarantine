<?php
namespace App\Components;

use App\Exceptions\UnknownGameException;
use App\Models\GameState;
use App\Models\Game;
use Illuminate\Http\Request;
use App\Models\Player;
use App\Models\Category;

class GameStateComponent
{
    const STATUS_NOT_HOST = "NOT_HOST";
    const STATUS_NOT_YOUR_TURN = "NOT_YOUR_TURN";
    const STATUS_WRONG_STATE = "WRONG_STATE";
    const STATUS_SET_LENGTH_FIRST = "SET_LENGTH_FIRST";
    const STATUS_WRONG_NUM_CAT = "WRONG_NUM_CATEGORIES";
    
    /**
     * 
     * @var integer
     */
    private $player_id;
    
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
        /*
         * TODO: get gameId and playerId from JWT
         */
        $this->gameId = $request->input('id');
        if ($this->gameId) {
            $this->params = $request->input();
            $this->player_id = $request->input('pid', -1);
            try {
                $this->game = Game::find($this->gameId);
                $this->gameState = $this->game->gameState;
                
            } catch (\Exception $e) {
                dd($e);
                throw new UnknownGameException();
            }
        }
    }
    
    public function getGameState()
    {
        return $this->gameState;
    }
    
    public function isHost()
    {
        return $this->player_id == $this->gameState->hostId;
    }
    
    public function isCurrentPlayer()
    {
        return $this->player_id == $this->gameState->currentPlayer;
    }
    
    /**
     * 
     * @param string $status
     * @return array
     */
    static private function statusMsg($status, $message=null, $file=null, $line=null)
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
    static public function addPlayer($game_code, $name)
    {
        try {
            $game = Game::where(compact('game_code'))->firstOrFail();
            $gameState = $game->gameState;
            $gameState->addPlayer($name);
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
        if ($this->isHost()) {
            $this->gameState->acceptPlayer($player_id);
            return $this->save();
            
        } else {
            return $this->statusMsg(self::STATUS_NOT_HOST);
        }
    }
    
    /**
     * Reject the add player request
     *
     * @param integer $player_id
     * @return string
     */
    public function rejectPlayer($player_id)
    {
        if ($this->isHost()) {
            $this->gameState->rejectPlayer($player_id);
            return $this->save();
            
        } else {
            return $this->statusMsg(self::STATUS_NOT_HOST);
        }
    }
    
    
    /**
     * Change a player's name, host can change any, others only their own
     * 
     * @param string $old
     * @param string $new
     * @return array
     */
    public function changePlayersName($player_id, $name)
    {
        if (!$this->isHost()) {
            $player_id = $this->player_id;
        }
        $this->gameState->changePlayersName($player_id, $name);
        
        return $this->save();
    }
    
    /**
     * change the host to a different player
     *
     * @param integer $player_id
     * @return string
     */
    public function changeGameName($name)
    {
        if ($this->isHost()) {
            $this->gameState->gameName = $name;
            
            return $this->save();
            
        } else {
            return $this->statusMsg(self::STATUS_NOT_HOST);
        }
    }
    
    public function setWinningSpree($flag)
    {
        if ($this->isHost()) {
            $this->gameState->winningSpree = $flag;
            
            return $this->save();

        } else {
            return $this->statusMsg(self::STATUS_NOT_HOST);
        }
    }
    
    /**
     * change the host to a different player
     * 
     * @param integer $player_id
     * @return string
     */
    public function changeHost($player_id)
    {
        if ($this->isHost()) {
            $this->gameState->hostId = $player_id;
            
            return $this->save();
            
        } else {
            return $this->statusMsg(self::STATUS_NOT_HOST);
        }
    }
    
    /**
     * host rearranges the order of the players
     * 
     * @param integer[] $newOrder
     * @return string|array
     */
    public function changeOrder($newOrder)
    {
        if ($this->isHost()) {
            $this->gameState->changeOrder($newOrder);
            
            return $this->save();
            
        } else {
            return $this->statusMsg(self::STATUS_NOT_HOST);
        }
    }
    
    /**
     * host sets the length of the game
     * 
     * @param integer $length
     * @return string|array
     */
    public function gameLength($length)
    {
        if ($this->isHost()) {
            if ($this->gameState->status == GameState::STATUS_PENDING) {
                $this->gameState->gameLength = (int) $length;
                
                return $this->save();
                
            } else {
                return $this->statusMsg(self::STATUS_WRONG_STATE);
            }
            
        } else {
            return $this->statusMsg(self::STATUS_NOT_HOST);
        }
    }
    
    /**
     * Host sets the status of the game
     * @param string $status [START, PENDING, ACTIVE, PAUSED, ENDED];
     * @return string|array
     */
    public function gameStatus($status)
    {
        if ($this->isHost()) {
            if ($status == "START") {
                $this->gameState->startGame();      // set to ACTIVE and set currentPlayer
            } else {
                $this->gameState->status = $status;
            }
            return $this->save();
            
        } else {
            return $this->statusMsg(self::STATUS_WRONG_STATE);
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
    public function startingPosition($ring, $index)
    {
        $this->gameState->setInitPosition($this->player_id, $ring, $index);
        
        return $this->save();
    }
    
    /**
     * host sets the starting player
     * 
     * @param integer $player_id
     * @return string
     */
    public function startingPlayer($player_id)
    {
        if ($this->isHost()) {
            $this->gameState->setStartingPlayer($player_id);
            
            return $this->save();
            
        } else {
            return $this->statusMsg(self::STATUS_NOT_HOST);
        }
    }
    
    /**
     * each player adds their categories
     * 
     * @param integer $player_id
     * @param string[] $categories
     * @return string
     */
    public function addCategories($categories)
    {
        if ($this->gameState->gameLength == 0) {
            return $this->statusMsg(self::STATUS_SET_LENGTH_FIRST);

        } else if (count($categories) == Category::NUM_CATS[$this->gameState->gameLength]) {
            $this->gameState->addCategories($this->player_id, $categories);
            
            return $this->save();
        } else {
            return $this->statusMsg(self::STATUS_WRONG_NUM_CAT);
        }
    }
    
    /**
     * Skip the current player's turn
     * 
     * @return string
     */
    public function skipPlayersTurn() {
        if ($this->isHost()) {
            $this->gameState->nextPlayer();
            
            return $this->save();
            
        } else {
            return $this->statusMsg(self::STATUS_NOT_HOST);
        }
    }
    
    /**
     * roll dice
     * 
     * @return string
     */
    public function rollDice()
    {
        if ($this->isCurrentPlayer()) {
            if ($this->gameState->status == GameState::STATUS_ROLL) {
                $this->gameState->rollDice();
                
                return $this->save();

            } else {
                return $this->statusMsg(self::STATUS_WRONG_STATE);
            }
        } else {
            return $this->statusMsg(self::STATUS_NOT_YOUR_TURN);
        }
    }
    
    /**
     * move current player
     * 
     * @param integer $ring
     * @param integer $index
     * @return string
     */
    public function movePlayer($ring, $index)
    {
        if ($this->isCurrentPlayer()) {
            $this->gameState->moveCurrentPlayer($ring, $index);
    
            return $this->save();
            
        } else {
            return $this->statusMsg(self::STATUS_NOT_YOUR_TURN);
        }
    }
    
    /**
     * Host declares whether current player answered correctly
     * 
     * @param boolean $success
     * @param integer $points - for partial correct answer
     * @return string
     */
    public function scorePoints($success, $points=null)
    {
        if ($this->isHost()) {
            $gameOver = $this->gameState->scorePoints($success, $points);
            
            if ($gameOver) {
                $this->gameState->status = GameState::STATUS_ENDED;
            }
            
            return $this->save();
        } else {
            return $this->statusMsg(self::STATUS_NOT_HOST);
        }
    }
    
    /**
     * current player selects opponent for duel
     * 
     * @param integer $player
     * @return string
     */
    public function selectDuel($player)
    {
        if ($this->isCurrentPlayer()) {
            $this->gameState->players[$this->gameState->currentPlayer]->status = Player::STATUS_DUEL;
            $this->gameState->players[$player]->status = Player::STATUS_DUEL;
    
            return $this->save();
            
        } else {
            return $this->statusMsg(self::STATUS_NOT_YOUR_TURN);
        }
    }
    
    /**
     * host declares winner and looser in duel
     * 
     * @param integer $winner
     * @param integer $looser
     * @param boolean $draw
     * @return string
     */
    public function endDuel($winner, $looser, $draw)
    {
        if ($this->isHost()) {
            if ($draw) {
                $this->gameState->players[$winner]->status = Player::STATUS_ACTIVE;
                $this->gameState->players[$looser]->status = Player::STATUS_ACTIVE;
            } else {
                $this->gameState->players[$winner]->status = Player::STATUS_DUEL_WINNER;
                $this->gameState->players[$looser]->status = Player::STATUS_DUEL_LOOSER;
            }
            
            return $this->save();
            
        } else {
            return $this->statusMsg(self::STATUS_NOT_HOST);
        }
    }
    
    /**
     * Each player states how to distribute their duel points
     * 
     * @param {tag, points}[] $scoring
     * @return string
     */
    public function scoreDuel($scoring) {
        
        $m = $this->gameState->players[$this->player_id]->status == Player::STATUS_DUEL_WINNER ? 1 : -1;
        foreach ($scoring as $score) {
            $this->gameState->players[$this->player_id]->scorePoints($score['tag'], $m * $score['points']);
        }
        $this->gameState->nextPlayerAfterDuel();

        return $this->save();
    }
    
    /**
     * Host declares genius success
     * 
     * @param boolean $success
     */
    public function endGenius($success)
    {
        if ($this->isHost()) {
            if ($success) {
                $this->gameState->players[$this->gameState->currentPlayer]->status = Player::STATUS_GENIUS_WINNER;
            } else {
                $this->gameState->players[$this->gameState->currentPlayer]->status = Player::STATUS_ACTIVE;
                $this->gameState->nextPlayer();
            }
            
        } else {
            return $this->statusMsg(self::STATUS_NOT_HOST);
        }
    }
    
    /**
     * player distributes their geinus points
     * 
     * @param {tag, points}[] $scoring
     */
    public function scoreGenius($scoring)
    {
        if ($this->gameState->players[$this->player_id]->status == Player::STATUS_GENIUS_WINNER) {
            foreach ($scoring as $score) {
                $this->gameState->players[$this->player_id]->scorePoints($score['tag'], $score['points']);
            }
            $this->gameState->players[$this->player_id]->status = Player::STATUS_ACTIVE;
        }
        $this->gameState->nextPlayer();
    }
    
    
    public function fetchHistory()
    {
        return $this->game->history()->get()->toArray();
    }
}

