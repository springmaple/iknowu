<?php

class ProfileController extends Zend_Controller_Action {

    public function init() {
        $session = new Zend_Session_Namespace("auth");
        // testing
        if (!isset($session->uid)) {
            $this->_helper->redirector->gotoRoute(array(
                'controller' => 'index',
                'action' => 'index'));
        } else {
            $user = new Application_Model_DbTable_User();
            $stm = $user->find($session->uid);
            $this->view->user = $stm->toArray();
        }
    }

    public function indexAction() {
        // ******************************************************
        // ************ Variables Initializations ***************
        // ******************************************************
        $session = new Zend_Session_Namespace("auth");
        $db = Zend_Registry::get("db");
        $userDb = new Application_Model_DbTable_User();
        $error = false;

        // ******************************************************
        // ************ Function Logics *************************
        // ******************************************************
        // ***** @Do: Get user
        $id = $session->uid;
        if ($this->_getParam("id") != "") {
            // check if the id is provided
            $id = $this->_getParam("id");
        }
        // validate if the id is existing user.
        if (empty($id)) {
            // if the id is empty here means the user is not logged in and id parameter is not provided as well
            $error = "No user id is specified.";
        }
        $stm = $userDb->find($id);
        if ($stm->count() != 1) {
            // the user does not exist
            $error = "The user does not exist.";
        }
        $user = $stm->toArray()[0];
        // ***** @Do: Get product uploaded by user
        $selectSQL = $db->select()
                ->from("get_available_product_view", array("pid", "pname", "uid", "date"))
                ->where("uid = ?", $user["uid"]);
        $results = $selectSQL->query();
        $products = $results->fetchAll();
        include_once("helper/imageHelper.php");
        $tmpAvailableProducts = $products;
        foreach ($tmpAvailableProducts as $key => $availableProduct) {
            $products[$key]['image'] = getFirstImage($availableProduct["pid"]);
        }

        // ******************************************************
        // ************ Returns and Assignment ******************
        // ******************************************************
        if ($error) {
            $this->view->error = $error;
        }
        if ($id === $session->uid) {
            $this->view->isMe = true;
        }
        if ($user["ban"]) {
            $this->view->isBanned = true;
        }
        $this->view->user = $user;
        $this->view->productNum = $results->rowCount();
        $this->view->products = $products;
    }

    public function editAction() {
        // ******************************************************
        // ************ Variables Initializations ***************
        // ******************************************************
        $alert = "";
        $session = new Zend_Session_Namespace("alert");

        // ******************************************************
        // ************ Function Logics *************************
        // ******************************************************
        if (isset($session->registration)) {
            $alert = "registration";
        } else if (isset($session->editPassword)) {
            $alert = "passwordChanged";
        }
        Zend_Session::namespaceUnset("alert");

        // ******************************************************
        // ************ Returns and Assignment ******************
        // ******************************************************
        $this->view->alert = $alert;
    }

    public function editpasswordAction() {
        $error["currPwd"] = "";
        $error["newPwd"] = "";
        $error["conNewPwd"] = "";
        $this->view->success = false;
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $currentPassword = $this->_getParam("currPwd");
            $newPassword = $this->_getParam("newPwd");
            $confirmNewPassword = $this->_getParam("conNewPwd");

            $this->view->currentPassword = $currentPassword;
            $this->view->newPassword = $newPassword;
            $this->view->confirmNewPassword = $confirmNewPassword;

            if (trim($currentPassword) == null || trim($newPassword) == null || trim($confirmNewPassword) == null) {
                $string = "This field cannot be empty";
                if (trim($currentPassword) == null)
                    $error["currPwd"] = $string;
                if (trim($newPassword) == null)
                    $error["newPwd"] = $string;
                if (trim($confirmNewPassword) == null)
                    $error["conNewPwd"] = $string;
            } elseif (trim($newPassword) != trim($confirmNewPassword)) {
                // Confirm password not match new password
                $error["conNewPwd"] = "Confirm password is not matching.";
            } else {
                $db = new Application_Model_DbTable_User();
                $session = new Zend_Session_Namespace("auth");
                $uid = $session->uid;
                $result = $db->find($uid);
                $bcrypt = new Bcrypt();
                if (!$bcrypt->check($currentPassword, $result[0]['password'])) {
                    // Current password is wrong
                    $error["currPwd"] = "Wrong password!";
                } else {
                    $data['password'] = $bcrypt->customHashWith_MD5_Salt($newPassword);
                    $where["uid = ?"] = $uid;
                    $success = $db->update($data, $where);
                    if ($success) {
                        $this->view->success = "true";
                    } else {
                        $this->view->success = "false";
                    }
                }
            }
        }
        $this->view->currPwdErr = $error["currPwd"];
        $this->view->newPwdErr = $error["newPwd"];
        $this->view->conNewPwdErr = $error["conNewPwd"];
    }

    public function deactivateAction() {
        $key = $this->_getParam("key");
        $session = new Zend_Session_Namespace("auth");
        if ($key != md5($session->uid)) {
            throw new Zend_Controller_Action_Exception("NotLogin", EXCEPTION_NO_LOGIN);
        } else {
            $db = new Application_Model_DbTable_User();
            $where["uid = ?"] = $session->uid;
            $db->delete($where);

            Zend_Session::namespaceUnset("auth");
            $this->view->isLoggedin = false;
            $this->view->uInfo = null;
        }
    }

}

