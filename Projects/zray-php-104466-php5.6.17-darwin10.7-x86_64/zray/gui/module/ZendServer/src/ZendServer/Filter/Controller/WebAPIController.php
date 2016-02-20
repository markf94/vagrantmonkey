<?php

namespace ZendServer\Filter\Controller;

use WebAPI\Exception;

use ZendServer\Mvc\Controller\WebAPIActionController;
use ZendServer\Filter\Filter;
use ZendServer\Filter\Factory;
use ZendServer\Filter\Mapper;
use WebAPI;
use ZendServer\Log\Log;
use Zend\Json\Json;

use Zend\View\Model\ViewModel;

class WebAPIController extends WebAPIActionController {
    
    protected $mapper = null;
    
    /**
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function filterSaveAction() {
        $this->isMethodPost();
        
        $params = $this->getParameters(array('data' => array(), 'id' => 0));
        $this->validateMandatoryParameters($params, array('type', 'name'));
        $id = $this->validateInteger($params['id'], 'id');
        
        if ($this->isNameExists($params['name'], $id, $params['type'])) {
            throw new WebAPI\Exception(_t("Parameter Name '%s' must be unique for type '%s'",array($params['name'], $params['type'])), WebAPI\Exception::INVALID_PARAMETER);
        }
        
        $filterMapper = $this->getMapper();
        $filterParams = array(
            'id' => $id,
            'name' => $params['name'], 
            'filter_type' => $params['type'], 
            'data' => Json::encode($params['data']),
        );
        $filter = new Filter($filterParams);
        $id = $filterMapper->upsert($filter);
        $filterList = $filterMapper->getById($id);
        
        $viewModel = new ViewModel(array('filter' => $filterList->current()));
        $viewModel->setTemplate('zend-server/web-api/filter');
        return $viewModel;
        
    }
    
    /**
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function filterDeleteAction() {
        $this->isMethodPost();
        
        $params = $this->getParameters();
        $this->validateMandatoryParameters($params, array('name'));
        $name = $this->validateString($params['name'], 'name');
        
        $filterMapper = $this->getMapper();
        $filterList = $filterMapper->getByName($name);
        if (0 == $filterList->count()) {
        	throw new Exception(_t('Requested filter was not found'), Exception::NO_SUCH_FILTER);
        }
        $filterMapper->deleteFilterByName($name);
        
        $viewModel = new ViewModel(array('filter' => $filterList->current()));
        $viewModel->setTemplate('zend-server/web-api/filter');
        return $viewModel;
        
    }
    
    public function filterGetByTypeAction() {
        $this->isMethodGet();
        
        $params = $this->getParameters();
        $this->validateMandatoryParameters($params, array('type'));
        
        $this->validateType($params['type']);
        $filterList = $this->getMapper()->getByType($params['type']);
        $viewModel = new ViewModel(array('filters' => $filterList));
        $viewModel->setTemplate('zend-server/web-api/filter-list');
        return $viewModel;
    }
    
    /**
     * 
     * @return \ZendServer\Filter\Mapper
     */
    private function getMapper() {
        if (! $this->mapper) {
            $this->mapper = $this->getLocator('ZendServer\Filter\Mapper');
        }
        return $this->mapper;
    } 
    
    private function isNameExists($name, $id, $type) {
        $mapper = $this->getMapper();
        $filter = $mapper->getByTypeAndName($type, $name);
        if (count($filter) > 0 && $id != $filter->current()->getId()) {
            return true;
        }
        return false;
    }
    
    private function validateType($type) {
        if (!in_array($type, Filter::$types)) {
            throw new WebAPI\Exception(_t("Parameter '%s' should contain one of the following values (%s) ",array('Type', implode(',', Filter::$types))), WebAPI\Exception::INVALID_PARAMETER);
        }
    }
}
