<?php

namespace Vhost\Form;

use Zend\Form\Form;
use Zend\Validator\Hostname;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\GreaterThan;
use Zend\Validator\LessThan;
use Zend\Validator\NotEmpty;
use WebAPI\Validator\InArray;
use ZendServer\Validator\File\Exists;
use Deployment\Validator\VirtualHostPort;

class Vhost extends Form implements InputFilterProviderInterface {
	public function prepareElements() {
		$this->add(array(
			'name' => 'vhostId',
			'options' => array(
			),
			'attributes' => array(
				'id' => 'vhost-id',
				'type'  => 'hidden',
			),
		));
		
		$this->add(array(
			'name' => 'name',
			'options' => array(
			),
			'attributes' => array(
				'id' => 'vhost-name',
				'type'  => 'text',
				'placeholder' => 'e.g. www.mysite.com'
			),
		));
		
		$this->add(array(
			'name' => 'port',
			'options' => array(
					'allow_empty' => true
			),
			'attributes' => array(
				'id' => 'vhost-port',
				'type'  => 'Zend\Form\Element\Number',
				'placeholder' => 'e.g. 80',
				'value' => '80'
			),
		));
		
		$this->add(array(
				'name' => 'template',
				'options' => array(
				),
				'attributes' => array(
						'id' => 'vhost-template',
						'type'  => 'textarea',
				),
		));
		
		$this->add(array(
				'name' => 'sslEnabled',
				'type'  => 'checkbox',
				'options' => array(
						'checked_value' => 'TRUE',
            			'unchecked_value' => 'FALSE',
				),
				'attributes' => array(
						'id' => 'ssl-enabled',
				),
		));
		$this->add(array(
				'name' => 'sslAppName',
				'type'  => 'text',
				'options' => array(
				),
				'attributes' => array(
					'id' => 'ssl-app-name',
					'placeholder' => 'My Application Name',
				),
		));
		$this->add(array(
				'name' => 'sslCertificateChainPath',
				'type'  => 'text',
				'options' => array(
				),
				'attributes' => array(
						'id' => 'ssl-certificate-chain-path',
						'placeholder' => '/path/to/certificate/chain-file',
				),
		));
		$this->add(array(
				'name' => 'sslCertificateKeyPath',
				'type'  => 'text',
				'options' => array(
				),
				'attributes' => array(
						'id' => 'ssl-certificate-key-path',
						'placeholder' => '/path/to/certificate/key-file'
				),
		));
		$this->add(array(
				'name' => 'sslCertificatePath',
				'type'  => 'text',
				'options' => array(
				),
				'attributes' => array(
						'id' => 'ssl-certificate-path',
						'placeholder' => '/path/to/certificate/file'
				),
		));
		$this->add(array(
				'name' => 'forceCreate',
				'type'  => 'hidden',
				'attributes' => array(
					'id' => 'force-create',
					'value' => 'FALSE',
				),
		));
		

	}
	
	public function setData($data) {
		$group = array('name', 'port', 'template');
		if (isset($data['forceCreate']) && $data['forceCreate']) {
			$group[] = 'forceCreate';
		}
		if (isset($data['sslEnabled']) && $data['sslEnabled']) {
			$group[] = 'sslEnabled';
			if (isset($data['sslAppName']) && (! empty($data['sslAppName']))) {
				$group[] = 'sslAppName';
			} elseif (isset($data['sslCertificateChainPath']) && (! empty($data['sslCertificateChainPath']))) {
				array_push($group, 'sslCertificateKeyPath', 'sslCertificatePath', 'sslCertificateChainPath');
			} else {
				array_push($group, 'sslCertificateKeyPath', 'sslCertificatePath');
			}
		}
		$this->setValidationGroup($group);
		return parent::setData($data);
	}
	
	public function getInputFilterSpecification() {
		return array(
			'name' => array(
				'required' => true,
				'validators' => array(
					new Hostname(array('allow' => Hostname::ALLOW_ALL, 'useIdnCheck' => false, 'useTldCheck' => false))
				)
			),
			'port' => array(
				'required' => false,
				'validators' => array(
					new GreaterThan(array('min' => 0, 'inclusive' => true)),
					new LessThan(array('max' => pow(2,16), 'inclusive' => true)),
					new VirtualHostPort()
				)
			),
			'template' => array(
				'required' => false,
				'validators' => array(
						new NotEmpty()
				)
			),
			'sslEnabled' => array(
				'required' => true,
				'validators' => array(
					new InArray(array('haystack' => array(0,1)))
				)
			),
			'force' => array(
				'required' => true,
				'validators' => array(
					new InArray(array('haystack' => array(0,1)))
				)
			),
			'sslCertificateKeyPath' => array(
				'required' => true,
				'validators' => array(
				)
			),
			'sslCertificatePath' => array(
				'required' => true,
				'validators' => array(
				)
			),
		);
	}
}