<?php
namespace App\Models;

class GameState
{
    const STATUS_PENDING  = 'PENDING';
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_PAUSED = 'PAUSED';
    const STATUS_ENDED = 'ENDED';
    
    const STATUS_ROLL = 'ROLE';
    const STATUS_MOVE = 'SELECT_MOVE';
    const STATUS_ANSWER = 'ANSWER_QUESTION';
    
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
                        foreach ($value as $move) {
                            $this->possibleMoves[] = new Position($value);
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
            $this->players[$player_id] = null;
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
        if (!empty($this->players[$player_id])) {
            $this->players[$player_id]->move($ring, $index);
        }
    }
    
    public function setStartingPlayer($player_id)
    {
        $this->current_player = $player_id;
    }
    
    public function rollDice()
    {
        $this->currentCategory = '';
        $player = $this->players[$this->current_player];
        $this->dice->rollDice();
        $this->possibleMoves = Board::findMoves($player->ring, $player->index, $this->dice->value);
    }
    
    public function movePlayer($player_id, $ring, $index)
    {
        if ($player_id == 999) {
            $player_id = $this->currentPlayer;
        }
        if (!empty($this->players[$player_id])) {
            $this->players[$player_id]->move(Board::getPosition($ring, $index));
        }
    }
    
    public function scorePoints($success)
    {
        if (!empty($this->players[$this->current_player])) {
            $this->players[$player_id]->scorePoints($this->position, $success);
        }
    }
    
    public function nextPlayer($init=false)
    {
        if ($init) {
            $next = 0;
        } else {
            $next = $this->players[$this->currentPlayer]->position + 1;
            if ($next >= length($this->players)) {
                $next = 0;
            }
        }
        foreach ($this->players as $index=>$player) {
            if ($player->position == $next) {
                $this->currentPlayer = $index;
                break;
            }
        }
    }
}

