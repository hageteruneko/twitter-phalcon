<?php

$router = $di->getRouter();

$router->add(
    "/login",
    array(
        "controller" => "index",
        "action"     => "login",
    )
);
$router->add(
    "/callback.phtml",
    array(
        "controller" => "index",
        "action"     => "callback",
    )
);
$router->add(
    "/apli",
    array(
        "controller" => "index",
        "action"     => "apli",
    )
);
// Define your routes here

$router->handle();
