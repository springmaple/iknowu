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
        // ***** @Do: chec if this user has any update
        $messageSql = $messageDb->select()
                ->from($messageDb)
                ->where("touid = ?", $authSession->uid);
        $messageResult = $messageSql->query();
        if($messageResult->rowCount() == 0) {
            $noUpdate = true;
        }

        // ******************************************************
        // ************ Returns and Assignment ******************
        // ******************************************************
        $this->view->noFollow = $noFollow;
        $this->view->noUpdate = $noUpdate;
    }

    public function updateframeAction() {
        // ******************************************************
        // ************ Variables Initializations ***************
        // ******************************************************
        $authSession = new Zend_Session_Namespace("auth");
        $messageDb = new Application_Model_DbTable_Message();
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
        $sql->order(array("date"));
        $result = $sql->query();
        if ($result->rowCount() == 0) {
            switch($id){
                case 0:
                    $noUpdate = "No update is available at this moment.";
                    break;
                default:
                    $noUpdate = "No update to you from this user.";
            }
            
        } else {
            // this user has updates
            $updates = $result->fetchAll();
        }

        // ******************************************************
        // ************ Returns and Assignment ******************
        // ******************************************************
        $this->view->noUpdate = $noUpdate;
        $this->view->updates = $updates;
    }

}

