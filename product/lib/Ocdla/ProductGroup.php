<?php

class ProductGroup extends Product {

public $info;
public $db_product;

public function __construct( $product_id ) {
	if( empty( $product_id ) ) {
		throw new Exception('Class Product: constructor expects parameter $product_id.');
	}

	$product = new DBQuery(
		array(
			"type" => "select",
			"select" => array(
				"*",
				"cat1.i AS parent_i",
				"cat2.Item AS Item",
				"cat2.Price AS Price",
				"cat2.RegularPrice AS RegularPrice",
			),//select
			"tablenames" => array(
				0 => array(
					"name" => "catalog",
					"op" => "",
					"fields" => array()
				)//0
			),//tablenames
			"joins" => array(
				array(
"join_tables" => array(
									array(
										"tablename" => "catalog",
										"alias" => "cat1"
									),//first join table
									array(
										"tablename" => "catalog",
										"alias" => "cat2"
									)//second join table
								),//these tables make up the first join
								"join_type" => "LEFT JOIN",
								"join_fields" => array(
									"cat1.fm_parent_id",
									"cat2.fm_child_id"
								),//join_fields
								"join_op" => "="
				)//first join
			),//joins
			"where" => array(
				"cat1.i={$product_id}",
			)//where
		)//params
	);//product query
	
	$this->db_product = $product->exec();
	if( $product->getNumRows()==0 || $product->getNumRows()==NULL ) {
		throw new Exception('Class Product: Product with id {$product_id} not found.');
	}

}//constructor


public function getNumberOfRelatedProducts() {
	return count($this->db_product);
}



}//class ProductGroup