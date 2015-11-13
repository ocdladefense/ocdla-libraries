<?php

namespace Ocdla;

use Clickpdx\Core\InvalidLoginException;

class User
{	
	public $user_id;
	
	public $type;
	
	protected $userId;
	
	/**
	 * var memberId
	 *
	 * This corresponds to the member's Contact Id
	 */
	protected $memberId;
	
	protected $validUser;
	
	protected $username;
	
	protected $password;
	
	protected $firstName;
	
	protected $lastName;
	
	protected $email;
	
	/**
	 * var roles
	 *
	 * An array of roles that this user is associated with.
	 */
	protected $roles = array();
	
	protected $data;
	
	protected $sfData;
	
	public function save()
	{
		$conn=get_connection();
	  $stmt = $conn->prepare('INSERT INTO members (username,name_first,name_last,sf_Data) VALUES (:username,:name_first,:name_last,:sf_Data) ON DUPLICATE KEY UPDATE sf_Data=VALUES(sf_Data)');
	  
	  // Bind data
	  $stmt->bindValue(':username', $this->getUsername());
	  $stmt->bindValue(':name_first', $this->getFirstName());
	  $stmt->bindValue(':name_last', $this->getLastName());  			
	  $stmt->bindValue(':sf_Data', \serialize($this->getData()));
	  if($stmt->execute())
	  {
	  	$this->setUserId($conn->lastInsertId('autoId'));
	  	return $this;
	  }
	  else
	  {
	  	return false;
	  }
	}	
	public function getData()
	{
		return $this->sfData;
	}
	public static function newFromUsername($username)
	{
		return self::loadFromUsername($username);
	}
	
	
	private function setSomeValues()
	{
		if(foobar)
		{
			$user->roles = array();
			if($user->is_admin == 1) $user->roles[] = 'admin';
			if($user->is_member == 1) $user->roles[] = 'member';
		}
	
		else
		{
			$user->roles = array();
			$user->uid = 0;
			$user->name_first = 'Anonymous User';
		}
	}

	public static function newFromUid($uid)
	{
		return self::loadFromUid($uid);
	}

	private static function loadFromUid($uid)
	{
		$u=new User();
		$u->setUserId($uid);
		$stmt = db_query('SELECT autoId, id AS memberId, is_admin, is_member, name_first, name_last FROM {members} WHERE autoId = :autoId',
			array(':autoId'=>$uid),
			'pdo',
			false
		);
		if($stmt&&$stmt->rowCount())
		{
			$ret=$stmt->fetch();
			$u->setUserId($ret['autoId']);
			$u->setMemberId($ret['memberId']);
			$u->setFirstName($ret['name_first']);
			$u->setLastName($ret['name_last']);
			if($ret['is_admin']==1) $u->addRole('admin');
			if($ret['is_member']==1) $u->addRole('member');
			$u->setValidUser(true);
		}
		else
		{
			$u->setValidUser(false);
		}
		return $u;
	}
	
	private function addRole($rName)
	{
		array_unshift($this->roles,$rName);
	}
	
	public function getRoles()
	{
		return $this->roles;
	}
	
	private static function loadFromUsername($username)
	{
		$u=new User();
		$u->setUsername($username);
		$stmt = db_query('SELECT autoId, id AS memberId, name_first, name_last FROM {members} WHERE username = :username',
			array(':username'=>$username),
			'pdo',
			false
		);
		if($stmt&&$stmt->rowCount())
		{
			$ret=$stmt->fetch();
			$u->setUserId($ret['autoId']);
			$u->setMemberId($ret['memberId']);
			$u->setFirstName($ret['name_first']);
			$u->setLastName($ret['name_last']);
			$u->setValidUser(true);
		}
		else
		{
			$u->setUserId(0);
			$u->setValidUser(false);
		}
		return $u;
	}
	public function setValidUser($boolean)
	{
		$this->validUser=$boolean;
	}
	public function isValidUser()
	{
		return $this->validUser;
	}
	public function setMemberId($memberId)
	{
		$this->memberId = $memberId;
	}
	public function getMemberId()
	{
		return $this->memberId;
	}
	public function setUserId($uid)
	{
		$this->userId=$uid;
	}
	public function setFirstName($firstName)
	{
		$this->firstName=$firstName;
	}
	public function setUsername($username)
	{
		$this->username=$username;
	}
	public function setLastName($lastName)
	{
		$this->lastName=$lastName;
	}
	public function setEmail($email)
	{
		$this->email=$email;
	}
	public function __construct($params=null)
	{
		if(!isset($params)||!count($params))
		{
			return;
		}
		if(!isset($params['username']))
		{
			throw new Exception('Class User: username not given.');
		}
		if(!isset($params['password']))
		{
			throw new Exception('Class User: password not given.');
		}

		$query = new DBQuery(
			$params = array(
				"type" => "select",
				"tablenames" => array(
					0 => array(
						"name"		=>	"members",
						"op" 			=> 	"",
						"fields" 	=> 	array()
					)	
				),
				"schema" => array(
					"members",
				),
				"where" => array(
					"username='{$params['username']}'",
					"AND password='{$params['password']}'"
				)
			)
		);
		$this->data = $query->exec();
		$this->user_id = $this->data[0]["id"];
		$this->userId=$this->data[0]["id"];
		if($query->getNumRows()<1)
		{
			throw new InvalidLoginException('Class User: username and password combination not found.');
		}	
	}
	public function getUsername()
	{
		return $this->username;
	}
	public function getFirstName()
	{
		return $this->firstName;
	}
	public function getLastName()
	{
		return $this->lastName;
	}
	public function getUserId()
	{
		return $this->userId;
	}
	public function get_user_id()
	{
		if(!isset($this->user_id))
		{
			throw new Exception('Class User: user_id not set in function get_user_id().');
		}
		return $this->user_id;
	}
		
	public function __toString()
	{
		$tmp = array();
		$tmp []= "This is an instance of ". get_class($this);
		$tmp []= "Uuid is: {$this->getUserId()}.";
		$tmp []= "Member id is: {$this->getMemberId()}.";
		return implode('<br />',$tmp);
	}
}