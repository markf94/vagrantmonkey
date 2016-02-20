<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper;
use DevBar\ExceptionsContainer;

class ExceptionsJson extends AbstractHelper {
	
	public function __invoke($exceptions) {
		$entries = array();
		foreach ($exceptions as $exception) {
			$entries[] = $this->exceptionInfo($exception);
		}

		$defaultValue = \Zend\Json\Json::$useBuiltinEncoderDecoder;
		\Zend\Json\Json::$useBuiltinEncoderDecoder = true;
		
		$json = $this->getView()->json($entries);
		
		\Zend\Json\Json::$useBuiltinEncoderDecoder = $defaultValue;
		
		return $this->getView()->json($entries);
	}
	
	private function exceptionInfo(ExceptionsContainer $exception) {
		return array(
			'id' => $exception->getId(),
			'requestId' => $exception->getRequestId(),
			'text' => $exception->getExceptionText(),
			'code' => $exception->getExceptionCode(),
			'className' => $exception->getExceptionClass(),
			'filename' => $exception->getFileName(),
			'line' => $exception->getLineNumber(),
			'backtraceId' => $exception->getBacktraceId(),
			'sequenceId' => $exception->getSequenceId(),
			'createdAt' => $this->getView()->webapidate($exception->getCreatedAt()),
			'createdAtTimestamp' => $exception->getCreatedAt(),
		);
	}
}