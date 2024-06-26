<?php

$router = $di->getRouter();

$router->add(
    '/convert',
    [
        'controller' => 'Currency',
        'action' => 'convert',
    ]
);

$router->handle($_SERVER['REQUEST_URI']);
