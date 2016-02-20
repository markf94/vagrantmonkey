<?php

namespace ZendServer\View\Helper\Navigation;

use ZendServer\Log\Log;

use Zend\Navigation\Page\AbstractPage;

use Zend\View\Helper\Navigation\Menu as baseMenu;

class Menu extends baseMenu {
	public function htmlify(AbstractPage $page, $escapeLabel = true) {
		$html = parent::htmlify($page, $escapeLabel);
		$html = "$html<div class=\"subNavActiveIndicator\"></div>";
		return $html;
	}
}

