<?php
namespace WebAPI\Mvc\View\Http;
use Zend\Mvc\View\Http\RouteNotFoundStrategy as baseRouteNotFoundStrategy,
    Zend\Mvc\MvcEvent,
    Zend\View\Model as ViewModel;

use Zend\Stdlib\ResponseInterface as Response;

class RouteNotFoundStrategy extends baseRouteNotFoundStrategy {
	/**
	 * Template to use to report page not found conditions
	 *
	 * @var string
	 */
	protected $notFoundTemplate = 'error/index';
	
    /**
     * Create and return a 404 view model
     * 
     * @param  MvcEvent $e 
     * @return void
     */
    public function prepareNotFoundViewModel(MvcEvent $e)
    {
        $vars = $e->getResult();
        if ($vars instanceof Response) {
            // Already have a response as the result
            return;
        }

        $response = $e->getResponse();
        if ($response->getStatusCode() != 404) {
            // Only handle 404 responses
            return;
        }

        $exception = $e->getParam('exception');
        
        $response->setStatusCode(400);
        $model = new ViewModel\ViewModel();
        $model->setVariable('errorCode', 'unknownMethod');
        if ($exception instanceof \Exception) {
	        $model->setVariable('errorMessage', $exception->getMessage());
        } else {
        	$model->setVariable('errorMessage', 'Requested action was not found on this server');
        }
        $model->setTemplate($this->getNotFoundTemplate());

        // If displaying reasons, inject the reason
        $this->injectNotFoundReason($model, $e);

        // If displaying exceptions, inject
        $this->injectException($model, $e);

        // Inject controller if we're displaying either the reason or the exception
        $this->injectController($model, $e);

        $e->setResult($model);
    }
}