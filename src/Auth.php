<?php

namespace rdx\pocketcasts;

interface Auth {

	public function getAccessToken() : ?string;

	public function getRefreshToken() : string;

	public function rememberRefreshToken(string $token) : void;

}
