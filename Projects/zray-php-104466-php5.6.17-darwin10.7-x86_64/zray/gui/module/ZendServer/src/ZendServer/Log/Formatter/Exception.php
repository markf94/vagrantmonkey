<?php
namespace ZendServer\Log\Formatter;

use Zend\Log\Formatter\Simple;

class Exception extends Simple {
	/* (non-PHPdoc)
	 * @see Zend\Log\Formatter.Simple::format()
	 */
	public function format($event)
	{
		if (isset($event['info']) && (is_array($event['info']))) {
			return vsprintf(parent::format($event), $event['info']);
		} elseif (isset($event['info']) && ($event['info'] instanceof \Exception)) {
			$content = 	parent::format($event).
			'Exception of type \'' . get_class($event['info']) . '\': ' .
			$event['info']->getMessage();
			return $content;
		}
		return parent::format($event);
	}
}

