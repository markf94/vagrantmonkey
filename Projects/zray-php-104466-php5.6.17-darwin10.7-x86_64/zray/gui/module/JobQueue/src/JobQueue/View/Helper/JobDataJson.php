<?php
namespace JobQueue\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Module,
Deployment\Model;

class JobDataJson extends AbstractHelper {
	
	/**
	 * @param array $job
	 * @return string
	 */
	public function __invoke($job) {
	
		$vars = isset($job['vars']) ? $job['vars'] : '';
		$vars = htmlentities($vars);
		$vars = str_replace(array("\r", "\r\n"), '', $vars);
		$vars = str_replace(array("\r", "\r\n", "\n"), '\n', $vars);
		
		$output = isset($job['output']) ? $job['output'] : '';
		$output = htmlentities($output);
		$output = str_replace(array("\r", "\r\n"), '', $output);
		$output = str_replace(array("\r", "\r\n", "\n"), '\n', $output);
		
		$httpHeaders = isset($job['http_headers']) ? $job['http_headers'] : '';
		$httpHeaders = htmlentities($httpHeaders);
		$httpHeaders = str_replace(array("\r", "\r\n"), '', $httpHeaders);
		$httpHeaders = str_replace(array("\r", "\r\n", "\n"), '\n', $httpHeaders);
		
		$error = isset($job['error']) ? $job['error'] : '';
		$error = htmlentities($error);
		$error = str_replace(array("\r", "\r\n"), '', $error);
		$error = str_replace(array("\r", "\r\n", "\n"), '\n', $error);
		
	    return $this->getView()->json(array(
	    		"jobId" => $job["id"],
	    		"vars" => $vars,
	    		"output" => $output,
	    		"httpHeaders" => $httpHeaders,
	    		"error" => $error,
	    ));
	}
}

