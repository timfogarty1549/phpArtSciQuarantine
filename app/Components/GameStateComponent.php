<?php
namespace App\Components;

use App\Exceptions\UnknownGameException;
use App\Models\GameState;
use App\Models\Game;
use Illuminate\Http\Request;
use App\Models\Player;
use App\Models\Category;
use App\Exceptions\ArtScienceException;
use App\Exceptions\NotHostException;
use App\Exceptions\PlayerNotFoundException;
use App\Exceptions\NotYourTurnException;

class GameStateComponent
{
    const STATUS_WRONG_STATE = "WRONG_STATE";
    const STATUS_JOIN_PENDING = "PENDING";
    const STATUS_SET_LENGTH_FIRST = "SET_LENGTH_FIRST";
    const STATUS_WRONG_NUM_CAT = "WRONG_NUM_CATEGORIES";
    
    /**
     * uuid of the player making the request
     * @var string
     */
    private $uuid;
    
    /**
     * index of the player making the request
     * @var integer
     */
    private $player_id;
    
    /**
     * id of the game
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
         * TODO: get gameId and uuid/playerId from JWT
         */
        $this->gameId = strtoupper($request->input('game'));
        if ($this->gameId) {
            try {
                $this->game = Game::where('game_code', $this->gameId)->firstOrFail();
                $this->gameState = $this->game->gameState;
                
            } catch (\Exception $e) {
                throw new UnknownGameException($this->gameId);
            }
            $this->uuid = $request->input('uuid', '');
            $this->player_id = $this->gameState->findPlayer($this->uuid);
        }
    }

    public function getGameState()
    {
        return $this->gameState;
    }
    
    public function isHost($throw=true)
    {
        $flag = $this->player_id == $this->gameState->hostId;
        
        if (!$flag && $throw) {
            throw new NotHostException();
        }
        
        return $flag;
    }
    
    public function isCurrentPlayer($throw = false)
    {
        $flag = $this->player_id == $this->gameState->currentPlayer;
        
        if (!$flag && $throw) {
            throw NotYourTurnException();
        }
    }
    
    public function isValidPlayer($throw=true) {
        $flag = $this->player_id != -1;
        
        if( !$flag && $throw) {
            throw new PlayerNotFoundException($this->uuid);
        }
    }
    /**
     * 
     * @param string $status
     * @return array
     */
    static private function statusMsg($tag, $message=null)
    {
        $status = 'ERROR';
        return compact('status', 'tag', 'message');
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
    static public function createGame($game_name, $length, $spree, $host_name, $uuid)
    {
        $game_code = substr(str_shuffle('ABCDEFGHIJKLMNPQRSTUVWXYZ123456789'), 0, 5);
        
        try {
            Game::where(compact('game_code'))->firstOrFail();        // random code already exists
            
            return self::createGame($game_name, $length, $spree, $host_name, $uuid);        // try again
            
        } catch (\Exception $e) {
            $game = new Game();
            $game->gameState = GameState::create($game_code, $game_name, $length, $spree, $host_name, $uuid);
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
    static public function addPlayer($game_code, $name, $uuid)
    {
        try {
            $game = Game::where(compact('game_code'))->firstOrFail();
        } catch (\Exception $e) {
            throw new UnknownGameException($game_code);
        }

        $gameState = $game->gameState;
        $gameState->addPlayer($name, $uuid);
        $game->gameState = $gameState;
        $game->save();
        
        return self::statusMsg(self::STATUS_JOIN_PENDING);
    }
    
    /**
     * Accept the add player request
     * 
     * @param integer $player_id
     * @return string
     */
    public function acceptPlayer($uuid)
    {
        
        $this->isHost();
        $this->gameState->acceptPlayer($uuid);
        return $this->save();
    }
    
    /**
     * Reject the add player request
     *
     * @param integer $player_id
     * @return string
     */
    public function rejectPlayer($player_id)
    {
        $this->isHost();
        $this->gameState->rejectPlayer($player_id);
        return $this->save();
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
        if (!$this->isHost(false)) {
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
        $this->isHost();
        $this->gameState->gameName = $name;
            
        return $this->save();
    }
    
    public function setWinningSpree($flag)
    {
        $this->isHost();
        $this->gameState->winningSpree = $flag;
            
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
        $this->isHost();
        $this->gameState->hostId = $player_id;
            
        return $this->save();
    }
    
    /**
     * host rearranges the order of the players
     * 
     * @param integer[] $newOrder
     * @return string|array
     */
    public function changeOrder($newOrder)
    {
        $this->isHost();
        $this->gameState->changeOrder($newOrder);

        return $this->save();
    }
    
    /**
     * host sets the length of the game
     * 
     * @param integer $length
     * @return string|array
     */
    public function gameLength($length)
    {
        $this->isHost();

        if ($this->gameState->status == GameState::STATUS_PENDING) {
            $this->gameState->changeGameLength( (int) $length );
                
            return $this->save();
                
        } else {
            return $this->statusMsg(self::STATUS_WRONG_STATE);
        }
     }
    
    /**
     * Host sets the status of the game
     * @param string $status [START, PENDING, ACTIVE, PAUSED, ENDED];
     * @return string|array
     */
    public function gameStatus($status)
    {
        if ($this->isHost(false)) {
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
        $this->isValidPlayer();

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
        $this->isHost();
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
    public function addCategories($categories)
    {
        $this->isValidPlayer();
        
        if ($this->gameState->gameLength < 0) {
            return $this->statusMsg(self::STATUS_SET_LENGTH_FIRST);

        } else if (count($categories) == Category::NUM_CATS[$this->gameState->gameLength]) {
            $this->gameState->addCategories($this->player_id, $categories);
            
            return $this->save();
        } else {
            $msg = "=> ".$this->gameState->gameLength. " ". Category::NUM_CATS[$this->gameState->gameLength]. " ".count($categories);
            return $this->statusMsg(self::STATUS_WRONG_NUM_CAT, $msg);
        }
    }
    
    /**
     * Skip the current player's turn
     * 
     * @return string
     */
    public function skipPlayersTurn() {
        $this->isHost();
        $this->gameState->nextPlayer();
            
        return $this->save();
    }
    
    /**
     * roll dice
     * 
     * @return string
     */
    public function rollDice()
    {
        $this->isCurrentPlayer();
        if ($this->gameState->status == GameState::STATUS_ROLL) {
            $this->gameState->rollDice();
            
            return $this->save();

        } else {
            return $this->statusMsg(self::STATUS_WRONG_STATE);
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
        $this->isCurrentPlayer();
        $this->gameState->moveCurrentPlayer($ring, $index);
    
        return $this->save();
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
        $this->isHost();
        $gameOver = $this->gameState->scorePoints($success, $points);
            
        if ($gameOver) {
            $this->gameState->status = GameState::STATUS_ENDED;
        }
        
        return $this->save();
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
            throw new NotHostException();

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
            throw new NotHostException();

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

