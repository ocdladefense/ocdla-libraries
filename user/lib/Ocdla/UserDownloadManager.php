<?php

namespace Ocdla;


class UserDownloadManager
{
	/**
	 * member c, the collection.
	 */
	private $c;
	
	public function __construct()
	{
		$this->c = array();
	}

	public function populateDownloadsFromMemberId($memberId)
	{
		$this->c[$memberId] = UserDownloadCollection::newFromMemberId($memberId);
	}
	
	public function addDownload(UserDownload $download)
	{
		if(!isset($this->c[$memberId]))
		{
			$this->c[$memberId] = new UserDownloadCollection($download->getMemberId());
		}
		$this->c[$memberId]->add($download);
	}
	
	public function populateDownloadsFromTimestamp($time)
	{
		// Query for downloads that have not been processed
		$stmt = db_query('SELECT memberid AS memberId, i AS downloadId FROM {downloads} WHERE file_creation_time>=:time',array('time'=>$time),'pdo');
		$results = $stmt->fetchAll();
		$c = new UserDownloadCollection();
		array_walk($results,function($entry){
			$this->addDownload(new UserDownload($entry['downloadId']));
		});
	}
	
	public function notify()
	{
		array_walk($this->c,function(UserDownloadCollection $downloads){
			$this->sendEmailNotification($downloads);
		});
	}
	
	public function sendEmailNotification($downloads)
	{
		$bag = $downloads->getEmailBag();
		\sendMail($bag->getUsersEmailAddress(), $bag->getSubject(), array(
			'name'			=> $downloads->getUsersFirstLastName(),
			'links'			=> $bag->getEmailBody()));
	
		// once the email has been sent out for this UserDownload
		// modify the notification_time field
		// $result = $this->updateFileNotificationTime($ids);
	}
	
	public function updateFileNotificationTime($downloadIds)
	{
		if (empty($downloadIds)||count($downloadIds)<1) return false;
		$result = db_query("UPDATE {downloads} SET notification_time=:1 WHERE i IN(:2)",
			array(time(),$downloadIds));
		return $result;
	}
	
	public function __toString()
	{
		return (string)$this->c;
	}
}