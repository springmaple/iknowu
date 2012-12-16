<?php

class AjaxController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function init()
    {
        /* Initialize action controller here */
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext
                ->addActionContext('auth', 'json')
                ->addActionContext('profileedit', 'json')
                ->addActionContext('profileavatarupload', 'json')
                ->addActionContext('signout', 'html')
                ->addActionContext('fbauth', 'html')
                ->addActionContext('getcategory', 'json')
                ->addActionContext('getbrand', 'html')
                ->addActionContext('uploadcomment', 'html')
                ->addActionContext('getjcartconfig', 'json')
                ->addActionContext('getjcart', 'html')
                ->addActionContext('rate', 'html')
                ->initContext();
    }

    public function indexAction()
    {
// action body
        /* code template
          $response = $this->getResponse();
          $response->setHeader('Content-type', 'application/json', true);
          return $response->setBody(Zend_Json::encode(array("id" => "abc", "details" => "bc")));
         */
    }

    public function authAction()
    {
// action body       
        $email = $this->getParam("email");
        $password = $this->getParam("password");
        $db = new Application_Model_DbTable_User();
        $stm = $db->select()
                ->where("email = ?", $email);
        $results = $stm->query();
        $result = $results->fetchAll();
        if (!empty($result)) {
            $bcrypt = new Bcrypt();
            if ($bcrypt->check($password, $result[0]['password'])) {
                $this->view->isLoggedin = 1;
                $this->view->status = "Signed in successfully.";
                $session = new Zend_Session_Namespace("auth");
                $session->uid = $result[0]['uid'];
            } else {
                $this->view->isLoggedin = 2;
                $this->view->status = "Incorrect password.";
            }
        } else {
            $this->view->isLoggedin = 0;
            $this->view->status = "Non-existing email address.";
        }
    }

    public function signoutAction()
    {
// action body
        $session = new Zend_Session_Namespace("auth");
        $session->unlock();
        Zend_Session::namespaceUnset("auth");
    }

    public function fbauthAction()
    {
// action body
        $session = new Zend_Session_Namespace("auth");
        $db = new Application_Model_DbTable_User();
        $access_token = $this->getParam("accessToken");
        /*         * ** facebook login cookie
          $fb_cookie = null;
          if ($request->getCookie('isFbAutologin') != null) {
          $fb_cookie = $request->getCookie('isFbAutologin');
          }
         * 
         * // setcookie("isFbAutologin", 1, time()+60*60*24*30 ); 
         * */

// facebook login
        $facebook = new Facebook(Zend_Registry::get("fb"));
        // $facebook->setAccessToken($access_token);
        $f_uid = $facebook->getUser();


        if (!$f_uid) {
// facebook user not connected
        } else {
// facebook user is connected
            $f_user = $facebook->api('/me', 'GET');
            $select = $db->select()->where("email = ?", $f_user['email']);
            $stm = $select->query();
            $results = $stm->fetchAll();
            if (empty($results)) { // check if the user already exists
// register facebook user when database doesnt have his info
                $data = array();
                if ($f_user['email'] == null) {
                    return false;
                }
                $data['email'] = $f_user['email'];
                $data['uname'] = $f_user['name'];
                $data['password'] = sha1(uniqid());
                $data['category'] = 'M';
                if ($f_user['gender'] == 'male') {
                    $data['gender'] = 'M';
                } elseif ($f_user['gender'] == 'female') {
                    $data['gender'] = 'F';
                }
                $data['nickname'] = $f_user['username'];
                $data['img'] = $f_user['id'];

                $id = $db->insert($data);
// store just auto connected user id to session
                $session->uid = $id;
            } else { // if already have account with iknowu
                $session->uid = $results[0]['uid'];
            }
        }
    }

    public function profileeditAction()
    {
        $session = new Zend_Session_Namespace("auth");
        $db = new Application_Model_DbTable_User();

        $id = array();
        $id['uid = ?'] = $session->uid;
        $data = array();
        $data["nickname"] = $this->getParam("nickname");
        $data["gender"] = $this->getParam("gender");
        $data["name"] = $this->getParam("name");
        $data["address"] = $this->getParam("address");

        $success = $db->update($data, $id);
        $this->view->status = $success;
    }

    public function profileavataruploadAction()
    {
        // action body
        try {
            $db = new Application_Model_DbTable_User();
            $session = new Zend_Session_Namespace("auth");
            $uid = $session->uid;
            $this->view->success = false;

            $stm = $db->select()
                    ->where("uid = ?", $uid);
            $results = $stm->query();
            $result = $results->fetchAll();

            foreach ($_FILES as $file) {
                $path_parts = pathinfo($file["name"]);
                $extension = $path_parts['extension'];
                $file2Upload = $file;
                $newFileName = uniqid() . "." . $extension;
            }
            if (!empty($result)) {
                $img = $result[0]["img"];
                if (is_dir("images/avatars/")) {
                    if ($img != "default.jpg")
                        unlink("images/avatars/$img");
                    $imageUploadStatus = move_uploaded_file($file2Upload["tmp_name"], "images/avatars/" . $newFileName);
                    $data = array();
                    $data["img"] = $newFileName;
                    $id = array();
                    $id["uid = ?"] = $uid;
                    $db->update($data, $id);
                    if ($imageUploadStatus == true) {
                        $this->view->status = "Profile picture uploaded successfully.";
                        $this->view->newName = $newFileName;
                        $this->view->success = true;
                    }
                } else {
                    $this->view->status = "Internal server error, please try again later.";
                }
            }
        } catch (Exception $e) {
            $e->getTrace();
            $this->view->status = "Error in uploading profile picture, please try again later.";
        }
    }

    public function getcategoryAction()
    {
        // action body
        $mode = $this->_getParam("mode");

        $db = new Application_Model_DbTable_Category();

        switch ($mode) {
            case "sub":
                $stm = $db->select()
                        ->distinct()
                        ->from("category", "subcat")
                        ->where("maincat = ?", $this->_getParam("value"));
                $results = $stm->query()->fetchAll();
                $results1 = array();
                foreach ($results as $value) {
                    $results1[] = $value['subcat'];
                }
                $this->view->data = $results1;
                break;
            case "subsub":
                $stm = $db->select()
                        ->distinct()
                        ->from("category", "subsubcat")
                        ->where("subcat = ?", $this->_getParam("value1"))
                        ->where("maincat = ?", $this->_getParam("value"));
                $results = $stm->query()->fetchAll();
                $results1 = array();
                foreach ($results as $value) {
                    $results1[] = $value['subsubcat'];
                }
                $this->view->data = $results1;
                break;
        }
    }

    public function getbrandAction()
    {
        // action body
        $term = $this->_getParam("term");
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
                ->from('ajax_getbrand_view', 'bname')
                ->where("bname LIKE ?", "%" . $term . "%")
                ->limit(6);
        $array = array();
        foreach ($select->query()->fetchAll() as $value) {
            if ($value['bname'] != null) {
                //$a['label'] = $value['bname'];
                ///$a['value'] = $value['bname'];
                array_push($array, $value['bname']);
            }
        }
        echo json_encode($array);
        return false;
    }

    public function uploadcommentAction()
    {
        // action body
        $alertSession = new Zend_Session_Namespace("alert");
        $session = new Zend_Session_Namespace("auth");
        $uid = $session->uid;
        $pid = $this->_getParam("pid");
        $comment = $this->_getParam("comment");

        $commentDb = new Application_Model_DbTable_Comment();
        $commentArray = array("uid" => $uid, "pid" => $pid, "content" => $comment, "date" => date("Y-m-d H:i:s"));
        $result = $commentDb->insert($commentArray);

        if ($result) {
            $alertSession->justComment = true;
        }
    }

    public function deletecommentAction()
    {
        $alertSession = new Zend_Session_Namespace("alert");
        $session = new Zend_Session_Namespace("auth");
        $uid = $session->uid;
        $pid = $this->_getParam("pid");
        $date = $this->_getParam("date");

        $commentDb = new Application_Model_DbTable_Comment();
        $where = array("uid = ?" => $uid, "pid = ?" => $pid, "date = ?" => $date);
        $result = $commentDb->delete($where);

        if ($result) {
            $alertSession->justDeleteComment = true;
        }
    }

    public function getjcartconfigAction()
    {
        $config = Zend_Registry::get("jcart_config");
        $this->view->response = $config;
    }

    public function getjcartAction()
    {
        Zend_Registry::get("jcart")->display_cart();
    }

    public function rateAction()
    {
        $rateDb = new Application_Model_DbTable_Rate();
        
        /*
         * Purpose: check if user is logged in
         */
        $authSession = new Zend_Session_Namespace("auth");
        $uid = $authSession->uid;
        if(empty($uid)) {
            // this user is not logged in yet
            echo false;
        } else {
            $sql = $rateDb->select()
                    ->from($rateDb)
                    ->where("uid = ?", $uid)
                    ->where("pid = ?", $this->_getParam("pid"));
            $result = $sql->query();
            if($result->rowCount() < 1) {
                // this user not rated before
                $array = array("uid" => $uid, "pid" => $this->_getParam("pid"), "rate" => $this->_getParam("rate"), "date" => date("Y-m-d H:i:s"));
                $rateDb->insert($array);
            } else {
                // this user rated before, just update the previous rate value and date
                $array = array("rate" => $this->_getParam("rate"), "date" => date("Y-m-d H:i:s"));
                $where = array("uid = ?" => $uid, "pid = ?" => $this->_getParam("pid"));
                $rateDb->update($array, $where);
            }
            echo true;
        }
    }


}



