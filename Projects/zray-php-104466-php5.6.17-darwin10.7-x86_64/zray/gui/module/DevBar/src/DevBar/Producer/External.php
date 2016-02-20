<?php
namespace DevBar\Producer;

use DevBar\Listener\AbstractDevBarProducer;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

class External extends AbstractDevBarProducer {
	
	/**
	 * @var string
	 */
	private $template = '';
	
	/**
	 * @var array
	 */
	private $params = array();
	
	/**
	 * @var array
	 */
	private $globalParams = array();
	
	/**
	 * @var \Zend\View\Renderer\PhpRenderer
	 */
	private $renderer;
	
	public function __construct($name, array $params, array $globalParams, PhpRenderer $renderer) {
		$this->template = $name;
		
		$params['name'] = $name;
		$params['extensionName'] = $globalParams['extensionName'];
		$this->params = $params;
		
		$this->globalParams = $globalParams;
		
		$this->renderer = $renderer;
	}
	
	/* (non-PHPdoc)
	 * @see \DevBar\Listener\AbstractDevBarProducer::__invoke()
	*/
	public function __invoke() {
		if (isset($this->params['display']) && $this->params['display'] === false) {
			$this->params['display'] = false;
		} else {
			$this->params['display'] = true;
		}
		
		$layout = new ViewModel();
		$layout->setVariable('params', $this->params);
		$layout->setVariable('global', $this->globalParams);
		$layout->setTemplate('layout/panel');
		
		if (! $this->params['display']) {
			return $layout;
		}
		
		$content = new ViewModel();
		$content->setVariable('params', $this->params);
		$content->setTemplate(strtolower($this->params['extensionName']) . '/' . $this->template);
		
		$layout->setVariable('content', $this->renderer->render($content));
		
		return $layout;
	}
	
	public function attach(\Zend\EventManager\EventManagerInterface $events)
	{
	    $sharedEvents      = $events->getSharedManager();
	    $this->listeners[] = $sharedEvents->attach('devbar', 'DevBarModules', $this, 101);
	}
}

