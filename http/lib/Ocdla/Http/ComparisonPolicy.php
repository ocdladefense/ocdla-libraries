<?php
namespace Ocdla\Http;


class ComparisonPolicy extends Policy
{
	private $op1;
	
	private $op2;
	
	public function __construct($op1,$op2)
	{
		$this->op1 = $op1;
		$this->op2 = $op2;		
	}
	public function passes()
	{
		if($op1===$op2) return true;
		else return false;
	}
	public function fails()
	{
		if($op1!==$op2) return true;
		else return false;
	}
}