<?php

defined( '_JEXEC' ) or die( 'Unauthorized Access' );
jimport('joomla.application.component.controller');


class TestCompController extends JControllerLegacy{

    public function display($cachable = false, $urlparams = array()){
        echo JText::_('COM_TESTCOMP_DISPLAY');
    }

    function create(){
    echo "      Create whatever you want!!!";
    }

    function delete(){
    $id = JRequest::getInt('id', 0);
    echo "You want to delete $id";
    }
}
