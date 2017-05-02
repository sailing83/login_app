<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;

//Items controllers (example)
class Items extends Authenticated {

	
	public function indexAction() {

		View::renderTemplate('Items/index.html');
	}

	public function newAction() {
		echo 'new';
	}
}



?>