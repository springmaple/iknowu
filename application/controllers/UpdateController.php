<?php

class UpdateController extends Zend_Controller_Action {

    public function init() {
        
    }

    public function indexAction() {
        // ******************************************************
        // ************ Variables Initializations ***************
        // ******************************************************
        $followDb = new Application_Model_DbTable_Follow();
        $authSession = new Zend_Session_Namespace("auth");
        $messageDb = new Application_Model_DbTable_Message();
        $userDb = new Application_Model_DbTable_User();

        $noFollow = false;
        $noUpdate = false;

        // ******************************************************
        // ************ Function Logics *************************
        // ******************************************************
        if (!isset($authSession->uid)) {
            throw new Zend_Controller_Action_Exception("NotLogin", EXCEPTION_NO_LOGIN);
        }
        // ***** @Do: check if this user is following somebody
        $followSql = $followDb->select()
                ->from($followDb)
                ->where("followeruid = ?", $authSession->uid);
        $followResult = $followSql->query();
        if ($followResult->rowCount() == 0) {
            $noFollow = true;
        }
        // ***** @Do: check if this user has any update
        $messageSql = $messageDb->select()
                ->distinct()
                ->from($messageDb, "fromuid")
                ->where("touid = ?", $authSession->uid);
        $messageResult = $messageSql->query();
        if ($messageResult->rowCount() == 0) {
            $noUpdate = true;
        } else {
            $subscribedUsers = array();
            $usersId = $messageResult->fetchAll();
            foreach ($usersId as $user) {
                $userSql = $userDb->find($user["fromuid"]);
                $userResult = $userSql->toArray();

                $subscribedUsers[] = $userResult[0];
            }
        }

        // ******************************************************
        // ************ Returns and Assignment ******************
        // ******************************************************
        // override the update amount
        $this->view->totalUpdate = 0;
        
        $this->view->noFollow = $noFollow;
        $this->view->noUpdate = $noUpdate;
        $this->view->subscribedUsers = $subscribedUsers;
    }

    public function updateframeAction() {
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
        // ***** @Do: check if this user has updates
        $sql = $messageDb->select()
                ->from($messageDb)
                ->where("touid = ?", $authSession->uid);
        if ($id != 0) {
            $sql->where("fromuid = ?", $id);
        }
        $sql->order(array("date DESC"));
        $result = $sql->query();
        if ($result->rowCount() == 0) {
            switch ($id) {
                case 0:
                    $noUpdate = "No update is available at this moment.";
                    break;
                default:
                    $noUpdate = "No update to you from this user.";
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

