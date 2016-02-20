<?php

namespace ZendServer\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Michelf\Markdown;

class ParseMarkdown extends AbstractHelper {
	
	/**
	 * @var Markdown
	 */
	private $parser;
	
	public function __invoke($text) {
		return '<div class="markdown-wrapper">' . $this->getParser()->transform($text) . '</div>';
	}
	/**
	 * @return the $parser
	 */
	public function getParser() {
		if (is_null($this->parser)) {
			$this->parser = new Markdown();
		}
		return $this->parser;
	}

	/**
	 * @param \Michelf\Markdown $parser
	 */
	public function setParser($parser) {
		$this->parser = $parser;
	}

}

