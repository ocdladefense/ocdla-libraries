<?php


namespace Ocdla\MediaWiki;

use Ocdla\Http\LodCookie;
use Ocdla\MediaWikiException\AuthenticationException;
use Ocdla\MediaWiki\ApiLoginResponseParser;

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

class MediaWikiApiRequest {

	const API_RESPONSE_FORMAT = 'xml';
	
	protected $endpoint;
	
	protected $uid;
	
	protected $username;
	
	protected $password;
	
	protected $cookiefile;
	
	private $header;
	
	private $body;

	private $parser;
	
	private static $cookieClass = "\\Ocdla\\Http\\LodCookie";
	
	
	
	private $cookies = array( 'sessionid' => 'session', 'lguserid'=>'UserID', 'lgusername' => 'UserName', 'lgtoken' => 'Token');
	
	
	
	public function __construct($url) {	
		$this->endpoint = $url;
	}
	

	public function setUserId($uid){
		$this->uid = $uid;
	}

	
	public function setUsername($username){
		$this->username = $username;
	}	
	
	public function setPassword($password){
		$this->password = $password;
	}




	// throw new AuthenticationException($this);



	public function getHeader(){
		return $this->header;
	}
	
	public function getBody(){
		return $this->body;
	}
	
	public function getCookiePath(){
		return $this->cookiefile;
	}
	
	public function send()
	{
		
		// Make sure cookie can be created and opened for writing.
		$cookie = new self::$cookieClass($this->uid, 'w');
		
		$this->cookiefile = $cookie->getFilePath();
		
		$params = array(
			'action'			=> 'login',
			'lgname'			=> $this->username,
			'lgpassword'	=> $this->password,
			'format'			=> self::API_RESPONSE_FORMAT
		);
		
		
		$this->body = $this->formatRequestBody($params);
		
		
		
		$ch = curl_init($this->endpoint);
		curl_setopt($ch, 		CURLOPT_COOKIESESSION, true);
		curl_setopt($ch, 		CURLOPT_POST, true);
		// curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9');
		curl_setopt($ch, 		CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, 		CURLOPT_ENCODING, "UTF-8" );
		curl_setopt($ch, 		CURLOPT_POSTFIELDS, $this->body);
		curl_setopt($ch, 		CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, 		CURLOPT_COOKIEJAR, $this->cookiefile);
		curl_setopt($ch, 		CURLOPT_VERBOSE, 0);
		curl_setopt($ch, 		CURLOPT_HEADER, 1);
		$response = curl_exec($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		
		$header = substr($response, 0, $header_size);
		$body = substr($response, $header_size);
		
		curl_close($ch);
		
		return new ApiLoginResponseParser($body,$header);
		// return array('header'=>$header, 'body'=>$body);
	}


	
	private function formatRequestBody($params=array())
	{
		$b = array();
		
		foreach( $params as $k=>$v ) {
			$b[] = "$k=$v";
		}
		$b = implode("&",$b);
		
		
		return $b;
	}

	
	private function getVal($key)
	{
		return $this->loginRespParser->{$key};
	}
	
	public function __toString()
	{
		$ret="<h3>Initial Request:</h3><p>{$this->getRequestParams('init')}</p>";
		$ret.=$this->initRespParser;
		$ret.="<h3>Secondary Request:</h3><p>{$this->getRequestParams('login')}</p>";
		$ret.=$this->loginRespParser;
		return $ret;
	}
	
	
}