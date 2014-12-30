<?php
/*
// Controller for using Vreasy_Rest_Route instead of Zends own REST route.
*/
class Vreasy_Controller_Action_Helper_ContextSwitchExt extends Zend_Controller_Action_Helper_ContextSwitch {

    protected $_noRender = false;

    /**
     * JSON post processing
     *
     * JSON serialize view variables to response body
     *
     * @return void
     */
    public function postJsonContext()
    {
        if (!$this->getAutoJsonSerialization()) {
            return;
        }

        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $view = $viewRenderer->view;
        if ($view instanceof Zend_View_Interface && !$this->_noRender) {
            /**
             * @see Zend_Json
             */
            if(method_exists($view, 'getVars')) {
                require_once 'Zend/Json.php';
                $vars = $view->getVars();
                // Filter non empty variables from the view
                $vars = array_filter($vars, function($value) {
                    return !empty($value);
                });
                // Avoid having a associative array serialized
                $values = array_values($vars);
                $json = '';
                // When one variable set, then serialzie as a single object (instead of an array of one element)
                if(count($values) == 1) {
                    $json = Zend_Json::encode(array_pop($values));
                }
                else {
                    $json = Zend_Json::encode($vars);
                }

                $this->getResponse()->setBody($json);
            } else {
                require_once 'Zend/Controller/Action/Exception.php';
                throw new Zend_Controller_Action_Exception('View does not implement the getVars() method needed to encode the view into JSON');
            }
        }
    }

    public function setNoRender($noRender) {
        $this->_noRender = !!$noRender;
        return $this;
    }

    public function initContext($format = null)
    {
        $this->_currentContext = null;

        $request = $this->getRequest();
        if (method_exists($request, 'isXmlHttpRequest') &&
            $this->getRequest()->isXmlHttpRequest() && !$format)
        {
            $format = 'json';
        }
        if ($format == 'json') {
            $request->setParam('setVreasyJson', true);
        }
        return parent::initContext($format);
    }

    public function initJsonContext()
    {
        if (!$this->getAutoJsonSerialization()) {
            return;
        }
        $request = $this->getRequest();
        $request->setParam('setVreasyJson', true);
        parent::initJsonContext();
    }
}
