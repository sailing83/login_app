<?php

namespace App\Models;

use PDO;
use \App\Token;
use \App\Flash;
use \App\Mail;
use \Core\View;

class User extends \Core\Model {

	public $errors = array();
	public $remember_token;
	public $expiry_timestamp;

	/**
	 * Class constructor
	 *
	 * @param array $data Initial property values
	 * @return void
	 */
	public function __construct($data = []) {
		foreach($data as $key=>$value) {
			$this->$key = $value;
		}
	}

	/**
	 * Save the user model with the current property
	 *
	 * @return void
	 */
	public function save() {

		$this->validate();

		if(empty($this->errors)) {
			$password_hash = password_hash($this->password, PASSWORD_DEFAULT);

			$token = new Token();
			$hashed_token = $token->getHash();
			$this->activation_token = $token->getValue();

			$sql = "INSERT INTO users (name, email, password_hash, activation_hash)
					VALUES (:name, :email, :password_hash, :activation_hash)";
			$db = static::getDB();
			$stmt = $db->prepare($sql);

			$stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
			$stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
			$stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
			$stmt->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);
			return $stmt->execute();
		} else {
			return false;
		}

		
	}

	/**
	 * Validate current property values, adding validation error messages to the errors array property
	 *
	 * @ return void
	 */ 
	public function validate() {
		if (trim($this->name) == '') {
           $this->errors[] = 'Name is required';
       }

       // email address
       if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
           $this->errors[] = 'Invalid email';
       }
       if(isset($this->id)) {
       		if(static::emailExists($this->email, $this->id)) {
	       		$this->errors[] = "The email address already taken";
	        }
       } else {
       		if(static::emailExists($this->email, 0)) {
	       		$this->errors[] = "The email address already taken";
	        }
       }
       
       if(isset($this->password)) {
       		// Password
	        if ($this->password != $this->password_confirmation) {
	           $this->errors[] = 'Password must match confirmation';
	        }

	        if (strlen($this->password) < 6) {
	           $this->errors[] = 'Please enter at least 6 characters for the password';
	        }

	        if (preg_match('/.*[a-z]+.*/i', $this->password) == 0) {
	           $this->errors[] = 'Password needs at least one letter';
	        }

	        if (preg_match('/.*\d+.*/i', $this->password) == 0) {
	           $this->errors[] = 'Password needs at least one number';
	       	}
       }
       
	}

	/**
	 * Check if a user record already exists with the specified email
	 *
	 * @param string $email email address to search for
	 * @param string $ignored_id Return false anyway if the record found has this ID
	 * @return boolean True if a record already exists with the specified email, false otherwise
	 */
	public static function emailExists($email, $ignored_id = null) {
		$user = static::findByEmail($email);
		if($user) {
			if($user->id != $ignored_id) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Find a user model by id
	 * 
	 * @param string $email email address to search for
	 * @return mixed user object if found, false otherwise
	 */
	public static function findById($id) {
		$sql = 'SELECT * FROM users 
				WHERE id = :id';
		$db = static::getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
		$stmt->execute();
		return $stmt->fetch();
	}

	/**
	 * Find a user model by email address
	 * 
	 * @param string $email email address to search for
	 * @return mixed user object if found, false otherwise
	 */
	public static function findByEmail($email) {
		$sql = 'SELECT * FROM users 
				WHERE email = :email';
		$db = static::getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindParam(':email', $email, PDO::PARAM_STR);
		$stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
		$stmt->execute();
		return $stmt->fetch();
	}


	/**
	 * Authenticate a user by email and password
	 * 
	 * @param string $email email address
	 * @param string $password password
	 *
	 * @return mixed If email and password are authenticated successfully, returns user object, false otherwise.
	 */
	public static function authenticate($email, $password) {
		$user = static::findByEmail($email);
		if($user && $user->is_active) {
			if(password_verify($password, $user->password_hash)) {
				return $user;
			}
		}
		return false;
	}

	/**
	 * Remember the login by inserting a new unique token into the remember_logins table
	 *  
	 * @return boolean True if the login was remembered successfully, false otherwise
	 */
	public function rememberLogin() {
		$token = new Token();
		$hashed_token = $token->getHash();

		$this->remember_token = $token->getValue();

		$this->expiry_timestamp = time() + 60*60*24*30;	//expiry date, 30 days from now
		$sql = "INSERT INTO remembered_logins (token_hash, user_id, expires_at)
					VALUES (:token_hash, :user_id, :expires_at)";
			$db = static::getDB();
			$stmt = $db->prepare($sql);

			$stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
			$stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);
			$stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $this->expiry_timestamp), PDO::PARAM_STR);
			return $stmt->execute();
	}

	/**
	 * Update the user's profile
	 *
	 * @param array $data Data from the edit profile form
	 * @return boolean True if the data is updated, false otherwise
	 */
	public function updateProfile($data) {
		$this->name = $data['name'];
		$this->email = $data['email'];
		if($data['password'] != '') {
			$this->password = $data['password'];
			$this->password_confirmation = $data['password_confirmation'];
		}
		$this->validate();
		if(empty($this->errors)) {
			$sql = "UPDATE users 
					SET name = :name,
						email = :email";
			if(isset($this->password)) {
				$sql .= ",password_hash = :password_hash";
			}
						
			$sql .=	" WHERE id = :id";
			$db = static::getDB();
			$stmt = $db->prepare($sql);

			$stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
			$stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
			$stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
			if(isset($this->password)) {
				$password_hash = password_hash($this->password, PASSWORD_DEFAULT);
				$stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
			}
			return $stmt->execute();
		} else {
			return false;
		}
	}

	/**
	 * Send password reset instructions to the user specified
	 *
	 * @param string $email The email address
	 * @return void
	 */
	public static function sendPasswordReset($email) {
		$user = static::findByEmail($email);
		if($user) {
			if($user->startPasswordReset()) {
				//Send email here...
				$user->sendPasswordResetEmail();
			}
		}
	}

	/**
	 * Start the password reset process by generating a new token and expiry date
	 *
	 * @return void
	 */
	protected function startPasswordReset() {

		$token = new Token();
		$hashed_token = $token->getHash();
		$this->password_reset_token = $token->getValue();
		$expiry_timestamp = time() + 60*60*2;	//2 hours

		$sql = "UPDATE users
				SET password_reset_hash = :password_reset_hash,
					password_reset_expiry = :password_reset_expiry
				WHERE id = :id";
		$db = static::getDB();
		$stmt = $db->prepare($sql);

		$stmt->bindValue(':password_reset_hash', $hashed_token, PDO::PARAM_STR);
		$stmt->bindValue(':password_reset_expiry', date('Y-m-d H:i:s', $expiry_timestamp), PDO::PARAM_STR);
		$stmt->bindValue(":id", $this->id, PDO::PARAM_INT);
		return $stmt->execute();
	}

	/**
	 * Send password reset instructions in an email to the user
	 *
	 * @return void
	 */
	protected function sendPasswordResetEmail() {
		$url = "http://{$_SERVER['HTTP_HOST']}/password/reset/{$this->password_reset_token}";
		$text = View::getTemplate('Password/reset_email.txt', ['url' => $url]);
		$html = View::getTemplate('Password/reset_email.html', ['url' => $url]);
		Mail::send($this->email, 'Password reset', $text, $html);
	}

	/**
	 * Find a user model by password reset token and expiry
	 * 
	 * @param string $token Password reset token sent to user
	 * @return mixed User object if found and the token hasn't expired, null otherwise
	 */
	public static function findByPasswordReset($token) {
		$token = new token($token);
		$hashed_token = $token->getHash();

		$sql = "SELECT * FROM users
				WHERE password_reset_hash = :token_hash";
		$db = static::getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
		$stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
		$stmt->execute();
		$user = $stmt->fetch();
		if($user) {
			//Check password reset token hasn't expired
			if(strtotime($user->password_reset_expiry) > time()) {
				return $user;
			}
		}
	}

	/**
	 * Reset the password
	 * 
	 * @param string $password The new password
	 * @return boolean True if the password is reset successfully, false otherwise
	 */	 
	public function resetPassword($password, $password_confirmation) {
		$this->password = $password;
		$this->password_confirmation = $password_confirmation;
		$this->validate();
		
		if(empty($this->errors)) {
			$password_hash = password_hash($this->password, PASSWORD_DEFAULT);
			$sql = "UPDATE users
					SET password_hash = :password_hash,
						password_reset_hash = NULL,
						password_reset_expiry = NULL
					WHERE id=:id";
			$db = static::getDB();
			$stmt = $db->prepare($sql);

			$stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
			$stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
			return $stmt->execute();
		} else {
			return false;
		}
	}

	/**
	 * Send an email to the user containing the activation link
	 *
	 * @return void
	 */
	public function sendActivationEmail() {
		$url = "http://{$_SERVER['HTTP_HOST']}/signup/activate/{$this->activation_token}";
		$text = View::getTemplate('Signup/activation_email.txt', ['url' => $url]);
		$html = View::getTemplate('Signup/activation_email.html', ['url' => $url]);
		Mail::send($this->email, 'Account activation', $text, $html);
	}

	/**
	 * Activate the user account with the specified activation token
	 *
	 * @param string $value Activation token from the URL
	 * @return void
	 */
	public static function activate($value) {
		$token = new Token($value);
		$hashed_token = $token->getHash();

		$sql = "UPDATE users 
				SET is_active = 1, 
					activation_hash = null
				WHERE activation_hash = :activation_hash";
		$db = static::getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);
		$stmt->execute();
	}
}

?>