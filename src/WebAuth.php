<?php

namespace rdx\pocketcasts;

use Closure;

class WebAuth implements Auth {

	public function __construct(
		protected string $username,
		protected string $password,
		protected ?Closure $saveRefreshToken = null,
	) {}

	public function getAccessToken() : ?string {
		return null;
	}

	public function getRefreshToken() : string {
		// https://api.pocketcasts.com/user/login_pocket_casts
	}

	public function rememberRefreshToken(string $token) : void {
		call_user_func($this->saveRefreshToken, $token);
	}

}
