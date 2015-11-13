<?php
namespace Ocdla\Http;

use Ocdla\Http\ValidSessionCookiePolicy as ValidSessionCookiePolicy;

class ClientSession
{
	private $sessionCookieName;
	
	private $sessionUrl;
	
	private $enforceValidSessionCookie;
	
	private $sessionRulesEnforced = false;
	
	private $fulfillmentUrl;
	
	private $sessionId;
	
	private $policies = array();
	
	public function __construct()
	{
		$this->enforceValidSessionCookie = false;
		$this->enforced = false;
	}	
	public function getSessionIdentifier()
	{
		return $_COOKIE['OCDLA_SessionId'];
	}
	public function onPolicyFullfillment($action,$params)
	{
		$this->fulfillmentUrl = $params;
	}
	private function evaluatePolicies()
	{
		foreach($this->policies as $policy)
		{
			if($policy->fails()) return false;
		}
		return true;
	}
	public function onPolicyPass(Action $ca)
	{
		if($this->evaluatePolicies())
		{
			$ca->doAction();
		}
	}
	public function passesPolicy()
	{		
		if(!$this->evaluatePolicies()) return false;
		else return true;
	}
	public function policyFulfillmentService($serviceName,$uri,$args)
	{
		$this->policyFulfillment = $serviceName;
		$this->sessionUrl = $uri .'/?ref='.$args['redirect'];
		$this->sessionUrl = '/sess?server=auth.ocdla.org&ref=/sampleLogin.php';
	}
	public function request($url)
	{
		session_write_close();
		header('Location: '.$url);
		exit;
	}
	
	public function enforcePolicy($policyName,$arg1,$arg2=null)
	{
		$this->enforced = false;
		$policyClass = "Ocdla\\Http\\{$policyName}Policy";
		$this->policies[] = new $policyClass($arg1,$arg2);
	}
	
	public function isEnforced()
	{
		if($this->enforced) return true;
		else return false;
	}
	
	public function fulfillPolicy()
	{
		session_write_close();
		header('Location: '.$this->sessionUrl);
	}
}