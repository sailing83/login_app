<?php

namespace App;

class Token {

	protected $token;

	/**
	 * Create a new random token
	 *
	 * @return void
	 */
	public function __construct($token_value = null) {
		if($token_value) {
			$this->token = $token_value;
		} else {
			$this->token = uniqid();
		}
	}

	/**
	 * Get the token value
	 *
	 * @return string The token value
	 */
	public function getValue() {
		return $this->token;
	}

	/**
	 * Get the hased token value
	 *
	 * @return string The hashed value
	 */
	public function getHash() {
		return hash_hmac('sha256', $this->token, \App\Config::SECRET_KEY);
	}
}

?>