<?php

namespace Ocdla;

class UserSession
{
	private $expires;
	
	private $sessionId;
	
	private $contactId;
	
	private $userId;
	
	private $data;

	private $model;

	private $username;
	
	public function getUsername()
	{
		return $this->username;
	}
	public function __construct($sessionId=null)
	{
		$this->sessionId = isset($sessionId) ? $sessionId : $_COOKIE['OCDLA_SessionId'];
		$query = new DBQuery(
			$params = array(
				"type" => "select",
				"tablenames" => array(
					0 => array(
						"name"		=>	"members",
						"op" 			=> 	"",
						"fields" 	=> 	array( "autoId" )
					),
					1 => array(
						"name"		=>	"my_aspnet_Sessions",
						"op" 			=> 	"LEFT JOIN",
						"fields" 	=> 	array( "UserID" )
					)
				),
				"schema" => array(
					"members",
					"my_aspnet_Sessions"
				),
				"where" => array("my_aspnet_Sessions.SessionID='{$this->sessionId}'")
			)
		);
		$rows 					= $query->exec();
		$this->model 		= $session = $rows[0];
		$this->userId 	= $session['UserID'];
		$this->username	= $session['username'];
		$this->data 		= $session['Data'];
	}
	public function getFields()
	{
		return $this->model;
	}
	public function isAuthenticated()
	{
		return (isset($this->userId)&&!empty($this->userId)?true:false);
	}
	
	public function update_session( $UserID, $timeout = 120, $datetime = '2014-08-05 00:00:00' )
	{
		$timeout = defined('OCDLA_SESSION_TIMEOUT') ? OCDLA_SESSION_TIMEOUT : 25920000;
		$datetime = defined('OCDLA_SESSION_EXPIRES') ? OCDLA_SESSION_EXPIRES : '2014-08-05 00:00:00';
		$session = new DBQuery(
			array(
				"type" => "update",
				"tablenames" => array(
					0 => array(
						"name" => "my_aspnet_Sessions",
						"op" => "",
						"fields" => array()
					)
				),
				"fields" => array(
					"Expires" 	=> $datetime,
					"Timeout" 	=> $timeout,
					"UserID" 		=> $UserID
				),
				"where" => array(
					"SessionID='{$this->sessionId}'"
				)
			)
		);
		$session->exec();
	}


	public function __toString()
	{
		$tmp = "Session id is: {$this->sessionId}.<br />";
		$tmp .= "<ul><li>" . implode( "</li><li>", $this->model ) . "</li></ul>";
		return $tmp;
	}
}