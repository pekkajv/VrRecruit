<?php

class Vreasy_Rest_Controller extends Zend_Controller_Action
{
    protected $authEnabled = true;
    protected $jsonEnabled = true;
    protected $xmlEnabled = false;
    protected $user;

    public function init()
    {
        $contextSwitch = $this->_helper->getHelper('contextSwitchExt');
        if($this->jsonEnabled) {
            $contextSwitch->addActionContext(
                $this->getRequest()->getActionName(), 'json'
            )->initContext();
        }
        if($this->xmlEnabled) {
            $contextSwitch->addActionContext(
                $this->getRequest()->getActionName(), 'xml'
            )->initContext();
        }
    }

    public function preDispatch()
    {
        $this->view->errors = [];
    }

    public function skipAuth()
    {
        $this->authEnabled = false;
    }

    public function disableJson()
    {
        $this->jsonEnabled = false;
    }

    public function enableXml()
    {
        $this->disableJson();
        $this->xmlEnabled = true;
    }
}
