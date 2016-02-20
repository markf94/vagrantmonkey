<?php
namespace DevBar\Producer;

use Zend\View\Model\ViewModel;
use DevBar\Listener\AbstractDevBarProducer;
use Application\Module;
use ZendServer\Log\Log;

class LogEntries extends AbstractDevBarProducer
{
	
	/**
     * @return \Zend\View\Model\ViewModel
     */
    public function __invoke() {
    	
    	// get the default `error reporting` directive
    	
    	$directives = Module::serviceManager()->get('Configuration\MapperDirectives');
    	$directive = $directives->getDirective('error_reporting');
    	$errorReportingValue = $directive->getFileValue();
    	
    	$finalErrorReportingStatus = error_reporting();
    	
        $viewModel = new ViewModel(array(
        	'errorReporting' => $this->getAllowedErrorsList($errorReportingValue),
        	'showSilencedLogs' => Module::config()->get('zray')->zend_gui->showSilencedLogs ? 1 : 0,
         	'backtraceEnabled' => $this->directivesMapper->getDirectiveValue('zray.collect_backtrace'),
         	'producerEnabled' => $this->directivesMapper->getDirectiveValue('zray.collect_errors'),
            'azure' => isAzureEnv(),
		    'zrayStandalone' => isZrayStandaloneEnv(),
        ));
        $viewModel->setTemplate('dev-bar/components/log-entries');
        return $viewModel;
    }
    
    /**
     * 
     * @param string $errorReporting
     * @return array
     */
    protected function getAllowedErrorsList($errorReporting) {
    	// get error reporting number from the $errorReporting string parameter
    	$currentErrorReportingStatus = error_reporting();
    	eval('error_reporting('.$errorReporting.');');
    	$newErrorReportingStatus = error_reporting();
    	error_reporting($currentErrorReportingStatus);
    	
    	// list all available error reporting constants
    	$errorlevels = array(
    			1 => 'E_ERROR',
    			2 => 'E_WARNING',
    			4 => 'E_PARSE',
    			8 => 'E_NOTICE',
    			16 => 'E_CORE_ERROR',
    			32 => 'E_CORE_WARNING',
    			64 => 'E_COMPILE_ERROR',
    			128 => 'E_COMPILE_WARNING',
    			256 => 'E_USER_ERROR',
    			512 => 'E_USER_WARNING',
    			1024 => 'E_USER_NOTICE',
    			2048 => 'E_STRICT',
    			4096 => 'E_RECOVERABLE_ERROR',
    			8192 => 'E_DEPRECATED',
    			16384 => 'E_USER_DEPRECATED',
    	);
    	
    	// get list of all allowed errors
    	$allowedErrors = array();
    	foreach($errorlevels as $errorNumber => $errorConstantName) {
    		if ($errorNumber & $newErrorReportingStatus) {
    			$allowedErrors[] = $errorConstantName;
    		}
    	}
    	return $allowedErrors;
    }

}


