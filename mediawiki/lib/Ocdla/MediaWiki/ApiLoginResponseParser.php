<?php


namespace Ocdla\MediaWiki;

/* login array*/ 
class ApiLoginResponseParser
{
	private $result;
	
	private $token;
	
	private $cookieprefix;
	
	private $sessionid;
	
	private $lgtoken;
	
	private $lguserid;
	
	private $lgusername;
	
	private $xml;
	
	public function __get($prop)
	{
		return $this->{$prop};
	}
	
	public function __construct($xml)
	{
		$d = new \DOMDocument() and $d->preserveWhiteSpace = false and $d->formatOutput=true;
		$d->loadXML($xml);
		if(empty($xml)) throw new Exception('Could not log onto to Library of Defense.');
		$this->xml = $d->saveXML();
		$this->attributes = $d->getElementsByTagName('login')->item(0)->attributes;	
		$login=$d->getElementsByTagName('login')->item(0);

		$this->result					= $login->getAttribute('result');
		$this->token 					= $login->getAttribute('token');
		$this->cookieprefix 	= $login->getAttribute('cookieprefix');
		$this->sessionid 			= $login->getAttribute('sessionid');
		
		$this->lgtoken 				= $login->getAttribute('lgtoken');
		$this->lguserid 			= $login->getAttribute('lguserid');
		$this->lgusername 		= $login->getAttribute('lgusername');
	}
	public function getXml()
	{
		return $this->xml;
	}
	public function getResult()
	{
		return $this->result;
	}	
	public function getToken()
	{
		return $this->token;
	}
	public function __toString()
	{
		return "<pre>".htmlspecialchars($this->xml)."</pre>";
	}
}