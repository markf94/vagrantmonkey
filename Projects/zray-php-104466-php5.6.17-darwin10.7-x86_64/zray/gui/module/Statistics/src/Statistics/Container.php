<?php
namespace Statistics;

class Container {
	const TYPE_LINE 	  	= 0;
	const TYPE_PIE 		  	= 1;
	const TYPE_BAR 		  	= 2;
	const TYPE_LAYERED_LINE = 3;
	
	const YAXIS_INTEGER = 0;
	const YAXIS_DECIMAL = 1;
	
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var array
	 */
	private $data;
	/**
	 * @var string
	 */
	private $title = '';
	/**
	 * @var string
	 */
	private $yTitle = '';
	/**
	 * @var string
	 */
	private $valueType = '';
	/**
	 * @var integer
	 */
	private $chartType;
	/**
	 * @var integer
	 */
	private $counterId;
	
	/**
	 * @var integer
	 */
	private $yAxisType = self::YAXIS_DECIMAL;
	
	public function __construct($data) {
		$this->data = $data;
	}
	
	/**
	 * @param integer $type
	 * @return \Statistics\Container
	 */
	public function setChartType($type) {
		$this->chartType = $type;
		return $this;
	}
	
	/**
	 * @param integer $type
	 * @return \Statistics\Container
	 */
	public function setYAxisType($type = self::YAXIS_DECIMAL) {
		$this->yAxisType = $type;
		return $this;
	}

	/**
	 * @param integer $counterId
	 * @return \Statistics\Container
	 */
	public function setCounterId($counterId) {
		$this->counterId = $counterId;
		return $this;
	}

	/**
	 * @param array $data
	 * @return \Statistics\Container
	 */
	public function setData($data) {
		$this->data = $data;
		return $this;
	}
	
	/**
	 * @param string $title
	 * @return \Statistics\Container
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}	
	
	/**
	 * @param string $title
	 * @return \Statistics\Container
	 */
	public function setYTitle($title) {
		$this->yTitle = $title;
		return $this;
	}
	
	/**
	 * @param string $title
	 * @return \Statistics\Container
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @param string $title
	 * @return \Statistics\Container
	 */
	public function setValueType($type) {
		$this->valueType = $type;
		return $this;
	}
	
	/**
	 * @return integer
	 */
	public function getChartType() {
		return $this->chartType;
	}
	
	/**
	 * @return array:
	 */
	public function getData() {
		return $this->data;
	}
	
	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * @return string
	 */
	public function getYTitle() {
		return $this->yTitle;
	}
	
	/**
	 * @return integer
	 */
	public function getYAxisType() {
		return $this->yAxisType;
	}
	
	/**
	 * @return string
	 */
	public function getValueType() {
		return $this->valueType;
	}

	/**
	 * @return integer $counterId
	 */
	public function getCounterId() {
		return $this->counterId;
	}
}