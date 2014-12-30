<?php

class Vreasy_IndexController extends Vreasy_Rest_Controller
{
    public function indexAction()
    {
        $this->view->message = "It works!";
    }
}

