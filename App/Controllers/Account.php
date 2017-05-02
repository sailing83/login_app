<?php

namespace App\Controllers;

use \App\Models\User;

/**
 * Account controller
 *
 */
class Account extends \Core\Controller {

	public function validateEmailAction() {
		$ignore_id = isset($_GET['ignore_id']) ? $_GET['ignore_id'] : 0;
		$is_valid = !User::emailExists($_GET['email'], $ignore_id);
		header('Content-Type: application/json');
		echo json_encode($is_valid);
	}
}

?>