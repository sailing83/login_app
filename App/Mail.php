<?php

namespace App;

use Mailgun\Mailgun;

/**
 * Mail
 * 
 */
class Mail {

	/**
	 * Send a message
	 * 
	 * @param string $to Recipient
	 * @param string $subject Subject
	 * @param string $text Text-only content of the message
	 * @param string $html HTML content of the message
	 *
	 * @return mixed
	 */
	public static function send($to, $subject, $text, $html) {
		# First, instantiate the SDK with your API credentials
		$mg = Mailgun::create(Config::MAILGUN_API_KEY);

		# Now, compose and send your message.
		$mg->messages()->send(Config::MAILGUN_DOMAIN, [
		  'from'    => 'yf_1983@hotmail.com', 
		  'to'      => $to, 
		  'subject' => $subject, 
		  'text'    => $text,
		  'html'	=> $html
		]);
	}

	/**
	 * Send the password reset link to the controller
	 *
	 * @return void
	 */
	public function requestReset() {

	}
}

?>