<?php

namespace Ocdla;


class Product
{

	protected $info;

	public function __construct($productId)
	{
		if(empty($productId))
		{
			throw new \Exception('Class Product: constructor expects parameter $product_id.');
		}
		$this->info = db_query('SELECT * FROM catalog WHERE i=:1',array($productId))->fetch_assoc();
	}

	public function getName()
	{
		return $this->info['Item'];
	}


	public function __toString()
	{
		return "<pre>".print_r($this->info,true)."</pre>";
		$table = '<table rowspacing="0" cellspacing="0">';
				for($i=0; $i<count($this->info);$i++)
				{
					$cells = "";
					$row = "<tr>";
				
					if( $i==0 ) {
						$table .= "<tr>";
						foreach( $this->info[$i] AS $key=>$value )
						{
							$table .= "<th>{$key}</th>";	
						}
						$table .= "</tr>";
					}
				
					foreach( $this->info[$i] AS $key=>$value )
					{
						$cells .= "<td>{$value}</td>";
					}
					$row .= $cells;
					$row .= "</tr>";
				
					$table .= $row;
				}
				$table .= "</table>";
				return $table;
	}
}