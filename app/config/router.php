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
$router->add(
    "/profile",
    array(
        "controller" => "index",
        "action"     => "profile",
    )
);
// Define your routes here

$router->handle();
