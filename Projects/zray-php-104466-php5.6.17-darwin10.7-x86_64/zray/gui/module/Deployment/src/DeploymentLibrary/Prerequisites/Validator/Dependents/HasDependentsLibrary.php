<?php

namespace DeploymentLibrary\Prerequisites\Validator\Dependents;

class HasDependentsLibrary extends HasDependentsAbstract {

	/**
	 * (non-PHPdoc)
	 * @see \DeploymentLibrary\Prerequisites\Validator\Dependents\HasDependentsAbstract::breaksDependents()
	 * @param integer $libId
	 */
	public function breaksDependents($libId, &$brokenPlugin) {
		
		$libraries = $this->getLibrariesMapper()->getLibrariesByIds()->toArray(); // open for editing
		$libName = $libraries[$libId];
		
		/// doctor the libraries list so that it seems this library or version is removed
		if (isset($libraries[$libId])) {
			$libraries[$libId] = null;
			unset($libraries[$libId]);
		}
		
		return $this->validateLibrariesDependents($libraries, $libName['libraryName'], $brokenPlugin);
	}
}

