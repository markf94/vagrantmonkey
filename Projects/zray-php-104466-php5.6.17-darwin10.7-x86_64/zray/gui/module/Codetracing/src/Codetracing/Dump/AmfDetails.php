<?php
namespace Codetracing\Dump;

class AmfDetails {
	
	/**
	 * @var \SplFileObject
	 */
	private $file;

	/**
	 * @return string
	 */
	public function getData() {
		return $this->file->readAll();
	}

	/**
	 * @return \SplFileObject
	 */
	public function getFile() {
		return $this->file;
	}

	/**
	 * @return integer
	 */
	public function getLength() {
		return $this->file->getSize();
	}

	/**
	 * @param \SplFileObject $file
	 */
	public function setFile(\SplFileObject $file) {
		$this->file = $file;
		return $this;
	}

}