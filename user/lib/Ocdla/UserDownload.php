<?php

use Clickpdx\Core\Routing\RouteException;

namespace Ocdla;


class UserDownload
{
	/**
	 * variable downloadPath
	 *
	 * The path prepended to saved files. The
	 * complete filename should be saved as:
	 * self::$downloadPath . '/'. $user_filename
	 * where $user_filename = "{$this->username}_{$this->filename}".
	 */
	static $destPath = DOWNLOAD_PATH;
	
	static $sourcePath = UPLOAD_PATH;
	
	public $type;
	
	static $ownerpw = "ocdla";
	
	const ITEM_TITLE_SEPARATOR = '-';
	
	private $userId;
	
	/**
	 * var username
	 *
	 * The username of the user associated with this download.
	 */
	private $username;
	
	private $password;
	
	private $productId;
	
	private $productName;
	
	private $downloadLocation;
	
	private $filename;
	
	private $userFilename;

	private $orderId;
	
	private $userFullname;
	
	private $exitCode;
	
	public function setDownloadPath($path)
	{
		self::$downloadPath = $path;
	}
	
	public static function newFromParams($params)
	{
		if(empty($params['memberId'])||empty($params['productId']))
		{
			throw new \Exception(__METHOD__.' expects both memberId and productId parameters.');
		}
		$stmt = db_query('SELECT d.i AS downloadId FROM {downloads} d WHERE memberid=:memberId AND productid=:productId',
			array('memberId'=>$params['memberId']
				,'productId'=>$params['productId']),'pdo');
		$info = $stmt->fetch();
		return new UserDownload($info['downloadId']);
	}

	public function __construct($downloadId)
	{	
		if(empty($downloadId)) throw new \Exception('Class UserDownload: constructor expects parameter downloadId.');
		
		$stmt = db_query('SELECT m.username, m.name_first, m.name_last, m.autoId, m.id AS memberId, cat.i AS productId, cat.DownloadLocation, cat.Item, cat.SoftwareType, d.* FROM {members} m LEFT JOIN {downloads} d ON(m.id=d.memberid) JOIN {catalog} cat ON(cat.i=d.productid) WHERE d.i=:downloadId',
			array('downloadId'=>$downloadId),'pdo');
		
		$info = $stmt->fetch();
		if(!$info) throw new \Exception("No download with id, {$downloadId}, exists!");
		
		$this->productId 				= $info['productId'];
		
		$this->downloadId				= $info['i'];
		
		$this->userId 					= $info['memberId'];
		
		$this->userFullname			= $info['name_first'] . ' '.$info['name_last'];
		
		$this->orderId					= $info['Order_ID'];
		
		$this->productName			= $this->parseProductName($info['Item']);
		
		$this->type 						= $info['SoftwareType'];
		
		$path 									= explode('\\',$info["DownloadLocation"]);
		$this->filename 				= array_pop($path);
		
		$this->username 				= $info['username'];
		
		$this->password 				= $info['password'];
		
		$this->userFilename 		= "{$this->username}_{$this->filename}";
		$this->userFilename			= $this->type == 'zip'? $this->userFilename .'.zip':
																	$this->userFilename;
	}

	private function parseProductName($title)
	{
		return $this->titleHasSeparator($title)
					?
					substr($title,0,strpos($title,self::ITEM_TITLE_SEPARATOR))
					:
					$title;
	}
	
	private function titleHasSeparator($title)
	{
		return strpos($title,self::ITEM_TITLE_SEPARATOR)!==false;
	}



	public function getDownloadType()
	{
		return $this->type;
	}

	public function getProductName()
	{
		return $this->productName;
	}
	
	public function getDownloadId()
	{
		return $this->downloadId;
	}
	
	public function getUserFullName()
	{
		return $this->userFullname;
	}
	
	public function getOrderId()
	{
		return $this->orderId;
	}

	public function getProductId()
	{
		return $this->productId;
	}
	
	public function getMemberId()
	{
		return $this->userId;
	}
	
	public function getUserId()
	{
		return $this->userId;
	}

	public function setFileCreationTime($time=null)
	{	
		$stmt = db_query('UPDATE {downloads} SET file_creation_time=:time WHERE i=:downloadId',
			array('time'=>$time
				,'downloadId'=>$this->downloadId),'pdo');
	}

	public function setNotificationTime($time = NULL)
	{
		$stmt = db_query('UPDATE {downloads} SET notification_time=:time WHERE i=:downloadId',
			array('time'=>$time
				,'downloadId'=>$this->downloadId),'pdo');
	}	
	
	public function createUserFile()
	{
		return 		$this->exitCode = ($this->type == "pdf" ?
			$this->createUserPdf() :
			$this->createUserZip());
	}
	
	private function createUserZip()
	{
		if($this->type!='zip')
		{
			throw new \Exception("Cannot create a zip archive from a non-zip source!");
		}
		$sourceDir = self::$sourcePath . '/' . $this->filename;
		if(chdir($sourceDir))
		{
			exec($this->getZipCommand(), $output, $return_var);
		}
		else
		{
			throw new \Exception("Could not initialize zip creation within directory, {$sourceDir}.");
		}
		return $return_var;	
	}
	
	
	private function createUserPdf()
	{	
		if ($this->type != "pdf")
		{
			throw new \Exception('Class UserDownload: function createUserPdf being invoked on a non-pdf UserDownload instance!'); 
		}
		
		if (chdir(self::$sourcePath)&&$this->fileExists())
		{
			exec($this->getPdfCommand(), $output, $return_var);
		}
		else
		{
			throw new \Exception("Could not change directory to ".self::$sourcePath .' or the file, '.$this->filename .', does not exist.');
		}
		return $return_var;
	}
	
	public function getExitCode()
	{
		return $this->exitCode;
	}
	
	public function fileExists()
	{
		return file_exists(self::$sourcePath .'/' . $this->filename);
	}
	
	public function userFileExists($basePath)
	{
		return file_exists((isset($basePath)?$base_path:self::$destPath) . "/{$this->userFilename}");
	}
	
	private function getZipCommand()
	{
		$executable = "/usr/bin/zip";
		$options = "rv9";
		$inFile = ".";
		$outFile = self::$destPath ."/{$this->userFilename}";
		return $executable . " -{$options} {$outFile} {$inFile}";
	}
	
	private function getPdfCommand()
	{
		$executable = "/usr/bin/pdftk";
		$options = "owner_pw ".self::$ownerpw." allow Printing CopyContents verbose";
		$logFile = "/var/log/pdftk-web.log";
		$inFile = self::$sourcePath . "/" .$this->filename;
		$outFile = self::$destPath .'/'.$this->userFilename;
		return $executable . " {$inFile} output {$outFile} {$options} >> {$logFile}";
	}
	
	private function isPdf()
	{
		return $this->type==='pdf';
	}
	
	private function getShellCommand()
	{
		return $this->isPdf()?$this->getPdfCommand():$this->getZipCommand();
	}
	

	
	public function copyToDownloads()
	{
		$source = self::$sourcePath . '/' . $this->userFilename;
		$target = self::$destPath . '/' . $this->userFilename;
		return copy($source,$target);
	}


	private function fileStatusIcon($boolean)
	{
		if($boolean)
		{
			return "<img style='width:18px;' src='/sites/default/files/icons/status-ok.png' alt='File exists.' />";
		}
		else
		{
			return "<img style='width:18px;' src='/sites/default/files/icons/chrome_red_lock_details.png' alt='File doesn't exist.' />";
		}
	}

	
	public function getUrl($absolute=false)
	{
		$path = '/sites/default/files/downloads';
		return $absolute ?
			'https://members.ocdla.org'.$path .'/'. $this->userFilename
		:$path. '/'.$this->userFilename;
	}
	
	public function getDownloadLinkHtml()
	{
		return "<a href='{$this->getUrl(true)}'>{$this->getProductName()}</a>";
	}
	
	public function getDownloadLink()
	{
		switch($this->type)
		{
			case 'zip':
				return '/download/zip/'.$this->downloadId;
				break;
			case 'pdf':
				return $this->getUrl();
				break;
		}
	}

	public function getMemberHtml()
	{
		$sourceFileStatus = $this->fileStatusIcon($this->fileExists());
		$userFileStatus = $this->fileStatusIcon($this->userFileExists());
									
		$str = "<table style='margin-bottom:8px; width:475px; border:1px solid #666;'>
			<thead>
				<tr>
					<th style='width:100px;text-align:center;'>File Ready?</th>
					<th>File Name</th>
					<th>Type</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan='3'>
						This file is for {$this->getUserFullName()}.
					</td>
				</tr>
				<tr>
					<td style='width:100px;text-align:center;'>
						{$sourceFileStatus}
					</td>
					<td style='width:300px;text-align:center;'>
						<a href='{$this->getDownloadLink()}' title='Download this file'>
							{$this->filename}
						</a>
					</td>
					<td style='text-align:center;'>{$this->type}</td>
				</tr>
			</tbody>
		</table>";
		return $str;
	}

	public function __toString()
	{
		$sourceFileStatus = $this->fileStatusIcon($this->fileExists());
		$userFileStatus = $this->fileStatusIcon($this->userFileExists());
									
		$str = "<table style='margin-bottom:8px; width:475px; border:1px solid #666;'>
			<thead>
				<tr>
					<th>FileExists?</th>
					<th>FileName</th>
					<th>Type</th>
					<th>DownloadId</th>
					<th>UserId</th>
					<th>ProductId</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style='width:25px;text-align:center;'>
						{$sourceFileStatus}
					</td>
					<td style='width:225px;text-align:center;'>{$this->filename}</td>
					<td style='text-align:center;'>{$this->type}</td>
					<td style='text-align:center;'>{$this->downloadId}</td>
					<td style='text-align:center;'>{$this->userId}</td>
					<td style='text-align:center;'>{$this->productId}</td>
				</tr>
				<tr>
					<td colspan='6' style='text-align:center;background-color:#666;color:#fff;'>
						Below is the user information for this file:
					</td>
				<tr style='border-top:1px solid #666;'>
					<td style='text-align:center;'>
						{$userFileStatus}
					</td>
					<td colspan='5'>
						{$this->getShellCommand()}
					</td>
				</tr>
			</tbody>
		</table>";
		return $str;
	}
}