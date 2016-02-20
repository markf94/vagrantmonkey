<?php

namespace Configuration\Controller;

use ZendServer\Mvc\Controller\ActionController,
	Application\Module,
	Configuration\Forms\MonitorCleanup,
	Configuration\Forms\MonitorDefaultEmail,
	Zend\Validator\GreaterThan,
	Zend\Validator\Digits;

use Zend\Form\Factory;

class SessionClusteringController extends ActionController {
    public function indexAction() {
    	$directives = $this->getDirectivesMapper();
    	$sessionHandler = $directives->getDirectiveValue('session.save_handler');
    	$extension = $this->getExtensionsMapper()->selectExtension('Zend Session Clustering');
    	$sessionClusteringActive = ($extension->getStatus() == 'Loaded');
    	$inCluster = Module::isCluster();
    	
    	$formFactory = new Factory();
    	$form = $formFactory->createForm(array(
							'elements' => array(
								array(
									'spec' => array(
										'name' => 'zend_sc.session_lifetime',
										'type' => 'Zend\Form\Element\Text',
										'attributes' => array(
											'size' => 4,
								))),
								array(
									'spec' => array(
										'name' => 'zend_sc.ha.cluster_members',
										'type' => 'Zend\Form\Element\Hidden',
									)),
								array(
									'spec' => array(
										'name' => 'zend_sc.ha.use_broadcast',
										'type' => 'Zend\Form\Element\Checkbox',
										'options' => array(
											'checked_value' => '0',
											'unchecked_value' => '1',
								))),
								array(
									'spec' => array(
										'name' => 'zend_sc.garbage_collection_delta',
										'type' => 'Zend\Form\Element\Text',
										'attributes' => array(
											'size' => 4,
								))),
				)));
    	
    	$directivesData = $directives->selectSpecificDirectives(array(
    				'zend_sc.garbage_collection_delta', 
    				'zend_sc.ha.use_broadcast', 
    				'zend_sc.ha.cluster_members', 
    				'zend_sc.session_lifetime', 
    			))->toArray();
    	
    	$directivesArray = array_combine(array_map(function($directive){
	    		return $directive['NAME'];
	    	}, $directivesData), array_map(function($directive){
	    		return $directive['DISK_VALUE'];
	    	}, $directivesData));

    	$directivesArray = is_array($directivesArray) ? $directivesArray : array();
	    $form->setData($directivesArray);
    	
    	return array(
			'pageTitle' => 'Session Clustering',
			 'pageTitleDesc' => '',  /* Daniel */
			 
	    			'clusterHandlerActive' => ($sessionHandler == 'cluster'),
	    			'sessionSaveHandler' => $sessionHandler,
    				'sessionClusteringActive' => $sessionClusteringActive,
    				'inCluster' => $inCluster,
    				'form' => $form
    			);
    }
}