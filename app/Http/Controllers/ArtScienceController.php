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
        $host_name = $request->input('name');
        
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
        $player = $request->input('player');
        
        return response()->json($c->acceptPlayer($player) );
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
        $player = $request->input('player');
        
        return response()->json($c->rejectPlayer($player) );
    }

    /**
     * player changes their name
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function changePlayerName(Request $request)
    {
        $name = $request->input('name');
        
        $c = new GameStateComponent($request);
        
        return response()->json($c->changeName($pid, $name));
    }
    
    /**
     * host changes game name
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function changeGameName(Request $request)
    {
        $name = $request->input('name');
        
        $c = new GameStateComponent($request);
        
        return response()->json($c->changeGameName($name));
    }
    
    /**
     * host sets flag to use Winning Spree rules
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setWinningSpree(Request $request)
    {
        $c = new GameStateComponent($request);
        
        return response()->json($c->setWinningSpree($request->input('value')));
        
    }
    
    /**
     * host reassigns hosting duties to another player
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function changeHost(Request $request)
    {
        $c = new GameStateComponent($request);
        $host = $request->input('name');
        
        return response()->json($c->changeHost($host));
    }

    /**
     * host rearrange the order of the players
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function reorderPlayers(Request $request)
    {
        $c = new GameStateComponent($request);
        $newOrder = explode(',',$request->input('players'));
        
        return response()->json($c->changeOrder($newOrder));
    }
    
    /**
     * host sets the game length
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function gameLength(Request $request)
    {
        $c = new GameStateComponent($request);
        $length = $request->input('length', 0);
        
        return response()->json($c->gameLength($length));
    }
    
    /**
     * Host skips the current player's turn
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function skipPlayer(Request $request)
    {
        $c = new GameStateComponent($request);
        
        return response()->json($c->skipPlayersTurn());
    }
    
    /**
     * each player sets their starting position
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function startPosition(Request $request)
    {
        $c = new GameStateComponent($request);
        $ring = $request->input('ring');
        $index = $request->input('index');
        
        return response()->json($c->startingPosition($ring, $index));
    }
  
    /**
     * each player selects their categories
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function setCategories(Request $request)
    {
        $c = new GameStateComponent($request);
        $categories = explode(',', $request->input('categories'));
        
        return response()->json($c->addCategories($categories));
    }

    public function gameStatus(Request $request)
    {
        $c = new GameStateComponent($request);

        return response()->json($c->gameStatus($request->input('status')));
    }
    
    /**
     * roll dice
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dice(Request $request)
    {
        $c = new GameStateComponent($request);
        
        return response()->json($c->rollDice());
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

    /**
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function scorePoints(Request $request)
    {
        $c = new GameStateComponent($request);
        $success = $request->input('success');
        $points = $request->input('points', 0);
        
        return response()->json($c->scorePoints($success, $points));
    }
    
    public function selectDuel(Request $request)
    {
        $c = new GameStateComponent($request);
        
        return response()->json($c->selectDuel($request->input('opponent')));
    }
    
    public function endDuel(Request $request)
    {
        $c = new GameStateComponent($request);
        $winner = $request->input('winner');
        $looser = $request->input('looser');
        $draw = $request->input('draw');
    
        return response()->json($c->endDuel($winner, $looser, $draw));
    }
    
    public function scoreDuel(Request $request)
    {
        $c = new GameStateComponent($request);
        $scoring = $request->input('scoring');
        
        return response()->json($c->scoreDuel($scoring));
    }

    
    public function scoreGenius(Request $request)
    {
        $c = new GameStateComponent($request);
        $scoring = $request->input('scoring');
        
        return response()->json($c->scoreGenius($scoring));
    }
    
    /**
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function undo(Request $request)
    {
        $c = new GameStateComponent($request);

        return response()->json($c->undo());
    }
    
    /**
     * 
     * @param Request $request
     */
    public function listHistory(Request $request)
    {
        $c = new GameStateComponent($request);
        return response()->json($c->fetchHistory() );
    }
}

