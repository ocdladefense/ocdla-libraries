<?php
namespace Ocdla;

use Clickpdx\Core\Database\Database as Database;


class PhpSession
{
 
  /**
   * Db Object
   */
	protected $db;
	
 	protected $sessionid;
 	
 	protected $UserID = null;
 	
 	protected $log = array();
 	
 	protected $errors = array();
 
 
 	/**
 	 * Wrapper for PHP's built-in session handling/file functionality.
 	 *
 	 */
	public function __construct($params = null) {
		
	}
	
	

	
	public function getUserID()
	{
		return $this->UserID;
	}
	

	
	
	public function authenticate($username = null, $password)
	{
		$errors = array();
		
		if(empty($username)||empty($password)) {
			$errors[] = 'None of these credentials should be empty.';
		}
		
		
		$this->processAuthenticationErrors($errors);
		return true;
	}




	protected function processAuthenticationErrors($errors)
	{
		if(count($errors))
		{
			throw new \Exception(implode("\n",$errors));
		}
	}




	public function setAppStatus($appName,$status)
	{
		if (!isset($_SESSION['sessionStatus'])) $_SESSION['sessionStatus'] = array();
		$_SESSION['sessionStatus'][$appName] = $status;
	}
	
	public function hasOcdlaSession()
	{
		return $this->hasAppSession('ocdla');
	}
	
	public function hasLodSession()
	{
		return $this->hasAppSession('lodProd');
	}
	
	public function hasLodTestSession()
	{
		return $this->hasAppSession('lodTest');
	}
	
	public function hasIncompleteSession()
	{
		return $this->hasLodSession()&&$this->hasOcdlaSession();
	}
	
	public function hasAppSession($appName)
	{
		return $_SESSION['sessionStatus'][$appName];
	}
	
	protected function authenticateSession($UserID)
	{
		$SESSION_DATA_WAS_UPDATED = true;
		
		// No need to re-authenticate
		if($this->hasAuthenticatedSession()) return true;
	
		if($SESSION_DATA_WAS_UPDATED)
		{
			$this->UserID = $UserID;
			return true;
		}
		
		return false;
	}
	
	
	
	public function getSessionId()
	{
		return session_id();
	}
	
	
	
	
	public function hasAuthenticatedSession()
	{
		
		return $this->UserID != null;

	}
}