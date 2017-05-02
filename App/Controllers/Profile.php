<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;

class Profile extends Authenticated {

	/**
	 * Before filter, called before each action method
	 *
	 * @return void
	 */
	protected function before() {
		parent::before();
		$this->user = Auth::getUser();
	}


	/**
	 * Show the profile
	 *
	 * @return void
	 */
	public function showAction() {
		View::renderTemplate('Profile/show.html',
			['user'=>$this->user]);
	}

	/**
	 * show the form for editing the profile
	 *
	 * @return void
	 */
	public function editAction() {
		View::renderTemplate('Profile/edit.html',
			['user'=>$this->user]);
	}

	/**
	 * Update the profile
	 *
	 * @return void
	 */
	public function updateAction() {

		if($this->user->updateProfile($_POST)) {
			Flash::addMessage('Your profile is updated');
			$this->redirect('/Profile/show');
		} else {
			View::renderTemplate('Profile/edit.html', [
					'user'=>$this->user
				]);
		}
	}
}

?>