<?php


namespace Ocdla\MediaWiki;

use Ocdla\Http\LodCookie as LodCookie;
use Ocdla\Http\LodTestCookie as LodTestCookie;
use Ocdla\MediaWikiException\AuthenticationException as AuthenticationException;

/*
$MediaWikiApiResponseCodes = array(
"NoName" => "This user did not provide a username or the username parameter (lgname) was not provided to the API.",
"Illegal" => "The provided username was illegal.",
"NotExists" => "The username you provided doesn't exist",
"EmptyPass" => "You didn't set the lgpassword parameter or you left it empty.",
"WrongPass" => "The password you provided was incorrect.",
"WrongPluginPass" => "Same as WrongPass, returned when an authentication plugin rather than MediaWiki itself rejected the password.",
"CreateBlocked" => "The wiki tried to automatically create a new account for you, but your IP address has been blocked from account creation.",
"Throttled" => "You've logged in too many times in a short time.",
"Blocked" => "User is blocked.",
"mustbeposted" => "The login module requires a POST request.",
"NeedToken" => "Either you did not provide the login token or the sessionid cookie. Request again with the token and cookie given in this response."
);
*/

class ApiAuthenticationRequestTest extends ApiAuthenticationRequest
{


	public function __construct( $apiUrl, $UserID, $username, $password )
	{	
		$this->apiUrl = $apiUrl;
		$this->cookie = new LodTestCookie($UserID);
		$this->cookiefile = $this->cookie->getFilePath();
	
		$this->success 			= false;
		$this->username 		= $username;
		$this->password 		= $password;
	
		$this->stages = array(
			'init' => array(
				'action'			=> 'login',
				'lgname'			=> $username,
				'lgpassword'	=> $password,
				'format'			=> self::API_RESPONSE_FORMAT,
			),
			'confirm' => array(
				'action'			=> 'login',
				'lgname'			=> $username,
				'lgpassword'	=> $password,
				'format'			=> self::API_RESPONSE_FORMAT,
			),
		);
	}


}