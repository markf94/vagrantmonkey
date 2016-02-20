<?php

namespace Configuration\License;

class EmptyLicense extends License {
	public function __construct() {
		parent::__construct(array(
				'edition' => License::EDITION_EMPTY,
				'user_name' => '',
				'serial_number' => '',
				'expiration_date' => 0,
				'num_of_nodes' => 0,
				'signature_invalid' => true,
				'license_ok' => false,
				'is_first_license' => false,
				'evaluation' => false,
				'date_lock' => true,
				'is_cloud' => false,
		));
	}
}

