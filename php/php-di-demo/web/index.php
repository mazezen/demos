<?php

use FastRoute\RouteCollector;

$container = require __DIR__ . '/../app/bootstrap.php';

$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
    $r->addRoute('GET', '/', 'Mazezen\PhpDiDemo\Controller\HomeController');
    $r->addRoute('GET', '/article/{id}', ['Mazezen\PhpDiDemo\Controller\ArticleController', 'show']);
});

$route = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

switch ($route[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        echo '404 Not Found';
        break;
    
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        echo '405 Method Not Allowed';
        break;

    case FastRoute\Dispatcher::FOUND:
        $controller = $route[1];
        $parameters = $route[2];

        // PHP-DI resolves the controller name and injects its dependencies
        $container->call($controller, $parameters);
        break;
}

