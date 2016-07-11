<?php

namespace Ocdla;


class UserDownloadManager
{
	/**
	 * member c, the collection.
	 */
	private $c;
	
	private static $adminEmail = 'jbernal.web.dev@gmail.com';
	
	public function __construct()
	{
		$this->c = array();
	}

	public function populateDownloadsFromMemberId($memberId)
	{
		$this->c[$memberId] = UserDownloadCollection::newFromMemberId($memberId);
	}
	
	public function populateDownloadsFromTimestamp($time)
	{
		// Query for downloads that have not been processed
		$stmt = db_query('SELECT memberid AS memberId, i AS downloadId FROM {downloads} WHERE notification_time IS NULL AND file_creation_time>=:time',array('time'=>$time),'pdo');
		$results = $stmt->fetchAll();
		foreach($results as $entry)
		{
			$this->checkDownloadAndAdd($entry);
		}
		return count($results);
	}
	
	private function checkDownloadAndAdd($entry)
	{
		$d = new UserDownload($entry['downloadId']);
		if(empty($download->getUserId()))
		{
			throw new \Exception("There is no valid member affiliated with this download (Download id: {$downloadId}; member.id: {$download->userId})");
		}
		$this->addDownload($d);
	}
	
	public function addDownload(UserDownload $download)
	{
		if(empty($download->getMemberId()))
		{
			throw new \Exception('This download must belong to a member but no memberid was given.');
		}
		if(!isset($this->c[$download->getMemberId()]))
		{
			$this->c[$download->getMemberId()] = new UserDownloadCollection($download->getMemberId());
		}
		$this->c[$download->getMemberId()]->add($download);
	}
	
	public function notify()
	{
		/*
		array_walk($this->c,function(UserDownloadCollection $downloads){
			try
			{
				$this->sendEmailNotification($downloads);
				$this->updateFileNotificationTime($downloads);
			}
			catch(\Exception $e)
			{
				print $e;
			}
		});
		*/
		foreach($this->c as $downloads)
		{
			try
			{
				$this->sendEmailNotification($downloads);
				$this->updateFileNotificationTime($downloads);
			}
			catch(\Exception $e)
			{
				print $e;
			}
		}
	}
	
	public function notifyAdmin()
	{
		array_walk($this->c,function(UserDownloadCollection $downloads){
			try
			{
				$this->sendAdminEmailNotification($downloads);
				$this->updateFileNotificationTime($downloads);
			}
			catch(\Exception $e)
			{
				print $e;
			}
		});
	}
	
	public function getDownloadLink($type,$productid,$contact_id)
	{
		$link = $type == "pdf" ? "https://www.ocdla.org/index.php?q=download/pdf&productid={$productid}" : "https://www.ocdla.org/index.php?q=download/zip&productid={$productid}&contact_id={$contact_id}";
		return "https://member.ocdla.org/my-downloads";
	}
	
	public function sendEmailNotification($downloads)
	{
		$bag = $downloads->getEmailBag();
		\sendMail($bag->getUsersEmailAddress(), $bag->getSubject(), array(
			'name'			=> $downloads->getUsersFirstLastName(),
			'links'			=> $bag->getEmailBody()));
	}
	
	public function sendAdminEmailNotification($downloads)
	{
		$bag = $downloads->getEmailBag();
		\sendMail(self::$adminEmail, $bag->getSubject(), array(
			'name'			=> $downloads->getUsersFirstLastName(),
			'links'			=> $bag->getEmailBody()));
	}
	
	public function updateFileNotificationTime(UserDownloadCollection $coll)
	{
		$time = time();
		$coll->map(function($download) use($time){
		$result = db_query("UPDATE {downloads} SET notification_time=:1 WHERE i=:2",
			array($time,$download->getDownloadId()),'mysql',true);
		});
	}
	
	public function __toString()
	{
		$str = '';
		foreach($this->c as $coll)
		{
			$str .= (string)$coll;
		}
		return $str;
	}
}