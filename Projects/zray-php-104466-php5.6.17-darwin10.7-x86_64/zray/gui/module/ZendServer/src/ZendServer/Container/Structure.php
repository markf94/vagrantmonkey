<?php
namespace ZendServer\Container;

interface Structure {
	/**
	 * Return an array structure ready to be consumed by Json or Xml parser for WebAPI output
	 * @return array
	 */
	public function toArray();
}

