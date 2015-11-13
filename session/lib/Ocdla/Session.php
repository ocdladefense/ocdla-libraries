<?php
namespace Ocdla;

use Clickpdx\Core\Database\Database as Database;


class Session
{
 
  /**
   * Db Object
   */
	protected $db;
	protected $conn;
 	protected $sessionid;
 	protected $UserID = null;
 	
 	protected $log = array();
 	protected $errors = array();
 
	public function __construct($frameworkObject=null)
	{
		// Instantiate new Database object
		if(get_class($frameworkObject)=='Doctrine\DBAL\Connection')
		{
			$this->db = $frameworkObject;
		}
		else if (isset($frameworkObject))
		{
			$this->db = $frameworkObject->get('database_connection');
		}
		else
		{
			$this->db = get_connection();
		}

		// Instantiate a standard PDO connection
		// Do this for now because Doctrine may be buggy with certain queries
		$this->conn = new \Ocdla\PDO();
	  
		// Set handler to overide SESSION
		session_set_save_handler(
		  array($this, "_open"),
		  array($this, "_close"),
		  array($this, "_read"),
		  array($this, "_write"),
		  array($this, "_destroy"),
		  array($this, "_gc")
		);
	  
		session_name(getSessionName());
		session_set_cookie_params(60*60*24*30,'/','.ocdla.org');
		session_start(); 
	}
	
	/**
	 * Open
	 */
	public function _open()
	{
	  if($this->db) return true;
	  else return false;
	}

	
	/**
	 * Close
	 */
	public function _close()
	{
	  if($this->db->close()) return true;
	  else return false;
	}

	/**
	 * Read
	 */
	public function _read($id)
	{
		$sql = Database::finalizeSql("SELECT * FROM {my_aspnet_Sessions} WHERE SessionID = :id");
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":id", $id);

	  // Return the session data,
	  // If successful
	  if($stmt->execute())
	  {
			// Save returned row
			$session = $stmt->fetch();
			$this->UserID = $session['UserID'];

			// Return the data
			return $session['Data'];
	  }
	  else return '';
	}

	/**
	 * Write
	 */
	public function _write($id, $data)
	{
	  // Create time stamp
	  $access = time();
	  
	  $sql = Database::finalizeSql("INSERT INTO {my_aspnet_Sessions} (SessionID, Created, Expires, UserID, Data) VALUES (:sessionId, :created, :expires, :userid, :data) ON DUPLICATE KEY UPDATE Data=VALUES(Data)");
	  // Set query  
	  $stmt = $this->db->prepare($sql);
	  
	  // Bind data
	  $stmt->bindValue(':sessionId', $id);
	  $stmt->bindValue(':userid', $this->UserID);	  
	  $stmt->bindValue(':created', OCDLA_SESSION_CREATED);
	  $stmt->bindValue(':expires', OCDLA_SESSION_EXPIRES);
	  $stmt->bindValue(':data', $data);
 
	  // Attempt Execution
	  // If successful
	  if($stmt->execute()) return true;
	  else return false;
	}
 
	/**
	 * Destroy
	 */
	public function _destroy($id)
	{
	  // Set query
	  if(empty($id)) return false;
	  $sql = Database::finalizeSql("DELETE FROM {my_aspnet_Sessions} WHERE SessionID = :id");
	  $stmt = $this->db->prepare($sql);
	  
	  // Bind data
	  $stmt->bindValue(':id', $id);
	  
	  // Attempt execution
	  // If successful
	  if($stmt->execute()) return true;
	  else return false;
	} 
 
	public function _gc($max)
	{
	  // Calculate what is to be deemed old
	  $old = time() - $max;
 
 		$sql = Database::finalizeSql("DELETE FROM {my_aspnet_Sessions} WHERE Created < :old");
 		
	  // Set query
	  $this->db->query($sql);
	  
	  // Bind data
	  $this->db->bind(':old', $old);
	  
	  // Attempt execution
	  if($this->db->execute()) return true;
		else return false;
	}
	
	public function getUserID()
	{
		return $this->UserID;
	}
	
	
	public function getFields()
	{
		$sql = Database::finalizeSql('SELECT * FROM {my_aspnet_Sessions} sess LEFT JOIN {members} m ON(m.autoId=sess.UserID) WHERE SessionID = :sessionid');
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':sessionid', $this->getSessionId());
		$stmt->execute();
		return $stmt->fetch();
	}
	
	
	public function authenticate($username = null, $password)
	{
		$errors = array();
		if(empty($username)||empty($password))
		{
			$errors[] = 'None of these credentials should be empty.';
			$this->processAuthenticationErrors($errors);
		}
		$sql = Database::finalizeSql('SELECT autoId FROM {members} WHERE username = :username AND password = md5(:password)');
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':username', $username);
		$stmt->bindValue(':password', $password);
		
		if( $stmt->execute() ) {
			$result = $stmt->fetch();
			if($result['autoId']) {
				$log[] = $result['autoId'];
				$log[] = session_id();
				if($this->authenticateSession($result['autoId'])) {
					$log[] = 'Your id was successfully updated.';
				} else {
					$errors[] = 'Your session could not be authenticated.';
				}
			} else {
				$errors[] = 'Your record does not exist.';
			}
		} else {
			$errors[] = 'There was an error processing your login.';
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
		// No need to re-authenticate
		if($this->hasAuthenticatedSession()) return true;
		$query = Database::finalizeSql("UPDATE {my_aspnet_Sessions} SET UserID=? WHERE SessionID=?");
		$stmt = $this->conn->db->prepare($query);
		$stmt->execute(array($UserID, session_id()));
		$affected_rows = $stmt->rowCount();  
		if($affected_rows > 0)
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
		if(!$this->UserID) return false;
		$sql = Database::finalizeSql("SELECT * FROM {my_aspnet_Sessions} WHERE SessionID=? AND UserID=?");
		$stmt = $this->conn->db->prepare($sql);
		$stmt->execute(array(session_id(), $this->UserID));
		if(1 === $stmt->rowCount()) return true;
	}
}