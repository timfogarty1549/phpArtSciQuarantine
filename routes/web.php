<?php

$router->get('/', function () use ($router) {
    return $router->app->version();
});

/*
 * routes with no JTW
 */

$router->get('create', "ArtScienceController@create");
$router->get('join', "ArtScienceController@join");

/*
 * routes where JTW must have game_code and player_id
 */

$router->group([
    'prefix'=>'player',
    'middleware' => [
        //         'auth:game',
        //         'restdata'
    ]
], function() use ($router) {
    $router->get('state', "ArtScienceController@state");
    $router->get('name', "ArtScienceController@changePlayerName");
    $router->get('categories', "ArtScienceController@setCategories");
    $router->get('position', "ArtScienceController@startPosition");
    $router->get('dice', "ArtScienceController@dice");
    $router->get('move', "ArtScienceController@move");
    $router->get('duel', "ArtScienceController@selectDuel");
    $router->post('duel', "ArtScienceController@scoreDuel");
    $router->post('genius', "ArtScienceController@scoreGenius");
});

/*
 * routes where JTW must have game_code and player_id
 * and where player_id is the host_id
 */

$router->group([
    'prefix'=>'game',
    'middleware' => [
//         'auth:game',
//         'restdata'
        ]
], function() use ($router) {
    $router->get('name', "ArtScienceController@changeGameName");
    $router->get('spree', "ArtScienceController@setWinningSpree");
    $router->get('host', "ArtScienceController@changeHost");
    $router->get('order', "ArtScienceController@reorderPlayers");
    $router->get('accept', "ArtScienceController@accept");
    $router->get('reject', "ArtScienceController@reject");
    $router->get('skip', "ArtScienceController@skipPlayer");
    $router->get('length', "ArtScienceController@gameLength");
    $router->get('status', "ArtScienceController@gameStatus");
    $router->get('score', "ArtScienceController@scorePoints");
    $router->get('duel', "ArtScienceController@endDuel");
    $router->get('undo', "ArtScienceController@undo");
});

