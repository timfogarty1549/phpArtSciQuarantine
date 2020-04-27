<?php
namespace App\Models;

class GameState
{
    const STATUS_PENDING  = 'PENDING';
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_PAUSED = 'PAUSED';
    const STATUS_ENDED = 'ENDED';
    
    const STATUS_ROLL = 'ROLL';
    const STATUS_MOVE = 'SELECT_MOVE';
    const STATUS_ANSWER = 'ANSWER_QUESTION';
    const STATUS_DUEL = 'DUEL';
    const STATUS_REPLICATION = 'REPLICATION';
    
    /**
     *
     * @var string
     */
    var $gameName;
    
    /**
     * 
     * @var string
     */
    var $gameCode;
    
    /**
     * 
     * @var integer;
     */
    var $gameLength;
    
    /**
     * 
     * @var boolean
     */
    var $winningSpree;

    /**
     * @var string
     */
    var $status;
    
    /**
     * @var string
     */
    var $message;
    
    /**
     *
     * @var integer
     */
    var $hostId;
    
    /**
     * 
     * @var integer
     */
    var $currentPlayer;
    
    /**
     * 
     * @var Player[];
     */
    var $players = [];
    
    /**
     * 
     * @var Dice
     */
    var $dice;
    
    /**
     * 
     * @var Position[]
     */
    var $possibleMoves = [];
    
    function __construct( $json=null ) {
        if ($json) {
            $data = json_decode($json, true);
            
            foreach ($data AS $key => $value) {
                switch($key) {
                    case 'position':
                        $this->position = new Position($value);
                        break;
                    case 'players':
                        $this->players = [];
                        foreach ($value as $player) {
                            $this->players[] = new Player($player);
                        }
                        break;
                    case 'possibleMoves':
                        $this->possibleMoves = [];
                        foreach ($value as $position) {
                            $this->possibleMoves[] = new Position($position);
                        }
                        break;
                    case 'dice':
                        $this->dice = new Dice($value);
                        break;
                    default:
                        $this->{$key} = $value;
                        break;
                }
            }
        }
    }
    
    /**
     * 
     * @param integer $pid
     * @return boolean
     */
    function isHost($pid) {
        return $this->hostId == $pid;
    }

    /**
     * 
     * @param string $game_name
     * @param string $host_name
     */
    static public function create($game_code, $game_name, $host_name)
    {
        $game = new GameState();
        $game->gameCode = $game_code;
        $game->gameName = $game_name;
        $game->gameLength = 0;
        $game->status = GameState::STATUS_PENDING;

        $game->players = [ new Player($host_name, 0) ];
        $game->players[0]->accept();
        $game->hostId = 0;
        $game->dice = new Dice();

        return $game;
    }

    public function startGame()
    {
        $this->status = GameState::STATUS_ACTIVE;
        $this->nextPlayer(true);
    }
    /**
     * 
     * @param string $player_name
     */
    public function addPlayer($player_name) {
        $this->players[] = new Player($player_name, count($this->players));
    }
    
    public function acceptPlayer($player_id)
    {
        if (!empty($this->players[$player_id])) {
            $this->players[$player_id]->accept();
        }
    }
    
    public function rejectPlayer($player_id)
    {
        if (!empty($this->players[$player_id])) {
//            $this->players[$player_id] = null;
            unset($this->players[$player_id]);          // TODO: this will mess up the numbering, need player id
        }
    }
    
    public function changeHost($player_id) {
        if (!empty($this->players[$player_id])) {
            $this->host_id = $player_id;
        }
    }
    
    public function changeOrder($newOrder) {
        foreach ($newOrder as $index=>$player_id) {
            $this->players[$player_id]->position = $index;
        }
    }
    
    public function addCategories($player_id, $categories)
    {
        if (!empty($this->players[$player_id])) {
            $this->players[$player_id]->addCategories($categories, $this->gameLength);
        }
    }
   
    
    public function setInitPosition($player_id, $ring, $index)
    {
        $position = Board::getPosition($ring, $index);
        $this->players[$player_id]->currentPosition = $position;
    }
    
    public function setStartingPlayer($player_id)
    {
        $this->currentPlayer = $player_id;
    }
    
    public function rollDice()
    {
        $this->currentCategory = '';
        $player = $this->players[$this->currentPlayer];
        $this->dice->rollDice();
        $this->possibleMoves = Board::findMoves($player->currentPosition, $this->dice->value);
        $this->status = GameState::STATUS_MOVE;
    }
    
    public function moveCurrentPlayer($ring, $index)
    {
        $position = Board::getPosition($ring, $index);
        $this->players[$this->currentPlayer]->currentPosition = $position;
        if ($position->category != Category::REROLL) {
            $this->status = GameState::STATUS_ANSWER;
        }
    }
    
    public function scorePoints($success, $points=null)
    {
        if ($success) {
            $this->players[$this->currentPlayer]->scorePoints($points);
        }
        if (!$success || !$this->winningSpree) {
            $this->nextPlayer();
        }
        
        $this->status = self::STATUS_ROLL;
    }
    
    public function nextPlayer($init=false)
    {
        if ($init) {
            $next = 0;
        } else {
            $next = $this->players[$this->currentPlayer]->order + 1;
            if ($next >= count($this->players)) {
                $next = 0;
            }
            $this->status = GameState::STATUS_ROLL;
        }
        foreach ($this->players as $index=>$player) {
            if ($player->order == $next) {
                $this->currentPlayer = $index;
                break;
            }
        }
    }
    
    public function nextPlayerAfterDuel()
    {
        $clear = true;
        foreach ($this->players as $player) {
            if ($player->status == Player::STATUS_DUEL_WINNER || $player->status == Player::STATUS_DUEL_LOOSER) {
                $clear = false;
            }
        }
        if ($clear) {
            $this->nextPlayer();
        }
    }
}

