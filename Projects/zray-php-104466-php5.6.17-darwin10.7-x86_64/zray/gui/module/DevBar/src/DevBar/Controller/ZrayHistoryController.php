<?php

namespace DevBar\Controller;

use ZendServer\Mvc\Controller\ActionController;
use Zend\View\Helper\ViewModel;
use ZendServer\Log\Log;
use DevBar\Filter\Dictionary;
use Acl\License\Exception;

class ZrayHistoryController extends ActionController {

	public function indexAction() {
	   if (function_exists('zray_disable')) {
			\zray_disable(true);
		}
		
		$disablePage = false;
		if (isAzureEnv()) {
		    if (function_exists('zray_get_azure_license')) {
		        $license = \zray_get_azure_license();
		        if ($license != ZRAY_AZURE_LICENSE_STANDARD) { // if license not standard - disable page
		          $disablePage = true;
		        }
		    } else { // license not found - disable page
		        $disablePage = true;
		    }
		}
		
		$mapper = $this->getLocator('ZendServer\Filter\Mapper'); /* @var $mapper \ZendServer\Filter\Mapper */
		$existingFilters = array();
		foreach ($mapper->getByType('zrays') as $filter) { /* @var $filter \ZendServer\Filter\Filter */
		    $existingFilters[$filter->getName()] = array(
                'id' => $filter->getId(), 
                'name' => $filter->getName(), 
                'custom' => $filter->getCustom(), 
                'data' => $filter->getData()
            );
		}
		
		// filters area
		/* @var $historyDictionary \DevBar\Filter\Dictionary */
		$historyDictionary = $this->getLocator()->get('DevBar\Filter\Dictionary');
	
		return array(
		    'internalFilters' => array(
		        'severity' => array('name' => 'severity', 'label' => _t('Severity'), 'options' =>  $historyDictionary->getSeverityDictionary()),
		        'method' => array('name' => 'method', 'label' => _t('Method'), 'options' => $historyDictionary->getMethodDictionaryForFiltering()),
		        'response' => array('name' => 'response', 'label' => _t('Response'), 'options' => Dictionary::getResponseDictionary()
		        )),
		    'externalFilters' => array(
		        array(
		            'name' => 'timeRange',
		            'label' => 'Filter zray by time range: ',
		            'options' => $this->getTimeRange(),
		            'extra' => $this->getTimeRanges()
		        )),
		    'existingFilters' =>  $existingFilters,
		    'pageTitle' => 'Z-Ray History',
			'pageTitleDesc' => '',  /* Daniel */
		    'zrayEnabled' => $this->getDirectivesMapper()->getDirectiveValue('zray.enable'), // check config if zray enabled
			'disablePage' => $disablePage,
		    'zrayHeader' => $this->getZrayHeader(),
		    'zrayFooter' => $this->getZrayFooter(),
		);
	
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
	
	protected function getZrayHeader() {
	   
	    // locate footer script
	    $headerScript = getCfgVar('zend.install_dir') . DIRECTORY_SEPARATOR . 'share' . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'devbar_header.html';
	    $headerScript = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $headerScript);
	    if (!file_exists($headerScript)) {
	        throw new Exception('Z-Ray script "'.$headerScript.'" was not found');
	    }
	
	    // take footer script content
	    $zrayHeader = file_get_contents($headerScript);
	    if ($zrayHeader === false) {
	        throw new Exception('Cannot read Z-Ray script "'.$headerScript.'"');
	    }
	
	    return $zrayHeader;
	}
	
	protected function getZrayFooter() {
	    
	    //locate footer script
	    $footerScript = getCfgVar('zend.install_dir') . DIRECTORY_SEPARATOR . 'share' . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'devbar_footer.html';
	    $footerScript = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $footerScript);
	    if (!file_exists($footerScript)) {
	        throw new Exception('Z-Ray script "'.$footerScript.'" was not found');
	    }
	
	    // take footer script content
	    $footerContent = file_get_contents($footerScript);
	    if ($footerContent === false) {
	        throw new Exception('Cannot read Z-Ray script "'.$footerScript.'"');
	    }
	
	    // replace the placeholders within the footer scripts
	    $viewRenderer = $this->getLocator()->get('ViewRenderer');
	    $footerContent = str_replace('$(ZENDSERVER_UI)', $viewRenderer->serverUrl() . '/ZendServer', $footerContent);
	    $footerContent = str_replace('$(DEVBAR_ACCESS_TOKEN)', '', $footerContent);
	    $footerContent = str_replace('Z-Ray/iframe?', 'Z-Ray/iframe?embedded=0&historyEmbedded=1&', $footerContent);
	    
	    // remove the right devbar zend server logo and setting menu icon
	    $footerContent = preg_replace('^<div class="zdb-toolbar-preview" title="Zend Server">(.*)<div class="zdb-toolbar-detail zdb-toolbar-detail-redundant">^','<div class="zdb-toolbar-detail zdb-toolbar-detail-redundant">', $footerContent);
	    $footerContent = preg_replace('^<a class="zdf-toolbar-hide-button" title="Close Toolbar"(.*)</a>?^','', $footerContent);
	    
	    return $footerContent;
	}
}