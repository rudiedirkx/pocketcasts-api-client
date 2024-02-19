<?php

require __DIR__ . '/inc.bootstrap.php';

$client->logIn();

$uuid = $_GET['podcast'] ?? $_SERVER['argv'][1] ?? null;
if (!$uuid) {
	echo '{}';
	exit;
}

$episodes = $client->getEpisodes($uuid);
// $listened = array_values(array_filter($episodes, fn($x) => isset($x['bookmark'])));

echo json_encode([
	'found' => count($episodes),
	'episodes' => $episodes,
	'debug' => [
		'getEpisodesBookmarksTime' => $client->getEpisodesBookmarksTime,
		'getEpisodesEpisodesTime' => $client->getEpisodesEpisodesTime,
	],
]);
