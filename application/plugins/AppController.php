<?php

class Plugin_AppController extends Zend_Controller_Plugin_Abstract {

    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        if ($request->getControllerName() != "ajax"):
            $session = new Zend_Session_Namespace("auth");
            $db = new Application_Model_DbTable_User();
            
            // ***** @Do: Starts to render login info to layout
            $layout = Zend_Layout::getMvcInstance();
            $view = $layout->getView();

            if (isset($session->uid)) {
                $isLoggedin = true;
                $stm = $db->find($session->uid);
                $view->uInfo = $stm->toArray();
            } else {
                $isLoggedin = false;
            }
            $view->isLoggedin = $isLoggedin;
            
            // ***** @Do: Get the messages of the particular user
            if($isLoggedin) {
                
            }
        endif;
    }

}
