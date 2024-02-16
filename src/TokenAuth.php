<?php

namespace rdx\pocketcasts;

class TokenAuth implements Auth {

	public function __construct(
		protected string $refreshToken,
		protected ?string $accessToken = null,
	) {}

	public function getAccessToken() : ?string {
		return $this->accessToken;
	}

	public function getRefreshToken() : string {
		return $this->refreshToken;
	}

	public function rememberRefreshToken(string $token) : void {
		// Do nothing
	}

}
