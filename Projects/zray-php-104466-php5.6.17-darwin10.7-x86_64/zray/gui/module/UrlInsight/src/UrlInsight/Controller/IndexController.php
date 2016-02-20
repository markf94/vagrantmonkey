<?php
namespace UrlInsight\Controller;

use ZendServer\Mvc\Controller\ActionController;
use Application\Module;
use ZendServer\Exception;
use Configuration\DirectiveContainer;

class IndexController extends ActionController
{
	public function indexAction() {
		if (function_exists('zray_disable')) {
			\zray_disable(true);
		}

		// calculate server's timezone offset
		$tz = @date_default_timezone_get();
		$dt = new \DateTime(null, new \DateTimeZone($tz));
		$tzOffset = $dt->getOffset();
		
		$viewModel = $this->forward()->dispatch('UrlInsightWebAPI-1_9', array(
			'action' => 'urlinsightGetUrls', 
		)); /* @var $viewModel \Zend\Http\PhpEnvironment\Response */
		$viewModel->setTemplate('url-insight/index/index');
		
		// get the applications list
		$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
		$applications = $deploymentModel->getAllApplicationsInfo();
		
		$directivesMapper = $this->getLocator()->get('Configuration\MapperDirectives'); /* @var $directivesMapper \Configuration\MapperDirectives */

		$viewModel->setVariables(array(
			'pageTitle' => 'URL Insight',
			'pageTitleDesc' => '', /* Daniel */
			'perPage' => 10,
			'timeout'	=> Module::config('studioIntegration', 'zend_gui', 'studioClientTimeout'),
		    'dailyInterval' => $directivesMapper->getDirectiveValue('zend_url_insight.daily_interval'),
		    'weeklyInterval' => $directivesMapper->getDirectiveValue('zend_url_insight.weekly_interval'),
		    'monthlyInterval' => $directivesMapper->getDirectiveValue('zend_url_insight.monthly_interval'),
			'applications' => $applications,
			'serverTimezoneOffset' => $tzOffset,
		));
		
		return $viewModel;
	}
		
	/**
	 * @param integer $issueId
	 * @throws WebAPI\Exception
	 */
	protected function validateRequestId($requestId) {
		$issueIdValidator = new \Zend\Validator\Digits();
		if (! $issueIdValidator->isValid($requestId)) {
			throw new Exception(
					_t('Parameter \'id\' must be an integer'),
					Exception::INVALID_PARAMETER);
		}
	}
}