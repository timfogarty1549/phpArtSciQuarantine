<?php

$router->get('/', function () use ($router) {
    return $router->app->version();
});

    $router->get('join', "ArtScienceController@join");
    $router->get('poll', "ArtScienceController@poll");
    $router->get('accept', "ArtScienceController@accept");
    $router->get('create', "ArtScienceController@create");
    $router->get('state', "ArtScienceController@state");
    $router->get('changeName', "ArtScienceController@changeName");
    $router->get('host', "ArtScienceController@host");
    $router->get('order', "ArtScienceController@order");
    $router->get('dice', "ArtScienceController@dice");
    $router->get('move', "ArtScienceController@move");
    $router->get('answered', "ArtScienceController@answered");
    $router->get('undo', "ArtScienceController@undo");
    