<?php

class Profile_AccesserrorController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
       echo $_SERVER['REMOTE_ADDR'];
    }
}

