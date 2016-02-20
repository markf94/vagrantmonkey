<?php

namespace DeploymentLibrary\Prerequisites\Validator\Dependents;

class HasDependentsLibraryVersion extends HasDependentsAbstract {

	/**
	 * (non-PHPdoc)
	 * @see \DeploymentLibrary\Prerequisites\Validator\Dependents\HasDependentsAbstract::breaksDependents()
	 * @param integer $libVersionId
	 */
	public function breaksDependents($libVersionId, &$brokenPlugin) {
		$library = $this->getLibrariesMapper()->getLibraryById($this->getLibrariesMapper()->getLibraryIdByLibraryVersionId($libVersionId));

        $libraries = $this->getLibrariesMapper()->getLibrariesByIds()->toArray(); // open for editing

        /// doctor the libraries list so that it seems this library or version is removed
        if (isset($libraries[$library->getLibraryId()]) && isset($libraries[$library->getLibraryId()]['versions'][$libVersionId])) {
            if (1 == count($libraries[$library->getLibraryId()]['versions'])) {
                // if this is the last and only version, remove the library completely
                $libraries[$library->getLibraryId()] = null;
                unset($libraries[$library->getLibraryId()]);
            } else {
                $libraries[$library->getLibraryId()]['versions'][$libVersionId] = null;
                unset($libraries[$library->getLibraryId()]['versions'][$libVersionId]);
            }
        }
		
		return $this->validateLibrariesDependents($libraries, $library->getLibraryName(), $brokenPlugin);
	}
}

