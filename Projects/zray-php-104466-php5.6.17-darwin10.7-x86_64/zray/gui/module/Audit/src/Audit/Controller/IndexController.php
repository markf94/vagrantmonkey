<?php
namespace Audit\Controller;

use Audit\Db\ProgressMapper,
	Audit\Db\Mapper,
	Audit\Forms\Settings;

use ZendServer\Mvc\Controller\ActionController,
	Application\Module,
	MonitorUi\Filter,
	Servers\Forms\AddServer,
	Zend\Config\Reader\Ini,
	Statistics\Model as StatisticsModel,
	Configuration\MapperExtensions as MapperExtensions,
	Configuration\MapperDirectives as MapperDirectives;
use Zend\View\Model\ViewModel;

class IndexController extends ActionController
{
	public function indexAction() {
		
		$auditView = new ViewModel();
		$auditView->setVariable('perPage', Module::config('list', 'resultsPerPage'));
		
		$history = $this->getAuditSettingsMapper()->getHistory();
		$email = $this->getAuditSettingsMapper()->getEmail();
		$scriptUrl = $this->getAuditSettingsMapper()->getScriptUrl();
		
		$form = new Settings();
		$form->get('email')->setAttribute('value', $email);
		$form->get('callbackUrl')->setAttribute('value', $scriptUrl);

		$dictionary = new \Audit\Dictionary();
		$progressMapper = new \Audit\Db\ProgressMapper();
		$auditView->setVariable('internalFilters', array(
		   	'auditGroups' => array('name' => 'auditGroups', 'label' => _t('Operations'), 'options' => $dictionary->getAuditTypeGroups()),
		    'outcome' => array('name' => 'outcome', 'label' => _t('Outcome'), 'options' => $progressMapper->getProgressStrings()
		)));
		
		$auditView->setVariable('externalFilters', array(
            array(
                'name' => 'timeRange', 
                'label' => 'Filter audit by time range: ', 
                'options' => $this->getTimeRange(), 
                'extra' => $this->getTimeRanges()
            )
        ));
		
		$mapper = $this->getLocator('ZendServer\Filter\Mapper'); /* @var $mapper \ZendServer\Filter\Mapper */
		$existingFilters = array();
		foreach ($mapper->getByType('audit') as $filter) { /* @var $filter \ZendServer\Filter\Filter */
		    $existingFilters[$filter->getName()] = array(
                'id' => $filter->getId(), 
                'name' => $filter->getName(), 
                'custom' => $filter->getCustom(), 
                'data' => $filter->getData()
            );
		}
		
		$auditView->setVariable('existingFilters', $existingFilters);
		
		$auditView->setVariable('settingsForm', $form);
		$auditView->setVariable('history', $history);					
		$auditView->setVariable('pageTitle', 'Audit Trail');
		$auditView->setVariable('pageTitleDesc', ''); /* Daniel */
		
		return $auditView;
	}
	
	public function exportAction() {
	    $this->response = $this->forward()->dispatch('AuditWebAPI-1_3', array('action' => 'auditExport')); /* @var $auditView \Zend\View\Model\ViewModel */
	    $this->getEvent()->setParam('do-not-compress', true);
	    return $this->getResponse();
	}
	
	private function getTimeRange() {
	    return array (
	        'all' => _t ( 'All' ),
	        'day' => _t ( '24 Hours' ),
	        'week' => _t ( 'Week' ),
	        'month' => _t ( 'Month' ),
	    );
	}
	
	private function getTimeRanges() {
		
		$timeRangesArray = array('all' => array());
		$timeRangesArray['week'] = array(date('m/d/Y H:i', time() - 7*24*60*60) , date('m/d/Y H:i'), time() - 7*24*60*60, time());
		$timeRangesArray['month'] = array(date('m/d/Y H:i', strtotime('-1 month')), date('m/d/Y H:i'), strtotime('-1 month'), time());
		$timeRangesArray['day'] = array(date('m/d/Y H:i', time() - 24*60*60), date('m/d/Y H:i'), time() - 24*60*60, time());
		
		return $timeRangesArray;
	}
}