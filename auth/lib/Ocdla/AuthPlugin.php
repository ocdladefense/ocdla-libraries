<?php

namespace Ocdla;


abstract class AuthPlugin
{	
	// The database connection this plugin uses.
	protected $db;
	
	public function __construct($container)
	{
		// Instantiate new Database object
		if(get_class($container)=='Doctrine\DBAL\Connection')
		{
			$this->db = $container;
		}
		else
		{
			$this->db = $container->get('database_connection');
		}
	}
	
	public function provisionUser($userObj)
	{
		$userObj->create();
	}
	
	public function authenticate($username=null,$password=null)
	{
		return false;
	}
	
	protected function processAuthenticationErrors($errors)
	{
		if(count($errors))
		{
			throw new \Exception(implode("\n",$errors));
		}
	}
}