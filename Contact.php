<?php

namespace Ocdla;


class Contact
{
	private $contact_info;
	
	function __construct($contact_id)
	{
		$this->contact_info = array();
		$contact = new DBQuery(
			$params = array(
				"type"=>"select",
				"tablenames"=>array(
					0	=>array(
						"name"=>"member_contact_info",
						"op"=>"",
						"fields"=> array()
						)
				),
				'keys' => array(
					'contact_id' => $contact_id,
				),
			)
		);
		$info = $contact->exec();
		foreach( $info AS $entry ) {
			$this->contact_info[$entry['type']] = $entry;
		}
	}
	
	public function getInfo()
	{
		return $this->contact_info;
	}
	
	
}

