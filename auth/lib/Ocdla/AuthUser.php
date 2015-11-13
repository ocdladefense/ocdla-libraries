<?php

namespace Ocdla;


class AuthUser extends AuthPlugin
{	

	public function provisionUser($userObj)
	{
		$userObj->create();
	}
	public function authenticate($username=null,$password=null)
	{
		$errors = array();
		if(empty($username)||empty($password))
		{
			$errors[] = 'None of these credentials should be empty.';
			// $this->processAuthenticationErrors($errors);
		}
		$stmt = $this->db->prepare('SELECT id FROM members WHERE username = :username AND password = md5(:password)');
		$stmt->bindValue(':username', $username);
		$stmt->bindValue(':password', $password);
		
		if( $stmt->execute() )
		{
			$result = $stmt->fetch();
			if($result['id'])
			{
				$log[] = $result['id'];
				$log[] = session_id();
				if($this->authenticateSession($result['id']))
				{
					$log[] = 'Your id was successfully updated.';
				}
				else
				{
					$errors[] = 'Your session could not be authenticated.';
				}
			}
			else
			{
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
	
	private function authenticateSession($UserID)
	{
		// No need to re-authenticate
		if($this->hasAuthenticatedSession())
		{
			return true;
		}
		$query = "UPDATE my_aspnet_Sessions SET UserID={$UserID} WHERE SessionID=".session_id();
		$stmt = $this->conn->db->prepare("UPDATE my_aspnet_Sessions SET UserID=? WHERE SessionID=?");
		$stmt->execute(array($UserID, session_id()));
		$affected_rows = $stmt->rowCount();  
		if( $affected_rows > 0 )
		{
			$this->UserID = $UserID;
			return true;
		}
		return false;
	}
}