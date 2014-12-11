<?php

$app->get('/stats/list', function () use ($app) {
    $response = array(
        'stats' => array(
            array('name' => 'signatures'),
        ),
    );

    return $app->json($response);
});

$app->get('/stats/show/signatures', function () use ($app) {
    $pliggTable = $app['pligg.table_prefix'].'users';
    $wpTable = $app['wordpress.table_prefix'].'dk_speakup_signatures';
    $wpPetitionID = $app['wordpress.petition_id'];

    // Combien d'utilisateurs sur pligg
    $sql = 'SELECT COUNT(user_id) FROM '.$pliggTable;
    $pliggUsers = $app['db']->fetchColumn($sql);

    // Combien de signataires sur Wordpress
    $sql = 'SELECT COUNT(id) FROM '.$wpTable.' '.
        'WHERE petitions_id = ?';
    $wpSignatures = $app['db']->fetchColumn($sql, array($wpPetitionID));

    // Combien sont dans les deux
    $sql = 'SELECT COUNT(user_id) FROM '.$pliggTable.' '.
        'INNER JOIN '.$wpTable.' '.
        'ON '.$pliggTable.'.user_email='.$wpTable.'.email '.
        'WHERE '.$wpTable.'.petitions_id = ?';
    $duplicates = $app['db']->fetchColumn($sql, array($wpPetitionID));

    $response = array(
        'name' => 'signatures',
        'value' => $pliggUsers + $wpSignatures - $duplicates,
    );

    return $app->json($response, 200, array(
        'Cache-Control' => 'public, max-age=300',
    ));
});
