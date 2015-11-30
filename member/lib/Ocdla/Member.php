<?php

namespace Ocdla;

class Member
{
	private $data;
	
	public function __construct($memberId)
	{
		if (!isset($memberId)) throw new \Exception('invalid retrieval of member record in '.__FUNCTION__);	
		
		$stmt = db_query("SELECT m.*, ci.value AS email FROM {members} m LEFT JOIN {member_contact_info} ci ON(ci.contact_id=m.id) WHERE m.id=:1 AND type='email'",array($memberId),'mysql');
		
		$this->data = $stmt->fetch_assoc();
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
	
}