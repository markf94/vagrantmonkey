<?php
namespace EventsGroup\View\Helper;

use Zend\View\Helper\AbstractHelper;

class SelectAll extends AbstractHelper {
	
	/**
	 * @param string $id
	 * @return string
	 */
	public function __invoke($id) {
		return '<span class="select-all" onclick="fnSelect(\''.$id.'\')" >[select all]</span>';  
	}
}

