<?php

defined( '_JEXEC' ) or die( 'Unauthorized Access' );
jimport('joomla.application.component.controller');


class TestCompController extends JControllerLegacy{

    public function display($cachable = false, $urlparams = array()){
        JToolBarHelper::Title('TEST COMPONENT MAIN');
        echo JText::_('COM_TESTCOMP_DISPLAY');
    }

    function create(){
        JToolBarHelper::Title('TEST COMPONENT CREATE');
        echo "      Create whatever you want!!!";
    }

    function delete(){
        JToolBarHelper::Title('TEST COMPONENT DELETE');
        $id = JRequest::getInt('id', 0);
    echo "You want to delete $id";
    }
}
