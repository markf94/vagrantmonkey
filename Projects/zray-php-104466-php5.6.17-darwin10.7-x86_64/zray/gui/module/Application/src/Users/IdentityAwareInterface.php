<?php

namespace Users;

interface IdentityAwareInterface {
	/**
	 * @param Identity $identity
	 */
	public function setIdentity(Identity $identity);
}