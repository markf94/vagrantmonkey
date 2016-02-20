<?php
namespace Deployment\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Application\Module,
	ZendServer\Exception;
use Prerequisites\Validator\Generator;

class AppPrerequisites extends AbstractHelper {
	
	/**
	 * @param array $messages
	 * @return string
	 */
	public function __invoke($messages) {
		$ret = '';
	
		if (0 < count($messages)) {
				
			$ret.= '<div>';
			if (isset($messages[Generator::VERSION_VALIDATOR_ELEMENT])) {
				$ret.= $this->getVersionMessages($messages[Generator::VERSION_VALIDATOR_ELEMENT]);
			}
			if (isset($messages[Generator::COMPONENT_VALIDATOR_ELEMENT])) {
				$ret.= $this->getComponentMessages($messages[Generator::COMPONENT_VALIDATOR_ELEMENT]);
			}
			if (isset($messages[Generator::EXTENSION_VALIDATOR_ELEMENT])) {
				$ret.= $this->getExtensionMessages($messages[Generator::EXTENSION_VALIDATOR_ELEMENT]);
			}
			if (isset($messages[Generator::DIRECTIVE_VALIDATOR_ELEMENT])) {
				$ret.= $this->getDirectivesMessages($messages[Generator::DIRECTIVE_VALIDATOR_ELEMENT]);
			}
			if (isset($messages[Generator::LIBRARY_VALIDATOR_ELEMENT])) {
				$ret.= $this->getLibraryMessages($messages[Generator::LIBRARY_VALIDATOR_ELEMENT]);
			}
			$ret.= '</div>';
		}
		return $ret;
	}
	
	/**
	 * @param array $elements
	 * @return string
	 */
	private function getDirectivesMessages(array $elements) {
		$ret = '<em>' . _t('Directives:') . '</em>';
		$ret.= '<ul class="prerequisites-list">';
	
		foreach ($elements as $element => $messages) {
			foreach ($messages as $code => $message) {
	
				$ret.= '<li class="' . $this->getCssClass($code) . '">';
				$ret.= '<span>' . $this->view->escapeHtml($message) . '</span>';
				$ret.= '</li>';
			}
		}
	
		$ret.= '</ul>';
	
		return $ret;
	}
	
	/**
	 * @param array $elements
	 * @return string
	 */
	private function getComponentMessages(array $elements) {
		$ret = '<em>' . _t('Zend Components:') . '</em>';
		$ret.= '<ul class="prerequisites-list">';
	
		foreach ($elements as $element => $messages) {
			foreach ($messages as $code => $message) {
				$ret.= '<li class="' . $this->getCssClass($code) . '">';
				$ret.= '<span>' . $this->view->escapeHtml($message) . '</span>';
				$ret.= '</li>';
			}
		}
	
		$ret.= '</ul>';
	
		return $ret;
	}
	
	/**
	 * @param array $elements
	 * @return string
	 */
	private function getVersionMessages(array $elements) {
		$ret = '<em>' . _t('System:') . '</em>';
		$ret.= '<ul class="prerequisites-list">';
	
		foreach ($elements as $element => $messages) {
			foreach ($messages as $code => $message) {
				$name = '';
				switch (trim($element)) {
					case Generator::PHP_ELEMENT:
						$name = _t('PHP');
						break;
					case Generator::ZEND_SERVER_ELEMENT:
						$name = _t('Zend Server');
						break;
					case Generator::ZEND_FRAMEWORK_ELEMENT:
						$name = _t('Zend Framework');
						break;
					case Generator::ZEND_FRAMEWORK_ELEMENT2:
						$name = _t('Zend Framework 2');
						break;
				    case Generator::PLUGIN_ELEMENT:
						$name = _t('Plugin');
						break;
				}
	
				if ('' !== $name) {
					$ret.= '<li class="' . $this->getCssClass($code) . '">';
					$ret.= '<span>' . $name . '&nbsp;' . $this->view->escapeHtml($message) . '</span>';
					$ret.= '</li>';
				}
			}
		}
	
		$ret.= '</ul>';
	
		return $ret;
	}
	
	/**
	 * @param array $elements
	 * @return string
	 */
	private function getLibraryMessages(array $elements) {
		$ret = '<em>' . _t('Libraries:') . '</em>';
		$ret.= '<ul class="prerequisites-list">';
	
		foreach ($elements as $element => $messages) {
			foreach ($messages as $code => $message) {
				$ret.= '<li class="' . $this->getCssClass($code) . '">';
				if (strstr($code, 'notValid')) { // Equals and Deplyed validator messgaes already has the element
					$ret.= '<span>' . $this->view->escapeHtml($message) . '</span>';
				} else {
					$ret.= '<span>' . $element . '&nbsp;' . $this->view->escapeHtml($message) . '</span>';
				}
				$ret.= '</li>';
			}
		}
	
		$ret.= '</ul>';
	
		return $ret;
	}
	
	/**
	 * @param array $elements
	 * @return string
	 */
	private function getExtensionMessages(array $elements) {
		$ret = '<em>' . _t('PHP Extensions:') . '</em>';
		$ret.= '<ul class="prerequisites-list">';
	
		foreach ($elements as $element => $messages) {
			foreach ($messages as $code => $message) {
				$ret.= '<li class="' . $this->getCssClass($code) . '">';
				$ret.= '<span>' . $this->view->escapeHtml($message) . '</span>';
				$ret.= '</li>';
			}
		}
	
		$ret.= '</ul>';
	
		return $ret;
	}
	
	private function getCssClass($code) {
		if (false === strpos($code, 'valid')) {
			return 'prerequisite-item-error';
		} else {
			return 'prerequisite-item-valid';
		}
	}
}