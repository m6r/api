<?php

use App\Services\Statistics;
use Symfony\Component\HttpFoundation\Request;

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

$app->get('/stats/show/{resource}', function (Silex\Application $app, Request $req, $resource) {
    switch ($resource) {
        case 'wp-signatures':
            $scope = Statistics::SIGNATURES_WORDPRESS;
            break;
        case 'pligg-signatures':
            $scope = Statistics::SIGNATURES_PLIGG;
            break;
        case 'double-signatures':
            $scope = Statistics::SIGNATURES_DUPLICATES;
            break;
        case 'signatures':
            $scope = Statistics::SIGNATURES_TOTAL;
            break;
        default:
            $app->abort(404);
            break;
    }

    $signatures = $app['app.statistics']->getSignatures($scope);

    $response = array(
        'name' => $resource,
        'value' => $signatures,
    );

    if ('day' === $req->query->get('history')) {
        $response['history'] = $app['app.statistics']->getSignaturesHistory($scope);
    }

    return $app->json($response, 200, array(
        'Cache-Control' => 'public, max-age=300, ',
        'Access-Control-Allow-Origin' => '*',
    ));
});
