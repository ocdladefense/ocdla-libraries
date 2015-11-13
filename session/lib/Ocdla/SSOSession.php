<?php
namespace Ocdla;


class SSOSession extends Session
{
 
 	private $samlAttributes;
 	
 	
	public function __construct(\Doctrine\DBAL\Connection $conn){
		// Instantiate new Database object
		$this->db = $conn;

		// Instantiate a standard PDO connection
		// Do this for now because Doctrine may be buggy with certain queries
		$this->conn = new PDO();
	  
		// Set handler to overide SESSION
		session_set_save_handler(
		  array($this, "_open"),
		  array($this, "_close"),
		  array($this, "_read"),
		  array($this, "_write"),
		  array($this, "_destroy"),
		  array($this, "_gc")
		);
	  
		session_name('OCDLA_SessionId');
		session_set_cookie_params(60*60*24*30,'/','.ocdla.org');
		session_start(); 
	}
	
	
	/**
	 * Read
	 */
	public function _read($id)
	{
		$sql = "SELECT * FROM my_aspnet_Sessions WHERE SessionID = :id";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":id", $id);

	  if($stmt->execute())
	  {
			$session = $stmt->fetch();
			$this->UserID = $session['UserID'];
			$this->samlAttributes = unserialize($session['Saml']);
		 	return $session['Data'];
	  }
	  else
	  {
		 return '';
	  }
	}
	
	
	public function getSamlAttributes()
	{
		return $this->samlAttributes;
	}
	
	
	public static function writeSamlData($conn, $OCDLA_SessionId, $data)
	{
	  // Create time stamp
	  $access = time();
	  
	  // Set query  
	  $stmt = $conn->prepare('UPDATE my_aspnet_Sessions SET Saml=:data WHERE SessionID=:sessionId');
	  
	  // Bind data
	  $stmt->bindValue(':sessionId', $OCDLA_SessionId);  
	  $stmt->bindValue(':data', serialize($data));
 
	  // Attempt Execution
	  // If successful
	  if($stmt->execute()){
			// Return True
			return true;
	  }
	
	  // Return False
	  return false;
	}

	protected function authenticateSession($UserID)
	{
		// No need to re-authenticate
		if($this->hasAuthenticatedSession()) {
			return true;
		}
		$stmt = $this->conn->db->prepare("UPDATE my_aspnet_Sessions SET UserID=? WHERE SessionID=?");
		$stmt->execute(array($UserID, session_id()));
		$affected_rows = $stmt->rowCount();  
		if( $affected_rows > 0 ) {
			$this->UserID = $UserID;
			return true;
		}
		return false;
	}

	public function authenticateWithPlugin(AuthPlugin $p,$username)
	{
		return $p->authenticate($username);		
	}

	public function authenticate($username = null, $password = null)
	{
		$errors = array();
		if(empty($username))
		{
			$errors[] = 'The username cannot be empty.';
			$this->processAuthenticationErrors($errors);
		}
		$stmt = $this->db->prepare('SELECT id FROM members WHERE username = :username');
		$stmt->bindValue(':username', $username);
		
		if($stmt->execute())
		{
			$result = $stmt->fetch();
			if($result['id']) {
				$log[] = $result['id'];
				$log[] = session_id();
				if($this->authenticateSession($result['id'])) {
					$log[] = 'Your id was successfully updated.';
				} else {
					$errors[] = 'Your session could not be authenticated.';
				}
			} else {
				$errors[] = 'Your record does not exist.';
			}
		}
		else
		{
			$errors[] = 'There was an error processing your login.';
		}
		$this->processAuthenticationErrors($errors);
		return true;
	}

}