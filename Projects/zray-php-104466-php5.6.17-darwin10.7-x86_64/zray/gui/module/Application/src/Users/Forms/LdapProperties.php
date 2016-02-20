<?php
namespace Users\Forms;

use Zend\Form\Fieldset;

use Zend\Ldap\Ldap;

use Zend\Form;
use Application\Module;
use Zend\Validator\Regex as Regex;
use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;

class LdapProperties extends Form\Form {
	
	public function __construct($options = null) {
		parent::__construct($options);
		
		$ldap = new Fieldset('ldap');
		$this->add($ldap);
		
		$ldap->add(array(
				'name' => 'host',
				'options' => array(
						'label' => _t('LDAP Host'),
				),
				'attributes' => array(
						'id' => 'ldap-host',
						'placeholder' => _t('e.g. auth.mine.domain'),
				)));
		
		$ldap->add(array(
				'name' => 'port',
				'options' => array(
				),
				'attributes' => array(
						'id' => 'ldap-port',
						'placeholder' => _t('e.g. 389'),
						'value' => 389
		)));

		$ldap->add(array(
				'name' => 'baseDn',
				'options' => array(
					'label' => _t('Base DN'),
				),
				'attributes' => array(
						'id' => 'ldap-basedn',
						'placeholder' => _t('e.g. dc=localhost,dc=com'),
		)));

		$ldap->add(array(
				'name' => 'username',
				'options' => array(
					'label' => _t('Username DN'),
				),
				'attributes' => array(
						'id' => 'ldap-usernamedn',
						'autocomplete' => false,
						'placeholder' => _t('e.g. CN=administrator,CN=Users,DC=support,DC=net'),
		)));

		$ldap->add(array(
				'name' => 'password',
				'options' => array(
				'label' => _t('Directory Password'),
				),
				'type' => 'Zend\Form\Element\Password',
				'attributes' => array(
						'id' => 'ldap-password',
						'autocomplete' => false,
		)));

		$ldap->add(array(
				'name' => 'encryption',
				'options' => array(
					'value_options' => array(
						array(
							'attributes' => array('id' => 'ldap-use_ssl'),
							'label' => _t('Use SSL for the connection'),
							'value' => 'ssl',
						),
						array(
							'attributes' => array('id' => 'ldap-start_tls'),
							'label' => _t('Start TLS for the connection'),
							'value' => 'tls',
						),
						array(
							'attributes' => array('id' => 'ldap-no_encryption'),
							'label' => _t('Do not use encryption'),
							'value' => 'none',
						),
					),
					'label' => _t('Encryption Type')
				),
				'type' => 'Zend\Form\Element\Radio',
				
				'attributes' => array(
					'value' => array('none'),
						
		)));
		
		$ldap->add(array(
				'name' => 'accountCanonicalForm',
				'type' => 'Zend\Form\Element\Select',
				'options' => array(
					'label' => _t('Canonical Form'),
					'options' => array(
							(string)Ldap::ACCTNAME_FORM_USERNAME => _t('Username only'),
							(string)Ldap::ACCTNAME_FORM_BACKSLASH => _t('Backslash (<shortDomain>\\\\<user>)'),
							(string)Ldap::ACCTNAME_FORM_PRINCIPAL => _t('Principal (<user>@<domainName>)'),
					)
				),
				'attributes' => array(
						'id' => 'ldap-account_canonical_form',
						
		)));

		$ldap->add(array(
				'name' => 'accountDomainName',
				'options' => array(
				'label' => _t('Domain Name'),
				),
				'attributes' => array(
						'id' => 'ldap-account_domain_name',
						'autocomplete' => false,
						'placeholder' => _t('e.g. zend.net'),
				)));
		
		$ldap->add(array(
				'name' => 'accountDomainNameShort',
				'options' => array(
				'label' => _t('Short Domain Name'),
				),
				'attributes' => array(
						'id' => 'ldap-account_domain_name_short',
						'autocomplete' => false,
						'placeholder' => _t('e.g. zend'),
				)));
		
		$ldap->add(array(
				'name' => 'adminRoleGroup',
				'options' => array(
				'label' => _t('Administrator Group'),
				),
				'attributes' => array(
						'id' => 'ldap-admin_role_group',
						'autocomplete' => false,
						'placeholder' => _t('e.g. zend-admin-group'),
						'description' => _t('Be sure to enter the Active Directory group name that is to be assigned to administrators.
								It is highly recommended this group be assigned in your LDAP server before the change to extended authentication so that you may log in again immediately.'),
				)));
		
		$ldap->add(array(
				'name' => 'bindRequiresDn',
				'options' => array(
					'value_options' => array(
						array(
							'attributes' => array('id' => 'ldap-bindRequiresDn-false'),
							'label' => _t('Active Directory'),
							'value' => '0',
						),
						array(
							'attributes' => array('id' => 'ldap-bindRequiresDn-true'),
							'label' => _t('LDAP Server'),
							'value' => '1',
						),
					),
					'label' => _t('Server Type')
				),
				'type' => 'Zend\Form\Element\Select',
				
				'attributes' => array(
					'value' => array('1'),
					'description' => _t('Authentication on Active Directory is possible using only the user\'s supplied credentials. Other servers require a different authentication approach which uses the Username DN supplied previously.')
						
		)));
		
		$ldap->add(array(
				'name' => 'groupsAttribute',
				'options' => array(
						'label' => _t('Groups Attribute'),
				),
				'attributes' => array(
						'id' => 'ldap-groupsAttribute',
						'autocomplete' => false,
						'placeholder' => _t('e.g. memberof, groupScope, groupAttr, groupFilter'),
				)));
		
		$inputFactory = new Factory();
		$validators = $inputFactory->createInputFilter(array());
		$this->setInputFilter($validators);
		$this->prepare();
	}
}

