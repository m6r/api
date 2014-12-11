<?php
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

require_once __DIR__.'/../app/config.php';
require_once __DIR__.'/../app/app.php';

Request::setTrustedProxies(array('127.0.0.1'));
$app['http_cache']->run();
