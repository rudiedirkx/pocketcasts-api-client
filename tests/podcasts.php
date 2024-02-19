<?php

require __DIR__ . '/inc.bootstrap.php';

$client->logIn();

$podcasts = $client->getPodcasts();
$names = array_column($podcasts, 'title', 'uuid');
print_r($names);
