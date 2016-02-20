<?php

namespace Configuration\License;

use ZendServer\Log\Log;


class LicenseChangeContainer {

	const EVALUATION_TO_EVALUATION = 0;
	const EVALUATION_TO_COMMERCIAL = 1;
	const COMMERCIAL_TO_EVALUATION = 2; // not common, but still might occur manually
	const COMMERCIAL_TO_COMMERCIAL = 3;

	const EDITION_DOWNGRADE = -1;
	const EDITION_NO_CHANGE = 0;
	const EDITION_UPGRADE = 1;

	/**
	 * @var integer
	 */
	private $evaluationChange;

	/**
	 * @var integer
	 */
	private $editionChange;

	/**
	 * @var string
	 */
	private $currentEdition;

	/**
	 * @var string
	 */
	private $newEdition;

	/**
	 * @var string
	 */
	private $currentEvaluation;
	
	/**
	 * @var string
	 */
	private $newEvaluation;

	/**
	 * @return $evaluation
	 */
	public function getEvaluationChange() {
		return $this->evaluationChange;
	}

	/**
	 * @param integer $evaluation
	 */
	public function setEvaluationChange($evaluation) {
		$this->evaluationChange = $evaluation;
	}

	/**
	 * @return $editionChange
	 */
	public function getEditionChange() {
		return $this->editionChange;
	}
	
	/**
	 * @param integer $editionChange
	 */
	public function setEditionChange($editionChange) {
		return $this->editionChange = $editionChange;
	}

		
	/**
	 * @return the $currentEdition
	 */
	public function getCurrentEdition() {
		return ucfirst(strtolower($this->currentEdition));
	}
	
	/**
	 * @param string $currentEdition
	 */
	public function setCurrentEdition($currentEdition) {
		$this->currentEdition = $currentEdition;
	}

	/**
	 * @return the $newEdition
	 */
	public function getNewEdition() {
		return ucfirst(strtolower($this->newEdition));
	}	
	
	/**
	 * @param string $newEdition
	 */
	public function setNewEdition($newEdition) {
		$this->newEdition = $newEdition;
	}
	

	/**
	 * @return the $currentEvaluation
	 */
	public function getCurrentEvaluation() {
		return $this->currentEvaluation;
	}
	
	/**
	 * @param string $currentEvaluation
	 */
	public function setCurrentEvaluation($currentEvaluation) {
		$this->currentEvaluation = $currentEvaluation;
	}
	
	/**
	 * @return $newEvaluation
	 */
	public function getNewEvaluation() {
		return $this->newEvaluation;
	}
	
	/**
	 * @param string $newEvaluation
	 */
	public function setNewEvaluation($newEvaluation) {
		$this->newEvaluation = $newEvaluation;
	}
}