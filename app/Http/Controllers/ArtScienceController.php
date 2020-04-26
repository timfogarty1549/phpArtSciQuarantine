<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Components\GameStateComponent;

class ArtScienceController extends Controller
{
    /**
     * get the current state of the game
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function state(Request $request)
    {
        $c = new GameStateComponent($request);
        
        return response()->json($c->getGameState());
    }
    
    /**
     * create a new game
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function create(Request $request)
    {
        $game_name = $request->input('game');
        $host_name = $request->input('host');
        
        return response()->json(GameStateComponent::createGame($game_name, $host_name) );
    }

    /**
     * request to join an existing game via game code
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function join(Request $request)
    {
        $game_code = $request->input('game');
        $name = $request->input('name');
        
        return response()->json(GameStateComponent::addPlayer($game_code, $name) );
    }

    /**
     * host accepts request to joing
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function accept(Request $request)
    {
        $c = new GameStateComponent($request);
        $pid = $request->input('pid');
        
        return response()->json($c->acceptPlayer($pid) );
    }
    
    /**
     * host rejects request to join or removes an existing player
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function reject(Request $request)
    {
        $c = new GameStateComponent($request);
        $pid = $request->input('pid');
        
        return response()->json($c->rejectPlayer($pid) );
    }

    /**
     * player changes their name
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function changeName(Request $request)
    {
        $old = $request->input('old');
        $new = $request->input('new');
        
        $c = new GameStateComponent($request);
        
        return response()->json($c->changeName($old, $new));
    }
    
    /**
     * host reassigns hosting duties to another player
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function host(Request $request)
    {
        $c = new GameStateComponent($request);
        $host = $request->input('name');
        
        return response()->json($c->changeHost($host));
    }

    /**
     * rearrange the order of the players
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function reorder(Request $request)
    {
        $c = new GameStateComponent($request);
        $newOrder = explode(',',$request->input('players'));
        
        return response()->json($c->changeOrder($newOrder));
    }
    
    /**
     * each player sets their starting position
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function startingPosition(Request $request)
    {
        $c = new GameStateComponent($request);
        $pid = $request->input('pid');
        $ring = $request->input('ring');
        $index = $request->input('index');
        
        return response()->json($c->startingPosition($pid, $ring, $index));
    }
    
    /**
     * host sets the starting player
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function startingPlayer(Request $request)
    {
        $c = new GameStateComponent($request);
        $pid = $request->input('player');
        
        return response()->json($c->startingPlayer($pid));
    }
    
    /**
     * each player selects their categories
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function addCategories(Request $request)
    {
        $c = new GameStateComponent($request);
        $pid = $request->input('pid');
        $categories = explode(',', $request->input('categories'));
        
        return response()->json($c->addCategories($pid, $categories));
    }
    
    public function gameLength(Request $request)
    {
        $c = new GameStateComponent($request);
        $length = $request->input('length', 0);
        
        return response()->json($c->gameLength($length));
    }
    
    /**
     * get current status of game
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function poll(Request $request)
    {
        $c = new GameStateComponent($request);
        
        return response()->json($c->getGameState());
    }
    
    /**
     * skip a player's turn
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function skipPlayersTurn(Request $request)
    {
        $c = new GameStateComponent($request);
        
        return response()->json($c->skipPlayersTurn());
    }

    /**
     * role dice
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dice(Request $request)
    {
        $c = new GameStateComponent($request);
        
        return response()->json($c->rollDice());
    }
    
    public function startPosition(Request $request)
    {
        $c = new GameStateComponent($request);
        $pid = $request->input('pid');
        $ring = $request->input('ring');
        $index = $request->input('index');
        
        return response()->json($c->movePlayer($ring, $index, $pid));
    }

    /**
     * move current player to position on board
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function move(Request $request)
    {
        $c = new GameStateComponent($request);
        $ring = $request->input('ring');
        $index = $request->input('index');
        
        return response()->json($c->movePlayer($ring, $index));
    }

    public function scorePoints(Request $request)
    {
        $c = new GameStateComponent($request);
        
        $success = $request->input('success');
        
        return response()->json($c->scorePoints($success));
    }

    public function undo(Request $request)
    {
        return response()->json($this->gameState);
    }
}

