<?php
namespace Ocdla\Http;

interface Action
{
	public function doAction();
}

class ClientRedirect implements Action
{
	private $uri;
	
	public function __construct($uri)
	{
		$this->uri = $uri;
	}
	public function doAction()
	{
		$this->request($this->uri);
	}
	public function request($url)
	{
		session_write_close();
		header('Location: '.$url);
		exit;
	}
}