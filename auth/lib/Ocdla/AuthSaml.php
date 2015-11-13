<?php

namespace Ocdla;


class AuthSaml extends AuthPlugin
{	

	public function provisionAccount($u,$sData)
	{
		$u->setFirstName($sData->getAttr('firstName'));
		$u->setLastName($sData->getAttr('lastName'));
		return $u->save();
	}
	
	public function __construct($conn)
	{
		parent::__construct($conn);
	}
	
	private function queryAccountByUsername($username)
	{
		$stmt = $this->db->prepare('SELECT autoid FROM members WHERE username = :username');
		$stmt->bindValue(':username', $username);
		if($stmt->execute())
		{
			$ret=$stmt->fetch();
			if(!empty($ret['autoid']))
			{
				return $ret;
			}
			else
			{
				return false;
			}
		}
		return false;
	}
	
	public function authenticate($sAttr)
	{
		$username=$sAttr->getAttr('username');
		if(empty($username))
		{
			throw new Exception('The SAML username attribute cannot be empty.');
		}
		$u=User::newFromUsername($sAttr->getAttr('username'));

		if(!$u->isValidUser())
		{
			$u=$this->provisionAccount($u,$sAttr);
		}
		// print_r($u);
		$stmt = $this->db->prepare("UPDATE my_aspnet_Sessions SET UserID=? WHERE SessionID=?");
		$stmt->execute(array($u->getUserId(), session_id()));
		if(!$stmt->rowCount())
		{
			throw new \Exception('Error when attempting to authenticate session: Could not locate session: '.session_id());
		}
		return true;
	}		
}