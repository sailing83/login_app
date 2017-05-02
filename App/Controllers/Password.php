<?php

namespace App\Controllers;
use \Core\View;
use \App\Models\User;

class Password extends \Core\Controller {
	
	/**
	 * Show the forgotten password page
	 *
	 * @return void
	 */
	public function forgotAction() {
		View::renderTemplate('Password/forgot.html');
	}

	/**
	 * Send the password reset link to the supplied email
	 *
	 * @return void
	 */
	public function requestResetAction() {
		User::sendPasswordReset($_POST['email']);
		View::renderTemplate('Password/reset_requested.html');
	}

	/**
	 * Show the reset password form
	 *
	 * @return void
	 */
	public function resetAction() {
		$token = $this->route_params['token'];

		$user = User::findByPasswordReset($token);
		if($user) {
			View::renderTemplate('Password/reset.html', ['user'=>$user, 'token'=>$token]);
		} else {
			exit("Password reset token invalid or has expired");
		}
	}

	/**
	 * Reset user's password
	 *
	 * @return void
	 */
	public function resetPasswordAction() {
		$token = $_POST['token'];

		$user = User::findByPasswordReset($token);
		if($user) {
			//Reset password
			if($user->resetPassword($_POST['password'], $_POST['password_confirmation'])) {
				View::renderTemplate('Password/reset_success.html');
			} else {
				View::renderTemplate('Password/reset.html', [
						'token' => $token,
						'user' => $user
					]);
			}
			
		} else {
			exit("Password reset token invalid or has expired");
		}
	}

}

?>