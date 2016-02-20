<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper,
	DevBar\SqlQueryContainer;

class ZrayFooter extends AbstractHelper {
	
	private $params = array();
	
	public function __invoke($params) {
		$this->params = $params;
		
		return <<<TEMPLATE
	</div>
</div>
TEMPLATE;
	}
}