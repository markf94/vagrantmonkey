<?php
namespace JobQueue\Form;

use Zend\Form\Form;

class ImportForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('Import');
        
        $this->setAttribute('id', 'JobQueue_queues_import');
        $this->setAttribute('enctype', 'multipart/form-data');
        
        $this->add(array(
            'name' => 'file',
            'type' => 'Zend\Form\Element\File',
            'options' => array(
                'label' => _t('Import File'),
                'section' => 'Import',
                'section_description' => 'Import an exported .zip file containing queues. Queues with the same name are overridden.',
                'section_image' => '/images/settings/general-settings.png',
            ),
            'attributes' => array(
                'id' => 'JobQueue_queue_import_file',
                'title' => 'Select an exported ZIP file containing queues data.',
                'type' => 'file',
                'required' => true,
            )
        ));
        
        $this->add(array(
            'name' => 'delete_current',
            'type' => 'Zend\Form\Element\Checkbox',
            'options' => array(
                'section' => 'Import',
                'label' => _t('Delete current queues')
            ),
            'attributes' => array(
                'id' => 'JobQueue_queue_import_delete_current',
                'title' => 'Select to delete all the current queues before import. If left unselected, existing queues that do not exist in the imported data will be left untouched.',
                'type' => 'file',
                'required' => true,
            )
        ));
		
		$csrfContainer = new \Zend\Session\Container('zs_csrf');
		$this->add(array(
			'name' => 'access_token',
			'type' => 'Zend\Form\Element\Hidden',
			'options' => array(
                'section' => 'Import',
			),
			'attributes' => array(
				'value' => $csrfContainer->offsetGet('access_token')
			)
		));
		
   }
}