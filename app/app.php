<?php

$app->get('/stats/list', function () use ($app) {
    $response = array(
        'stats' => array(
            array('name' => 'signatures'),
            array('name' => 'wp-signatures'),
            array('name' => 'pligg-signatures'),
            array('name' => 'double-signatures'),
        ),
    );

    return $app->json($response, 200, array(
        'Cache-Control' => 'public, max-age=3600',
        'Access-Control-Allow-Origin:' => '*',
    ));
});

$app->get('/stats/show/{resource}', function (Silex\Application $app, $resource) {
    switch ($resource) {
        case 'wp-signatures':
            $method = 'getWordpressSignatures';
            break;
        case 'pligg-signatures':
            $method = 'getPliggSignatures';
            break;
        case 'double-signatures':
            $method = 'getDoubleSignatures';
            break;
        case 'signatures':
            $method = 'getSignatures';
            break;
        default:
            $app->abort(404);
            break;
    }

    $signatures = $app['app.statistics']->$method();

    $response = array(
        'name' => 'signatures',
        'value' => $signatures,
    );

    return $app->json($response, 200, array(
        'Cache-Control' => 'public, max-age=300, ',
        'Access-Control-Allow-Origin' => '*',
    ));
});
