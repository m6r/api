<?php

$app['parameters'] = array();

require_once __DIR__.'/parameters.php';

// HTTP reverse proxy configuration
$app->register(new Silex\Provider\HttpCacheServiceProvider(), array(
   'http_cache.cache_dir' => __DIR__.'../var/cache/',
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

$app['pligg.table_prefix'] = $app['parameters.pligg_table_prefix'];
$app['wordpress.table_prefix'] = $app['parameters.wordpress_table_prefix'];
$app['wordpress.petition_id'] = $app['parameters.wordpress_petition_id'];
