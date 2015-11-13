<?php

namespace Ocdla;

class ProductDownload extends Product
{

	public function __construct($productId)
	{
		parent::__construct($productId);

		/**
		 * One last error should be thrown if we can't
		 * find the file associated with this product.
		 */

	}
	
	public function hasValidFile()
	{
		return file_exists($this->info["DownloadLocation"]);
	}
	
	public function __toString()
	{
		return parent::__toString();
	}
}