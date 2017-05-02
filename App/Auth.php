<?php

namespace App;
use \App\Models\User;
use \App\Models\RememberedLogin;

//Authentication
class Auth {

	/**
	 * Login the user
	 *
	 * @param User $user The user model
	 * @return void
	 */
	public static function login($user, $remember_me) {
		session_regenerate_id(true);
		$_SESSION['user_id'] = $user->id;
		if($remember_me) {
			if($user->rememberLogin()){
				setcookie('remember_me', $user->remember_token, $user->expiry_timestamp, '/');
			}
		}
	}

	/**
	 * Logout the user
	 *
	 * @return void
	 */
	public static function logout() {
		$_SESSION = array();	//Unset all of the session variables

		//Delete the session cookie
		if (ini_get("session.use_cookies")) {
		    $params = session_get_cookie_params();
		    setcookie(session_name(), '', time() - 42000,
		        $params["path"], $params["domain"],
		        $params["secure"], $params["httponly"]
		    );
		}

		session_destroy();	//destroy the session
		static::forgetLogin();
	}


	/**
	 * Remember the originally requested page in the session
	 * 
	 * @return void
	 */
	public static function rememberRequestedPage() {
		$_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
	}

	/**
	 * Get the originally requested page to return to after requiring login, or default to the homepage
	 * 
	 * @return void
	 */
	public static function getReturnToPage() {
		return isset($_SESSION['return_to']) ? $_SESSION['return_to'] : '/';
	}

	/**
	 * Get the current logged in user, from the session or the remember-me cookie
	 * 
	 * @return mixed the user model or null if not logged in
	 */
	public static function getUser() {
		if(isset($_SESSION['user_id'])) {
			return User::findById($_SESSION['user_id']);
		} else {
			return static::loginFromRememberCookie();
		}
	}	


	/**
	 * Login the user from a remembered login cookie
	 * 
	 * @return mixed the user model or null if not logged in
	 */
	protected static function loginFromRememberCookie() {
		$cookie = isset($_COOKIE['remember_me']) ? $_COOKIE['remember_me'] : false;
		if($cookie) {
			$remembered_login = RememberedLogin::findByToken($cookie);
			if($remembered_login && !$remembered_login->hasExpired()) {
				$user = $remembered_login->getUser();
				static::login($user, false);
				return $user;
			}
		}
	}

	/**
	 * Forget the remembered login, if present
	 * 
	 * @return void
	 */
	protected static function forgetLogin() {
		$cookie = isset($_COOKIE['remember_me']) ? $_COOKIE['remember_me'] : false;
		if($cookie){
			$remembered_login = RememberedLogin::findByToken($cookie);
			if($remembered_login) {
				$remembered_login->delete();
			}
			setcookie('remember_me', '', time()-3600);
		}
	}

}

?>