<?php
namespace ZendServer\Filter\View\Helper;

use ZendServer\Filter\Filter;
use Zend\View\Helper\AbstractHelper;

class FilterJson extends AbstractHelper {
    function __invoke(Filter $filter) {
        return $this->getView()->json(array(
            "id" => $filter->getId(),
            "name" => $filter->getName(),
            "type" => $filter->getType(),
            "custom" => $filter->getCustom(),
            "data" => $filter->getData(),
        ));
    }
}