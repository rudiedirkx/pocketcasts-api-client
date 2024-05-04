<?php

require __DIR__ . '/inc.bootstrap.php';

$client->logIn();

$dcFile = __DIR__ . '/.decorrespondent';
if (file_exists($dcFile) && filemtime($dcFile) > strtotime('-30 minutes') && ($json = file_get_contents($dcFile))) {
	$episodes = json_decode($json, true);
}
else {
	$episodes = $client->getEpisodes('188d5a90-2a19-0132-ba6c-5f4c86fd3263');
	$episodes = array_slice($episodes, 0, 100);
	file_put_contents($dcFile, json_encode($episodes));
}

$episodes = array_map(function(array $info) {
	return [
		'title' => $info['episode']['title'],
		'duration' => $info['episode']['duration'] ?? 0,
		'published' => date('Y-m-d', strtotime($info['episode']['published'])),
		'show_notes' => $info['episode']['show_notes'],
		'bookmarked' => isset($info['bookmark']),
	];
}, $episodes);

echo json_encode([
	'episodes' => $episodes,
	'debug' => [
		'getEpisodesBookmarksTime' => $client->getEpisodesBookmarksTime,
		'getEpisodesEpisodesTime' => $client->getEpisodesEpisodesTime,
	],
]);
