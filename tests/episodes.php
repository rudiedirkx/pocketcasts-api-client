<?php

require __DIR__ . '/inc.bootstrap.php';

$client->logIn();

$uuid = $_GET['podcast'] ?? null;
if (!$uuid) {
	echo '{}';
	exit;
}

// $client->getPodcasts();

$episodes = $client->getEpisodes($uuid);
// var_dump(count($episodes));
// print_r($episodes);

echo json_encode([
	'found' => count($episodes),
	'episodes' => $episodes,
	'debug' => [
		'getEpisodesBookmarksTime' => $client->getEpisodesBookmarksTime,
		'getEpisodesEpisodesTime' => $client->getEpisodesEpisodesTime,
	],
]);
