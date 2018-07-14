<?php
//session_save_path(__DIR__.'/sessions');
session_start();
// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }
require __DIR__ . '/vendor/autoload.php';        

/*
error_reporting(E_ALL);

$environment = 'development';


$whoops = new \Whoops\Run;
if ($environment !== 'production') {
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
} else {
    $whoops->pushHandler(function($e){
        echo 'Todo: Friendly error page and send an email to the developer';
    });
}
$whoops->register();
*/
//Set theme
$_SESSION['theme'] = null;
if(!isset($_SESSION['theme'])){
    $date = DateTime::createFromFormat('Y-m-d', date('Y-m-d'));
    $year = $date->format("Y");
    $next_year = $year + 1;

    $cropover_start = DateTime::createFromFormat('Y-m-d', $year.'-07-01');
    $cropover_end = DateTime::createFromFormat('Y-m-d', $year.'-09-01');

    $christmas_start = DateTime::createFromFormat('Y-m-d', $year.'-11-01');
    $christmas_end = DateTime::createFromFormat('Y-m-d', $next_year.'-01-01');
    
    $easter_start = DateTime::createFromFormat('Y-m-d', $year.'-02-01');
    $easter_end = DateTime::createFromFormat('Y-m-d', $year.'-04-01');

    
    $theme = "base.css";
    // Christmas
    if($date>=$christmas_start && $date<=$christmas_end){
        $theme = "christmas.css";
    }
    //Cropover
    elseif($date>=$cropover_start && $date<=$cropover_end){
        $theme = "cropover.css";
    }
    //Easter
    elseif($date>=$easter_start && $date<=$easter_end){
        $theme = "easter.css";
    }
    $_SESSION['theme'] = $theme;
}

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $routes = include('Routes.php');
    foreach ($routes as $route) {
        $r->addRoute($route[0], $route[1], $route[2]);
    }
});

// Fetch method and URI from browser
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// Keep track of where the user was trying to go for redirect
if ($uri !== '/login') {
    $_SESSION['REQUEST_URI'] = $uri;
}

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        $class = new \Controllers\Error;
        $class->show("Error 404: That URL isn't right.");
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        $class = new \Controllers\Error;
        $class->show("Error 405: You can only use $allowedMethods with this URL");
        break;
    case FastRoute\Dispatcher::FOUND:     
        $className = $routeInfo[1][0];
        $method = $routeInfo[1][1];
        $vars = $routeInfo[2];
        $class = new $className;
        $class->$method($vars);
        break;
}
