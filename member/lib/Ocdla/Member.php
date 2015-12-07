<?php
/**
 * This is file-level documentation - Members.php
 *
 * Here is a full description of what this file does
 *
 *
 * 
 */
 
/**
 * Represents an individual OCDLA member
 *
 * 
 *
 */
namespace Ocdla;

class Member
{
	private $data;
	
	/**
	 * Constructor for Member
	 *
	 * Instantiate an OCDLA member with data from
	 * the `members` table.
	 *
	 * @param int $memberId The `members.id` of the Member to load.
	 *
	 * @return void
	 */
	public function __construct($memberId)
	{
		if (!isset($memberId)) throw new \Exception('Invalid retrieval of member record in '.__FUNCTION__);	
		
		$stmt = db_query("SELECT m.*, ci.value AS email FROM {members} m LEFT JOIN {member_contact_info} ci ON(ci.contact_id=m.id) WHERE m.id=:memberId AND type='email'",array('memberId'=>$memberId),'pdo');
		
		$this->data = $stmt->fetch();
	}
	
	public static function newFromUsername($username)
	{
		$id = db_query("SELECT m.id AS memberId FROM {members} m WHERE username=:username",array('username'=>$username),'pdo')->fetch();
		return new Member($id['memberId']);
	}

	
	private function setMemberInfo()
	{
		// $stmt = db_query('SELECT {members} m LEFT JOIN {member_contact_info} ci ON(ci. WHERE memberid=:memberId',array('memberId'=>$memberId),'pdo');
		// $results = $stmt->fetchAll();
		$this->userInfo = array(
			'memberId' => 25060,
			'email'			=> 'jbernal.web.dev@gmail.com'
		);
	}
	
	public function getUsername()
	{
		return $this->data['username'];
	}
	
	public function setPassword($plainTextPassword)
	{
		$this->password = md5($plainTextPassword);
	}

	
	
	
	public function savePassword($password)
	{
		// Update members set password=md5($password);
	}	
	
	private function saveOcdlaPassword()
	{
	
	}
	
	private function saveLodPassword()
	{
	
	}
	
	private function saveLodTestPassword()
	{
	
	}
	
	public function getMemberId()
	{
		return $this->data['id'];
	}
	
	public function getUserId()
	{
		return $this->data['autoId'];
	}
	
	public function getUserEmail()
	{
		return $this->data['email'];
	}
	
	public function getUserFirstLastName()
	{
		return $this->data['name_first'] . ' ' .$this->data['name_last'];
	}

	public function hasValidEmail()
	{
		if(!empty($this->data['value'])) return true;
	}
	public function getMemberEmailAddress()
	{	
		return $this->data['value'];
	}

	public function __toString()
	{
		return "Member object, Username: {$this->getUserFirstLastName()}.";
	}	
}