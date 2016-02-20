<?php

namespace ZendServer\Filter;
use ZendServer\Set;
use Zend\Json\Json;

class FilterList extends Set implements \ArrayAccess, \Iterator, \Countable {
    
    /**
     * @var array
     */
    protected $items;
    /**
     * @var string
     */
    protected $hydrateClass = '\ZendServer\Filter\Filter';
    
    /**
     * @param array $items
     */
    public function __construct(array $items, $hydrateClass = '\ZendServer\Filter\Filter') {
        parent::__construct($items, $hydrateClass);
    }
}