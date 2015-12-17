<?php

namespace Ocdla;

/**
 * Class to represent a password
 *
 * Use this class to execute actions related to resetting a
 *  user's password.  This usually involves generating a password reset link
 * saving data related to the password reset link and, ultimately,
 * saving the user's new password.
 *
 */
class PasswordResetAction
{
	private static $resetTable = 'members_password_reset';
	
	private $data = array();
	
	public static function newFromToken($token)
	{
		$self = new PasswordResetAction();
		return $self->loadFromToken($token);
	}
	
	public function loadFromToken($token)
	{
		$this->data = db_query("SELECT m.id, m.username, token, invalid FROM {".self::$resetTable."} p JOIN {members} m ON(m.username=p.username) WHERE token=:token",
			array('token'=>$token),'pdo')->fetch();
		return $this;
	}

	public function setUsername($username)
	{
		$this->data['username'] = $username;
	}
	
	private function getUsername()
	{
		return $this->data['username'];
	}
	
	private function getToken()
	{
		return $this->data['token'];
	}

	private function getMemberId()
	{
		return $this->data['id'];
	}
	
	public function hasValidToken()
	{
		return !(
			empty($this->getToken())
				||
			$this->data['invalid']==1
		);
	}
	
	public function createResetEntry()
	{
		$token = $this->generateToken();
		$insert = db_query("INSERT INTO {".self::$resetTable."} (username,token) VALUES(:username,:token)",
			array('username'=>$this->getUsername(), 'token'=>$token),'pdo');
		return $token;
	}
	
	public function generateToken()
	{
		$bytes = openssl_random_pseudo_bytes(8);
		return bin2hex($bytes);
	}
	
	public function invalidateAll()
	{
		db_query("UPDATE {".self::$resetTable."} SET invalid=1 WHERE username=:username",
			array('username'=>$this->getUsername()),'pdo');
	}
	
	public function invalidate()
	{
		db_query("UPDATE {".self::$resetTable."} SET invalid=1 WHERE token=:token",
			array('token'=>$this->getToken()),'pdo');
	}
	
	public function resetPasswordAllDomains($pwd)
	{
		$this->resetOcdlaPassword($pwd);
		$this->resetLodPassword($pwd);
	}
	
	private function resetOcdlaPassword($pwd)
	{
		db_query("UPDATE {members} SET password=MD5(:password) WHERE id=:memberId",
			array('password'=> $pwd,
				'memberId' => $this->getMemberId()),'pdo');
	}
	
	private function resetLodPassword($pwd)
	{
		db_query("UPDATE {lodusers} SET user_password=CONCAT(:prefix,MD5(:password)) WHERE user_name=:username",
			array(
				'prefix' 			=> ':A:',
				'password'		=> $pwd,
				'username' 		=> ucfirst($this->getUsername())
			)
		,'pdo',true);
	}
	
}