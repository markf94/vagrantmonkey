<?php

namespace StudioIntegration\Form\View\Helper;

use Zend\Form\Element\Select;

use Zend\Form\Element\Hidden;

use Zend\Form\ElementInterface;

use Zend\Form\View\Helper\FormHidden;

class HostsList extends FormHidden {
	public function __invoke(ElementInterface $element = null) {
		$elementString = parent::__invoke($element);
		$id = $element->getAttribute('id');
		
		$jsVariable = str_replace(array('-', '_'), '', $id);
		
		$hidden = new Hidden("{$id}-ipwidget");
		$hidden->setAttribute('id', "{$id}-ipwidget");
		// if the hidden still doesn't have value and the default values was set
		if ($element->getOption('default') && !$hidden->getValue()) {
			$hidden->setValue($element->getOption('default'));
		}
		$ipWidget = $this->getView()->formIpWidget($hidden);
		
		$cidrMask = new Select("{$id}-cidrmask");
		$cidrMask->setAttribute('id', "{$id}-cidrmask");
		$cidrMask->setOptions(array('options' => array(
			'32' => _t('Exact IP Address'),
			'24' => _t('10.0.0.*'),
			'16' => _t('10.0.*.*'),
			'8' => _t('10.*.*.*'),
		)));
		
		$cidrMaskString = $this->getView()->formSelect($cidrMask);
		return <<<HOSTSLIST
		
		<div class="hosts_list-controls">
			<div class="hosts_list-controls-ipwidget">
			{$ipWidget}
			</div>
			{$cidrMaskString}
			<button id="{$id}_btn" class="hosts_list-add_btn">+ Add</button>
		</div>
		<div class="hosts_list-list">
			{$elementString}
		</div>
		<script type="text/javascript">
			window.addEvent('load', function(){
				var {$jsVariable} = new valuesList($('{$id}'));
				$('{$id}_btn').addEvent('click', function(event){
					window.onbeforeunload = function(){
						return "Your changes have not been saved yet.";
					};

					var ipAddress = $("{$id}-ipwidget");
					event.preventDefault();
					if (ipAddress.value) {
						/// false response from add means that no operation was performed
						if (! {$jsVariable}.add("{ipAddress}/{cidrMask}".substitute(
								{'ipAddress': ipAddress.value, 'cidrMask': $('{$id}-cidrmask').value}))) {
								
							event.stop();
							event.stopPropagation();
							return false;
						}
					} else {
						event.stop();
						event.stopPropagation();
						return false;
					}
				});

				$("{$id}-ipwidget").retrieve('ipwidget').addEvent('ipValid', function(){
					$("{$id}_btn").set('disabled', false);
				});
				
				$("{$id}-ipwidget").retrieve('ipwidget').addEvent('ipInvalid', function(){
					$("{$id}_btn").set('disabled', true);
				});
			});
		</script> 
HOSTSLIST;
	}
}

