<?php

$app->get("/", function () use ($app) {
    return $app->welcome();
});
