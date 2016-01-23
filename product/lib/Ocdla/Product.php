<?php

namespace Ocdla;


class Product
{

	protected $info;

	protected $productId;
	
	protected $options;
	
	const OPTION_DELIMITER = '-';
	
	public function __construct($productId)
	{
		if(empty($productId))
		{
			throw new \Exception('Class Product: constructor expects parameter $product_id.');
		}
		$this->productId = $productId;
		$this->info = db_query('SELECT *, Description2 as Description, Truncate(Price,0) AS Price, Truncate(RegularPrice,0) AS RegularPrice FROM {catalog} WHERE i=:productId',array('productId'=>$productId),'pdo')->fetch();
	}

	public function getId()
	{
		return $this->info['i'];
	}

	public function getDescription()
	{
		return $this->info['Description'];
	}
	
	public function getImage()
	{
		return $this->info['Image'];
	}
	
	public function getEditor()
	{
		return $this->info['Author_Editor'];	
	}
	
	public function membersOnly()
	{
		return $this->NonMemberPurchase == "no";
	}

	public function getName()
	{
		return $this->info['Item'];
	}
	
	public function getMemberPrice()
	{
		return $this->info['Price'];
	}
	
	public function getPrice()
	{
		return $this->info['RegularPrice'];
	}
	
	public function getProductOptionName()
	{
		if($start = strpos($this->Item,self::OPTION_DELIMITER))
			return $this->parseOptionName($start);
		else return $this->Item;
	}
	
	private function parseOptionName($start)
	{
		return substr($this->Item, $start+1);
	}
	
	public function __get($prop)
	{
		return $this->info[$prop];
	}
	
	public function getOptions()
	{
		// So here we could query for any product options.
		
		// What is the query?
		$query = "SELECT t2.i
		FROM {catalog} AS t1, {catalog} AS t2
		WHERE t1.i=:productId
		AND t1.fm_parent_id=t2.fm_child_id AND t1.fm_parent_id IS NOT NULL
		AND t2.disabled<>1
		ORDER BY t2.OrderPriority";
		
		// Execute the query here
		$stmt = db_query($query,array('productId'=>$this->productId),'pdo');
		
		// Loop through creating new Products for each matched option
		while($entry = $stmt->fetch())
		{
			$this->options[] = new Product($entry['i']);
		}
		
		return $this->options;
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