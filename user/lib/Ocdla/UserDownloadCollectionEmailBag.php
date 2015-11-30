<?php

namespace Ocdla;


class UserDownloadCollectionEmailBag
{
	private $c;
	
	private static $subject = "OCDLA Downloads for order #";
	
	private static $linkFormatter = "formatAsHtmlUlList";
	
	public function __construct(UserDownloadCollection $downloads)
	{
		$this->c = $downloads;
	}

	
	private function getOrderNos()
	{
		return $this->c->map(function($download){
			return $download->getOrderId();
		});
	}
	
	private function getDownloadIds()
	{
		return $this->c->map(function($download){
			return $download->getDownloadId();
		});
	}
	
	public function getEmailBody()
	{
		return call_user_func_array(array($this,self::$linkFormatter),
			array($this->getDownloadLinks())
		);
	}

	public function getSubject()
	{
		return self::$subject.implode(',',$this->getOrderNos());	
	}

	public function getDownloadLinks()
	{
		return $this->c->map(function($download){
			return $download->getDownloadLinkHtml();
		});
	}
	
	private function formatAsHtmlUlList(array $links)
	{
		return '<ul><li>'.implode('</li><li>',$links).'</li></ul>';
	}

	
	public function getUsersEmailAddress()
	{
		return $this->c->getUserFormattedEmailAddress();
	}

	public function __toString()
	{
		return (string)$this->c;
	}
}