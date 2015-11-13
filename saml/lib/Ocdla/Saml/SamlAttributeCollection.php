<?php

namespace Ocdla\Saml;

class SamlAttributeCollection {
	private $attrs = array();

	public function __construct($attrs)
	{
		$this->attrs = $attrs;
	}

	public function setAttributes($attrs)
	{
		$this->attrs = $attrs;
	}
	public function getAttr($name)
	{
		return $this->attrs[$name][0];
	}
}