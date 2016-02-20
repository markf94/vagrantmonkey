<?php
namespace DevBar\Producer\Extension;

use Zend\View\Model\ViewModel;
use DevBar\Listener\AbstractDevBarProducer;
use DevBar\Db\ExtensionsMapper;

class DefaultTables extends AbstractDevBarProducer
{
	/**
	 * @var ExtensionsMapper
	 */
	private $extensions;
    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function __invoke() {
        $viewModel = new ViewModel();
        $viewModel->setTemplate('dev-bar/components/default-tables');
        return $viewModel;
    }
    
	/**
	 * @return ExtensionsMapper
	 */
	public function getExtensions() {
		return $this->extensions;
	}

	/**
	 * @param \DevBar\Db\ExtensionsMapper $extensions
	 */
	public function setExtensions($extensions) {
		$this->extensions = $extensions;
	}


  
}

