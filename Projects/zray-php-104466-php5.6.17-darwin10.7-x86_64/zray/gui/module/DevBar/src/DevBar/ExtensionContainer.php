<?php
namespace DevBar;

class ExtensionContainer {
	/**
	 * @var array
	 */
	protected $extension;
	
	/**
	 * @param array $extension
	 */
	public function __construct(array $extension) {
		$this->extension = $extension;
	}
	
	public function toArray() {
		return $this->extension;
	}	
	
	/**
	 * @return integer
	 */
	public function getId() {
		return (isset($this->extension['id']) ? $this->extension['id'] : '');
	}
	
	/**
	 * @return string
	 */
	public function getExtension() {
		return (isset($this->extension['namespace']) ? $this->extension['namespace'] : '');
	}
	
	/**
	 * @return string
	 */
	public function getDataType() {
		return (isset($this->extension['data_type']) ? $this->extension['data_type'] : '');
	}
	
	/**
	 * @return mixed
	 */
	public function getData() {
		return (isset($this->extension['serialized_data']) ? @unserialize($this->extension['serialized_data']) : null);
	}
	
	/**
	 * @return string
	 */
	public function getRowIndex() {
	    return (isset($this->extension['row_index']) ? $this->extension['row_index'] : '');
	}
}