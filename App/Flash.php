<?php

namespace App;

//Flash notification messages
class Flash {

	const SUCCESS = 'success';
	const INFO = 'info';
	const WARNING = 'warning';

	/**
	 * Add a flash message
	 *
	 * @param string $message The message content
	 * @return void
	 */
	public static function addMessage($message, $type = 'info') {

		//Create an array in the session to store flash messages
		if(!isset($_SESSION['flash_notification'])) {
			$_SESSION['flash_notification'] = array();
		}

		//Append the message to the array
		$_SESSION['flash_notification'][] = [
			'body' => $message,
			'type' => $type
		];
	}

	/**
	 * Get all flash messages
	 *
	 * @return mixed an array with all the flash messages or null if none set
	 */
	public static function getMessages() {
		if(isset($_SESSION['flash_notification'])) {
			$messages = $_SESSION['flash_notification'];
			unset($_SESSION['flash_notification']);
			return $messages;
		}
	}

}

?>