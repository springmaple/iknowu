<?php

class MessageController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        // ******************************************************
        // ************ Variables Initializations ***************
        // ******************************************************
        $authSession = new Zend_Session_Namespace("auth");
        $db = Zend_Registry::get("db");
        $userDb = new Application_Model_DbTable_User();

        $noMessage = false;
        $chatUsers = "";

        // ******************************************************
        // ************ Function Logics *************************
        // ******************************************************
        if (!isset($authSession->uid)) {
            throw new Zend_Controller_Action_Exception("NotLogin", EXCEPTION_NO_LOGIN);
        }

        // ***** @Do: check if this user has any update
        $messageResult = $db->query("SELECT * FROM message WHERE type='message' AND (fromuid = {$authSession->uid} OR touid = {$authSession->uid})");
        if ($messageResult->rowCount() == 0) {
            $noMessage = true;
        } else {
            $chatUsers = array();
            $usersId = $messageResult->fetchAll();
            foreach ($usersId as $user) {
                $userResult = $userDb->find($user["fromuid"])->toArray(); 
                $chatUsers[$userResult[0]["uid"]] = $userResult[0];
                
                $userResult = $userDb->find($user["touid"])->toArray(); 
                $chatUsers[$userResult[0]["uid"]] = $userResult[0];
            }
        }

        // ******************************************************
        // ************ Returns and Assignment ******************
        // ******************************************************
        // override the update amount        
        $this->view->chatUsers = $chatUsers;
        $this->view->noMessage = $noMessage;
    }

    public function messageframeAction() {
        // ******************************************************
        // ************ Variables Initializations ***************
        // ******************************************************
        $authSession = new Zend_Session_Namespace("auth");
        $messageDb = new Application_Model_DbTable_Message();
        $db = Zend_Registry::get("db");
        $updates = array();
        $noUpdate = "";

        // ******************************************************
        // ************ Function Logics *************************
        // ******************************************************
        // ***** @Do: disable the layout of this page because it is called within an iframe
        $this->_helper->layout->disableLayout();
        $id = $this->_getParam("id");
        if ($id == "") {
            $id = 0;
        }
        // ***** @Do: check if this user has messages
        $uid = $authSession->uid;
        $result = $db->query("SELECT * FROM message WHERE type='message' AND fromuid IN ({$id}, {$uid}) AND touid IN ({$id}, {$uid}) ORDER BY date DESC")->fetchAll();
        if (count($result) == 0) {
            switch ($id) {
                case 0:
                    $noUpdate = "No conversation is selected.";
                    break;
                default:
                    $noUpdate = "No conversation from this user.";
            }
        } else {
            // this user has updates
            $updates = $result->fetchAll();
            // ***** @Do: update the seen to 1
            foreach ($updates as $update) {
                $data = array("seen" => 1);
                $where = array("touid = ?" => $update["touid"], "fromuid = ?" => $update['fromuid'], "date = ?" => $update["date"]);
                $messageDb->update($data, $where);
            }
        }
        // ***** @Do: get the following users info
        $userResult = $db->query("SELECT *
            FROM user
            WHERE uid IN (SELECT DISTINCT fromuid FROM message WHERE type='update' AND touid={$authSession->uid})");
        $userSet = $userResult->fetchAll();
        $users = array();
        foreach ($userSet as $user) {
            $users[$user["uid"]] = $user;
        }

        // ******************************************************
        // ************ Returns and Assignment ******************
        // ****************************************************** 
        $this->view->noUpdate = $noUpdate;
        $this->view->updates = $updates;
        $this->view->followingList = $users;
    }

}

