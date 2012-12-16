<?php

class RegController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        $session = new Zend_Session_Namespace('auth');
        $session2 = new Zend_Session_Namespace("alert");

        $bcrypt = new Bcrypt();
        $password = $this->getParam("regPassword");
        $passwordHash = $bcrypt->customHashWith_MD5_Salt($password);

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $data = array("email" => $this->getParam("regEmail"),
                "password" => $passwordHash,
                "nickname" => $this->getParam("regNick"));

            $user = new Application_Model_DbTable_User();
            $id = $user->insert($data);
            if ($id) {
                $session->uid = $id;
                $session2->registration = true;
            }
        }

        if (isset($session->uid)) {
            $this->_helper->redirector->gotoRoute(array(
                'controller' => 'profile',
                'action' => 'edit'));
        }
    }

    public function forgotpasswordAction() {
        // ******************************************************
        // ************ Variables Initializations ***************
        // ******************************************************
        $viewType = "submit";
        $token = $this->_getParam("token");
        $uid = $this->_getParam("uid");
        $userDb = new Application_Model_DbTable_User();

        // ******************************************************
        // ************ Function Logics *************************
        // ******************************************************
        if ($token == "" || $uid == "") {
            $viewType = "submit";
        } else {
            $viewType = "error";
        }
        $user = $userDb->find($uid);
        if ($user->count() == 1) {
            $users = $user->toArray()[0];
            if ($users["password"] == $token) {
                $viewType = "change";
            }
        }

        // ******************************************************
        // ************ Returns and Assignment ******************
        // ******************************************************
        $this->view->view = $viewType;
        $this->view->uid = $uid;
    }

    public function mailsentAction() {
        // ******************************************************
        // ************ Variables Initializations ***************
        // ******************************************************
        $email = $this->_getParam("forgotPasswordEmail");
        $userDb = new Application_Model_DbTable_User();

        $mail = new Mail();
        $error = "";


        // ******************************************************
        // ************ Function Logics *************************
        // ******************************************************
        $result = $userDb->select()
                ->from($userDb)
                ->where("email = ?", $email);
        if (count($result) == 1) {
            $result = $result->query();
            $user = $result->fetchAll()[0];

            // ***** @Do: Send email to the user
            $mail->setRecipient($user["email"]);
            $mail->setTemplate(Mail::FORGOT_PASSWORD);
            $mail->uid = $user["uid"];
            $mail->token = $user["password"];
            $mail->name = $user["name"];
            $mail->address = $user["email"];
            $mail->send();
        } else {
            $error = "The user with the particular email <strong>{$email}</strong> does not exist.";
        }


        // ******************************************************
        // ************ Returns and Assignment ******************
        // ******************************************************
        $this->view->error = $error;
        $this->view->email = $email;
    }

    public function changepasswordAction() {
        // ******************************************************
        // ************ Variables Initializations ***************
        // ******************************************************
        $uid = $this->_getParam("forgotPasswordUid");
        $password = $this->_getParam("forgotPasswordNewPassword");
        $bcrypt = new Bcrypt();
        $userDb = new Application_Model_DbTable_User;
        $error = "";

        // ******************************************************
        // ************ Function Logics *************************
        // ******************************************************
        $encryptedPassword = $bcrypt->customHashWith_MD5_Salt($password);
        $data["password"] = $encryptedPassword;
        $where["uid = ?"] = $uid;
        $rowAffected = $userDb->update($data, $where);
        if(!$rowAffected) {
            $error = "Internal server error, please try again or contact us.";
        }
        $this->view->error = $error;

        // ******************************************************
        // ************ Returns and Assignment ******************
        // ******************************************************
    }

}

