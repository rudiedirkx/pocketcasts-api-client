<?php

use rdx\pocketcasts\Client;
use rdx\pocketcasts\TokenAuth;

header('Content-type: text/plain; charset=utf-8');
ini_set('html_errors', 0);

require __DIR__ . '/env.php';
require __DIR__ . '/../vendor/autoload.php';

if (!in_array($_SERVER['REMOTE_ADDR'], APP_LOCAL_IPS)) {
	echo '{}';
	exit;
}

header('Access-Control-Allow-Private-Network: true');
header('Access-Control-Allow-Origin: *');

$atFile = __DIR__ . '/.pcAccessToken';
$accessToken = file_exists($atFile) && filemtime($atFile) > strtotime('-30 minutes') ? file_get_contents($atFile) : null;
// var_dump($accessToken);
$client = new Client(new TokenAuth(PC_REFRESH_TOKEN, $accessToken), function(string $accessToken, int $expiresIn) use ($atFile) {
	var_dump(file_put_contents($atFile, $accessToken));
});
// print_r($client);
