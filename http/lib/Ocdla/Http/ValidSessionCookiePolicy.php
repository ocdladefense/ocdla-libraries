<?php
namespace Ocdla\Http;


class ValidSessionCookiePolicy extends Policy
{
	private $cookieName;
	
	public function __construct($cookieName)
	{
		// $this->policyName = $policyName;
		$this->cookieName = $cookieName;
	}
	public function passes()
	{
		if(isset($_COOKIE[$this->cookieName])) return true;
		else return false;
	}
	public function fails()
	{
		if(!isset($_COOKIE[$this->cookieName])) return true;
		else return false;
	}
}