<?php

namespace App\Controllers;
use \Core\View;
use \App\Models\User;
use \App\Auth;
use \App\Flash;

class Login extends \Core\Controller {

	/**
	 * Show login page
	 * 
	 * @return void
	 */
	public function newAction() {
		View::renderTemplate('Login/new.html');
	}

	/**
	 * Login in a user
	 *
	 * @return void
	 */
	public function createAction() {
		$user = User::authenticate($_POST['email'], $_POST['password']);
		$remember_me = isset($_POST['remember_me']);
		if($user) {
			Auth::login($user, $remember_me);

			//Remember login
			Flash::addMessage('Login successful', Flash::SUCCESS);
			$this->redirect(Auth::getReturnToPage());
		} else {
			Flash::addMessage('Login failed, please try again', Flash::WARNING);
			View::renderTemplate('Login/new.html', ['email'=>$_POST['email'],
													'remember_me' => $remember_me]);
		}
	}

	/**
	 * Log out a user
	 * 
	 * @return void
	 */
	public function destroyAction() {
		
		Auth::logout();
		$this->redirect('/Login/showlogout');
	}

	/**
	 * Show a "Logged out" flash message and redirect to the homepage 
	 * 
	 * @return void
	 */
	public function showLogoutAction() {
		Flash::addMessage('Logout successful', Flash::SUCCESS);
		$this->redirect('/');
	}
}

?>