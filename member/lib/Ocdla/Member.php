<?php

namespace Ocdla;

class Member
{
	private $data;
	
	public function __construct($id)
	{
		if (!isset($id)) throw new Exception('invalid retrieval of member record in '.__FUNCTION__);	
		
		$stmt = db_query("SELECT * FROM {members} m LEFT JOIN {member_contact_info} ci ON(ci.contact_id=m.id) WHERE m.id=:1 AND type='email'",array($id),'mysql',true);
		
		$this->data = $stmt->fetch_assoc();
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