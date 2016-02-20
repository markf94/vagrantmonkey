<?php

namespace Configuration\License;

use Configuration\License\License;

interface LicenseAwareInterface {
	public function setLicense(License $license);
}

