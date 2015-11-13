<?php
namespace Ocdla\Http;

interface PolicyEnforcement
{
	public function passes();
	public function fails();
}


class Policy implements PolicyEnforcement
{
	protected $policyName;
	
	public function __construct(){}
	
	public function __toString()
	{
		$str += 'Policy Name: '.$this->policyName;
		return $str;
	}
	public function passes()
	{
	
	}
	public function fails()
	{
		
	}
}