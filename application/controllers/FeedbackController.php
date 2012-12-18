<?php

class FeedbackController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        // ******************************************************
        // ************ Variables Initializations ***************
        // ******************************************************
        $userDb = new Application_Model_DbTable_User;
        $authSession = new Zend_Session_Namespace("auth");
        $email = "";

        // ******************************************************
        // ************ Function Logics *************************
        // ******************************************************
        if (isset($authSession->uid)) {
            $query = $userDb->find($authSession->uid);
            $email = $query->toArray()[0]["email"];
        }

        // ******************************************************
        // ************ Returns and Assignment ******************
        // ******************************************************
        $this->view->email = $email;
    }

    public function submitAction() {
        // ******************************************************
        // ************ Variables Initializations ***************
        // ******************************************************
        $rate = $this->_getParam("feedbackRate");
        $content = $this->_getParam("feedbackContent");
        $email = $this->_getParam("feedbackEmail");
        $today = date("Y-m-d H:i:s");
        $feedbackDb = new Application_Model_DbTable_Feedback;
        $data = array("rate" => $rate, "content" => $content, "email" => $email, "date" => $today, "seen" => 0);

        // ******************************************************
        // ************ Function Logics *************************
        // ******************************************************
        if (!empty($rate) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $isSuccess = $feedbackDb->insert($data);
        }

        // ******************************************************
        // ************ Returns and Assignment ******************
        // ******************************************************
        $this->view->isSuccess = $isSuccess;
    }

    public function viewAction() {
        // ******************************************************
        // ************ Variables Initializations ***************
        // ******************************************************
        $feedbackDb = new Application_Model_DbTable_Feedback;

        // ******************************************************
        // ************ Function Logics *************************
        // ******************************************************
        $feedbackQuery = $feedbackDb->select()
                ->from($feedbackDb)
                ->order(array("date DESC"));
        $feedbacks = $feedbackQuery->query()->fetchAll();

        // ******************************************************
        // ************ Returns and Assignment ******************
        // ******************************************************
        $this->view->feedbacks = $feedbacks;
    }

    public function viewerAction() {
        // ******************************************************
        // ************ Variables Initializations ***************
        // ******************************************************
        $email = $this->_getParam("email");
        $date = $this->_getParam("date");
        $error = "";
        $feedbackDb = new Application_Model_DbTable_Feedback;
        $feedback = "";

        // ******************************************************
        // ************ Function Logics *************************
        // ******************************************************
        if ($email != "" && $date != "") {
            $feedbackSql = $feedbackDb->select()
                    ->from($feedbackDb)
                    ->where("email = ?", $email)
                    ->where("date = ?", $date);
            $feedbacks = $feedbackSql->query()->fetchAll();
            if (count($feedbacks) != 1) {
                $error = "The feedback that you are looking for is not exist or is already deleted.";
            } else {
                $feedback = $feedbacks[0];
                $feedbackDb->update(array("seen" => 1), array("email = ?" => $feedback["email"], "date = ?" => $feedback["date"]));
            }
        } else {
            $error = "No feedback is selected.";
        }

        // ******************************************************
        // ************ Returns and Assignment ******************
        // ******************************************************
        $this->view->error = $error;
        $this->view->feedback = $feedback;
    }

}

