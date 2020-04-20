<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GameState;
use Components\GameStateController;

class ArtScienceController extends Controller
{

    public function join(Request $request) {
        $this->game_id = $request->input('game');
        $player = $request->input('player');
        if ($this->gameState) {
            return response()->json( $this->gameState->addPlayer($player) );
        } else {
            return response()->json(['status'=>'unknown_game']);
        }
    }

    public function create(Request $request) {
        $game_id = $request->input('game');
        $host = $request->input('host');
        
        $c = new GameStateController($game_id);
        
        return response()->json($c->createGame($host) );
    }
    
    public function poll(Request $request) {
        $game_id = $request->input('game');
        $c = new GameStateController($game_id);
        
        return response()->json($c->gameState);
    }
    
    public function state(Request $request) {
        $game_id = $request->input('game');
        $c = new GameStateController($game_id);
        
        return response()->json($c->gameState);
    }
    
    public function changeName(Request $request) {
        $game_id = $request->input('game');
        $old = $request->input('old');
        $new = $request->input('new');

        $c = new GameStateController($game_id);
        
        return response()->json($c->changeName($old, $new));
    }
    
    public function host(Request $request) {
        $game_id = $request->input('game');
        $host = $request->input('host');
        
        $c = new GameStateController($game_id);
        
        return response()->json($c->changeHost($host));
    }
    
    public function order(Request $request) {
        $game_id = $request->input('game');
        $order = $request->input('order');
        
        $c = new GameStateController($game_id);
        
        return response()->json($c->changeOrder($order));
    }
    
    public function dice(Request $request) {
        $game_id = $request->input('game');
        $player = $request->input('player');
        $c = new GameStateController($game_id);
        
        return response()->json($c->rollDice($player));
    }
    
    public function move(Request $request) {
        $game_id = $request->input('game');
        $player = $request->input('player');
        $position = $request->input('position');

        $c = new GameStateController($game_id);
        
        return response()->json($c->move($player, $position));
    }
    
    public function answered(Request $request) {
        $game_id = $request->input('game');
        $player = $request->input('player');
        $answer = $request->input('answer');
        
        $c = new GameStateController($game_id);
        
        return response()->json($c->answer($player, $answer));
    }
    
    public function undo(Request $request) {
        
        return response()->json($this->gameState);
    }
  
}

