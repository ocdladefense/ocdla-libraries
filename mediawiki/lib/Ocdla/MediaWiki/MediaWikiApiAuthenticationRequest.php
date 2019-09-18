<?php


namespace Ocdla\MediaWiki;

use Ocdla\Http\LodCookie as LodCookie;
use Ocdla\MediaWikiException\AuthenticationException as AuthenticationException;



class ApiAuthenticationRequest
{
	const API_RESPONSE_FORMAT = 'xml';
	
	protected $endpoint;
	
	protected $uid;
	
	protected $username;
	
	protected $password;
	
	protected $cookie;
	
	protected $cookiefile;
	
	private $header;
	
	private $body;

	private $loginRespParser;
	
	private $cookies = array( 'sessionid' => 'session', 'lguserid'=>'UserID', 'lgusername' => 'UserName', 'lgtoken' => 'Token');
	

		$this->loginParams = array(
			'action'			=> 'login',
			'lgname'			=> $username,
			'lgpassword'	=> $password,
			'format'			=> self::API_RESPONSE_FORMAT,
		);
	}


	
	public function getHeader(){
		return $this->header;
	}
	
	public function getBody(){
		return $this->body;
	}
	
	public function send()
	{
		
		$cookie = new self::$cookieClass($this->uid);
		
		$this->cookiefile = $cookie->getFilePath();
		
		$params = array(
			'action'			=> 'login',
			'lgname'			=> $this->username,
			'lgpassword'	=> $this->password,
			'format'			=> self::API_RESPONSE_FORMAT
		);
		
		
		$this->body = $this->formatRequestBody($params);
		
		
		
		$ch = curl_init($this->endpoint);
		curl_setopt($ch, 		CURLOPT_POST, true);
		// curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9');
		curl_setopt($ch, 		CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, 		CURLOPT_ENCODING, "UTF-8" );
		curl_setopt($ch, 		CURLOPT_POSTFIELDS, $this->body);
		curl_setopt($ch, 		CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, 		CURLOPT_COOKIEFILE, $this->cookiefile);
		curl_setopt($ch, 		CURLOPT_COOKIEJAR, $this->cookiefile);
		curl_setopt($ch, 		CURLOPT_VERBOSE, 0);
		curl_setopt($ch, 		CURLOPT_HEADER, 1);
		$response = curl_exec($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		
		$header = substr($response, 0, $header_size);
		$body = substr($response, $header_size);
		
		curl_close($ch);
		
		

		return new ApiLoginResponseParser($body,$header);
	}



	private function setToken($token){
		$this->token = token;
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
	
	
	public function __toString()
	{
		$ret="<h3>Initial Request:</h3><p>{$this->getRequestParams('init')}</p>";
		$ret.=$this->initRespParser;
		$ret.="<h3>Secondary Request:</h3><p>{$this->getRequestParams('login')}</p>";
		$ret.=$this->loginRespParser;
		return $ret;
	}
}