<?php

namespace Ocdla;


class UserDownloadCollection implements \IteratorAggregate
{
	/**
	 * member c, the collection.
	 */
	private $c;
	
	private $memberId;
	
	private $userInfo;
	
	public function __construct($memberId)
	{
		$this->c = array();
		$this->memberId = $memberId;
		$this->member = new Member($memberId);
	}
	
	public function getEmailBag()
	{
		return new UserDownloadCollectionEmailBag($this);
	}
	
	public function add(UserDownload $download)
	{
		if($download->getMemberId()==$this->memberId)
		{
			$this->c[] = $download;
		}
		else
		{
			throw new \Exception("The memberId for this download does't match the memberId for this collection.");
		}
	}

	public function getUsersFirstLastName()
	{
		return $this->member->getUserFirstLastName();
	}
	
	public function getUserFormattedEmailAddress()
	{
		return $this->member->getUserFirstLastName() . ' <'.$this->member->getUserEmail() .'>';
	}
	
	public static function newFromMemberId($memberId)
	{
		// Query for downloads that have not been processed
		$stmt = db_query('SELECT i AS downloadId FROM {downloads} WHERE memberid=:memberId',array('memberId'=>$memberId),'pdo');
		$results = $stmt->fetchAll();
		$c = new UserDownloadCollection($memberId);
		array_walk($results,function($entry) use($c){
			$c->add(new UserDownload($entry['downloadId']));
		});
		return $c;
	}
	
	public function populateFromMemberId($memberId)
	{
		// Query for downloads that have not been processed
		$stmt = db_query('SELECT i AS downloadId FROM {downloads} WHERE memberid=:memberId',array('memberId'=>$memberId),'pdo');
		$results=$stmt->fetchAll();
		array_walk($results,function($entry){
			$this->add(new UserDownload($entry['downloadId']));
		});
		return $this;
	}

	// return iterator
	public function getIterator()
	{
		return new \ArrayIterator($this->c);
	}
	
	public function map(callable $fn)
	{
		$result = array();

		foreach ($this as $item)
		{
			$result[] = $fn($item);
		}

		return $result;
	}

	public function __toString()
	{
		return entity_toString($this->c);
	}

}