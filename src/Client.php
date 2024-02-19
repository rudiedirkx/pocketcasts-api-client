<?php

namespace rdx\pocketcasts;

use Closure;

class Client {

	protected ?string $accessToken = null;
	protected ?string $accountUuid = null;
	protected ?string $accountEmail = null;

	public float $getPodcastsTime = 0;
	public float $getEpisodesBookmarksTime = 0;
	public float $getEpisodesEpisodesTime = 0;

	public function __construct(
		protected Auth $auth,
		protected ?Closure $saveAccessToken = null,
	) {}

	public function logIn() : bool {
		$this->accessToken = $this->auth->getAccessToken();

		if ($this->accessToken) {
			return true;
		}

		$refreshToken = $this->auth->getRefreshToken();
		$this->auth->rememberRefreshToken($refreshToken);

		$json = `curl -s 'https://api.pocketcasts.com/user/token' -H 'authority: api.pocketcasts.com' -H 'accept: */*' -H 'accept-language: en-CA,en;q=0.9' -H 'cache-control: no-cache' -H 'content-type: application/json' -H 'origin: https://play.pocketcasts.com' -H 'pragma: no-cache' -H 'referer: https://play.pocketcasts.com/' -H 'sec-ch-ua: "Not A(Brand";v="99", "Google Chrome";v="121", "Chromium";v="121"' -H 'sec-ch-ua-mobile: ?0' -H 'sec-ch-ua-platform: "Windows"' -H 'sec-fetch-dest: empty' -H 'sec-fetch-mode: cors' -H 'sec-fetch-site: same-site' -H 'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36' --data-raw '{"grantType":"refresh_token","refreshToken":"$refreshToken"}' --compressed`;
// echo $json;
		$data = json_decode($json, true);
// print_r($data);

		if (isset($data['accessToken'])) {
			$this->accessToken = $data['accessToken'];
			$this->accountUuid = $data['uuid'];
			$this->accountEmail = $data['email'];

			$this->rememberAccessToken($this->accessToken, $data['expiresIn'] ?? null);

			return true;
		}

		return false;
	}

	public function getPodcasts() : array {
		$accessToken = $this->accessToken;

		$t = microtime(1);
		$json = `curl -s 'https://api.pocketcasts.com/user/podcast/list' -H 'authority: api.pocketcasts.com' -H 'accept: */*' -H 'accept-language: en-CA,en;q=0.9' -H 'authorization: Bearer $accessToken' -H 'cache-control: no-cache' -H 'content-type: application/json' -H 'origin: https://play.pocketcasts.com' -H 'pragma: no-cache' -H 'referer: https://play.pocketcasts.com/' -H 'sec-ch-ua: "Not A(Brand";v="99", "Google Chrome";v="121", "Chromium";v="121"' -H 'sec-ch-ua-mobile: ?0' -H 'sec-ch-ua-platform: "Windows"' -H 'sec-fetch-dest: empty' -H 'sec-fetch-mode: cors' -H 'sec-fetch-site: same-site' -H 'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36' --data-raw '{"v":1}' --compressed`;
		$data = json_decode($json, true);
		$this->getPodcastsTime = microtime(1) - $t;

		return $data['podcasts'];
	}

	public function getEpisodes(string $podcastUuid) : array {
		$accessToken = $this->accessToken;

		$t = microtime(1);
		$json = `curl -s 'https://api.pocketcasts.com/user/podcast/episodes/bookmarks' -H 'authority: api.pocketcasts.com' -H 'accept: */*' -H 'accept-language: en-CA,en;q=0.9' -H 'authorization: Bearer $accessToken' -H 'cache-control: no-cache' -H 'content-type: application/json' -H 'origin: https://play.pocketcasts.com' -H 'pragma: no-cache' -H 'referer: https://play.pocketcasts.com/' -H 'sec-ch-ua: "Not A(Brand";v="99", "Google Chrome";v="121", "Chromium";v="121"' -H 'sec-ch-ua-mobile: ?0' -H 'sec-ch-ua-platform: "Windows"' -H 'sec-fetch-dest: empty' -H 'sec-fetch-mode: cors' -H 'sec-fetch-site: same-site' -H 'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36' --data-raw '{"uuid":"$podcastUuid"}' --compressed`;
		$bookmarks = json_decode($json, true);
		$this->getEpisodesBookmarksTime = microtime(1) - $t;

		$t = microtime(1);
		$json = `curl -s 'https://podcast-api.pocketcasts.com/mobile/show_notes/full/$podcastUuid' --location -H 'authority: podcast-api.pocketcasts.com' -H 'accept: */*' -H 'accept-language: en-CA,en;q=0.9' -H 'cache-control: no-cache' -H 'origin: https://play.pocketcasts.com' -H 'pragma: no-cache' -H 'referer: https://play.pocketcasts.com/' -H 'sec-ch-ua: "Not A(Brand";v="99", "Google Chrome";v="121", "Chromium";v="121"' -H 'sec-ch-ua-mobile: ?0' -H 'sec-ch-ua-platform: "Windows"' -H 'sec-fetch-dest: empty' -H 'sec-fetch-mode: cors' -H 'sec-fetch-site: same-site' -H 'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36' --compressed`;
		$episodes = json_decode($json, true);
		$this->getEpisodesEpisodesTime = microtime(1) - $t;

		$merged = [];
		foreach ($episodes['podcast']['episodes'] as $episode) {
			$merged[ $episode['uuid'] ]['episode'] = $episode;
		}
		foreach ($bookmarks['episodes'] as $bookmark) {
			if (isset($merged[ $bookmark['uuid'] ])) {
				$merged[ $bookmark['uuid'] ]['bookmark'] = $bookmark;
			}
		}

		usort($merged, fn(array $a, array $b) => $b['episode']['published'] <=> $a['episode']['published']);

		return $merged;
	}

	protected function rememberAccessToken(string $token, ?int $expiresIn = null) : void {
		if ($this->saveAccessToken) {
			call_user_func($this->saveAccessToken, $token, $expiresIn);
		}
	}

}
