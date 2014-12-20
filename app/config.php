<?php

$app['parameters'] = array();

require_once __DIR__.'/parameters.php';

// HTTP reverse proxy configuration
$app->register(new Silex\Provider\HttpCacheServiceProvider(), array(
   'http_cache.cache_dir' => __DIR__.'/../var/cache/',
   'http_cache.esi'       => null,
));

// DB configuration
$app->register(new Silex\Provider\DoctrineServiceProvider());

$app['db.options'] = array(
    'driver' => 'pdo_mysql',
    'host' => $app['parameters.db_host'],
    'dbname' => $app['parameters.db_name'],
    'user' => $app['parameters.db_user'],
    'password' => $app['parameters.db_password'],
    'port' => $app['parameters.db_port'],
);

// Application configuration

$app['app.pligg_table_prefix'] = $app['parameters.pligg_table_prefix'];
$app['app.wordpress_table_prefix'] = $app['parameters.wordpress_table_prefix'];
$app['app.wordpress_petition_id'] = $app['parameters.wordpress_petition_id'];

// Services configuration
$app['app.statistics'] = function ($app) {
    $db = $app['db'];
    $pliggTable = $app['app.pligg_table_prefix'].'users';
    $wpTable = $app['app.wordpress_table_prefix'].'dk_speakup_signatures';
    $wpPetitionID = $app['app.wordpress_petition_id'];

    return new App\Services\Statistics($db, $pliggTable, $wpTable, $wpPetitionID);
};
