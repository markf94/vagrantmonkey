<?php
namespace ZendServer\Filter;

use Issue\Filter\Filter as IssueFilter,
JobQueue\Filter\Filter as JobFilter;

class Factory {
    public function getContainer(\ZendServer\Filter\Filter $filter) {
        switch($filter->getType()) {
			case \ZendServer\Filter\Filter::ISSUE_FILTER_TYPE:
				return new IssueFilter($filter->getData());
				break;
			case \ZendServer\Filter\Filter::JOB_FILTER_TYPE:
				return new JobFilter($filter->getData());
				break;
		}
    }
}