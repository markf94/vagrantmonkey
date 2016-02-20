<?php

namespace Audit\ExtraData;

/**
 * An interface for parsing data into an extraData array for use in displaying audit entries
 */
interface ParserInterface {
	/**
	 * @return array
	 */
	public function toArray();
}