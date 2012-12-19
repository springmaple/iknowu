<?php

class AdminController extends Zend_Controller_Action {

    public function init() {
        $authSession = new Zend_Session_Namespace("auth");
        if ($this->_getParam("action") != "register" && $this->_getParam("action") != "registersubmit") {
            if (!isset($authSession->uid) || $authSession->role != "super" && $authSession->role != "admin") {
                throw new Zend_Controller_Action_Exception("Page not found", 404);
            }
        }
    }

    public function indexAction() {
        // action body
    }

    public function registerAction() {
        // ******************************************************
        // ************ Variables Initializations ***************
        // ******************************************************
        $error = "";

        // ******************************************************
        // ************ Function Logics *************************
        // ******************************************************
        $superAdminConfig = Zend_Registry::get("superadmin");
        if ($superAdminConfig["isset"]) {
            $error = "Super administrator already exists.";
        } else {
            $this->_helper->layout->disableLayout();
        }

        // ******************************************************
        // ************ Returns and Assignment ******************
        // ******************************************************
        $this->view->error = $error;
    }

    public function registersubmitAction() {
        // ******************************************************
        // ************ Variables Initializations ***************
        // ******************************************************
        $error = "";
        $userDb = new Application_Model_DbTable_User;
        $authSession = new Zend_Session_Namespace("auth");
        $superAdminConfig = Zend_Registry::get("superadmin");
        $email = $this->_getParam("adminRegisterEmail");
        $password = $this->_getParam("adminRegisterNickname");
        $nickname = $this->_getParam("adminRegisterPassword");
        $bcrypt = new Bcrypt();

        // ******************************************************
        // ************ Function Logics *************************
        // ******************************************************
        if ($superAdminConfig["isset"]) {
            $error = "Super administrator already exists.";
        } else {
            $data = array("email" => $email, "password" => $bcrypt->customHashWith_MD5_Salt($password), "nickname" => $nickname, "role" => "super");
            $result = $userDb->insert($data);
            $authSession->uid = $result;
            if (!$result) {
                $this->_helper->layout->disableLayout();
                $error = "Unable to register super admin, please contact us.";
            }
        }

        // ******************************************************
        // ************ Returns and Assignment ******************
        // ******************************************************
        $this->view->error = $error;
    }

}

