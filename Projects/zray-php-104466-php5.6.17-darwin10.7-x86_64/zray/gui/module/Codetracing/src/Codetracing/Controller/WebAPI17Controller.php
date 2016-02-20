<?php
namespace Codetracing\Controller;

use Audit\Db\ProgressMapper;
use Audit\AuditTypeInterface;
use ZendServer\Mvc\Controller\WebAPIActionController;
use WebAPI;
use Zend\Http\Response;
use Zend\Validator\Regex;
use ZendServer\Log\Log;

class WebAPI17Controller extends WebAPIActionController
{
	
	/**
	 * @throws WebAPI\Exception
	 */
	public function codetracingDeleteAction() {
		$this->isMethodPost();
        $params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('traceFile'));
		$traceFile = $this->validateStringOrArray($params['traceFile'], 'traceFile');
		
		if (! is_array($traceFile)) {
			$traceFile = array($traceFile);
		}
		
		foreach ($traceFile as $key => $traceId) {
			$this->validateTraceFileId($traceId, "traceFile[{$key}]");
		}
		
		$traceTasksMapper = $this->getLocator()->get('Codetracing\Mapper\Tasks'); /* @var $traceFileMapper \Codetracing\TraceFilesMapper */
		$traceFileMapper = $this->getLocator()->get('Codetracing\TraceFilesMapper'); /* @var $traceFileMapper \Codetracing\TraceFilesMapper */

		$traces = array();
		
		$this->auditMessage(AuditTypeInterface::AUDIT_CODETRACING_DELETE, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array('traces' => $traceFile));

		/// stack codetracing objects and id's in bunches
		$limit = 100;
		$current = 0;
		$traceFileObjects = array();
		while($currentTraces = array_slice($traceFile, $current, $limit)) {
			
			$traceRows = $traceFileMapper->findCodetracesByIds($currentTraces);
			
			foreach ($traceRows as $traceFileRow) {
				$traces[] = $traceFileRow->getId();
				$traceFileObjects[$traceFileRow->getId()] = $traceFileRow;
			}
			
			$current += $limit;
		};
		
		if (count($traces) != count($traceFile)) {
		    $missingTraces = array_diff($traceFile, $traces);
    		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array('Missing traces' => $missingTraces));
    		$missingTracesImploded = implode(',', $missingTraces);
    		throw new WebAPI\Exception(_t("Requested traces were not found ({$missingTracesImploded})"), WebAPI\Exception::NO_SUCH_TRACE);
		}
		
		$traceTasksMapper->deleteTracesByIds($traces);
		
		$this->getResponse()->setStatusCode(Response::STATUS_CODE_202);
		return array('traces' => $traceFileObjects);
	}
	
	/**
	 * @param string $traceFileId
	 * @param string $parameterName
	 * @return string
	 * @throws WebAPI\Exception
	 */
	protected function validateTraceFileId($traceFileId, $parameterName) {
		$traceFileValidator = new Regex('#^\d+\.\d+\.\d+$#');
		if (! $traceFileValidator->isValid($traceFileId)) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be a valid trace file ID", array($parameterName)), WebAPI\Exception::INVALID_PARAMETER); 
		}
		return $traceFileId;
	}
}
