<?php

class Plugin_AppController extends Zend_Controller_Plugin_Abstract {

    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        if ($request->getControllerName() != "ajax"):
            // ***** @Do: register superadmin for first time
            $userDb = new Application_Model_DbTable_User();
            $user = $userDb->select()
                    ->from($userDb)
                    ->where("role = ?", "super")
                    ->query()
                    ->fetchAll();
            if (count($user) == 0) {
                Zend_Registry::set("superadmin", array("isset" => false));
                $url = ROOT_DOMAIN . "/admin/register";
                if ($_SERVER['REQUEST_URI'] != "/iknowu/public/admin/register" && $_SERVER['REQUEST_URI'] != "/iknowu/public/admin/registersubmit") {
                    header("Location: {$url}");
                    return;
                }
            } else {
                Zend_Registry::set("superadmin", array("isset" => true));
            }

            $session = new Zend_Session_Namespace("auth");
            $db = Zend_Registry::get("db");

            // ***** @Do: Starts to render login info to layout
            $layout = Zend_Layout::getMvcInstance();
            $view = $layout->getView();

            if (isset($session->uid)) {
                $isLoggedin = true;
                $stm = $userDb->find($session->uid);
                $user = $stm->toArray();
                
                $session->role = $user[0]["role"];
                $view->role = $user[0]["role"];
                $view->uInfo = $user;
            } else {
                $view->role = "";
                $isLoggedin = false;
            }
            $view->isLoggedin = $isLoggedin;
            
            // ***** @Do: check if this person is admin
            

            // ***** @Do: Get the updates amount of the particular user
            if ($isLoggedin) {
                $sql = $db->query("SELECT count(fromuid) AS total FROM message WHERE touid='{$session->uid}' AND type='update' AND seen=0 ");
                $result = $sql->fetchAll();
                $view->totalUpdate = $result[0]["total"];
            }
            
            // ***** @Do: Get the unread messages
            // ***** @Do: Get the updates amount of the particular user
            if ($isLoggedin) {
                $sql = $db->query("SELECT count(fromuid) AS total FROM message WHERE touid='{$session->uid}' AND type='message' AND seen=0 ");
                $result = $sql->fetchAll();
                $view->totalMessage = $result[0]["total"];
            }
        endif;
    }

}
