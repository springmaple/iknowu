<?php

class ProductController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
        /* check unauthorized access to product upload */
        $userSession = new Zend_Session_Namespace("auth");
        if (!isset($userSession->uid) && $this->getRequest()->getActionName() != "index") {
            throw new Zend_Controller_Action_Exception("NotLogin", EXCEPTION_NO_LOGIN);
        }

// get session for stored instances
        $productSession = new Zend_Session_Namespace("productUpload");

//  $name = $gender = $price = $description = $images = $mainCategory = $subCategory = $subSubCategory = $brand = $color = "";
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$this->getParam(Zend_Registry::get("jcart_config")['item']['add'])) {
            $productSession->name = $this->getParam("productUploadName");
            $productSession->price = $this->getParam("productUploadPrice");
            $productSession->mainCategory = $this->getParam("productUploadMainCategorySelect");
            $productSession->subCategory = $this->getParam("productUploadSubCategorySelect");
            $productSession->subSubCategory = $this->getParam("productUploadSubSubCategorySelect");
            $productSession->gender = $this->getParam("productUploadGenderSelect");

            $productSession->description = $this->getParam("productUploadDesc");
            $productSession->brand = $this->getParam("productUploadBrand");
            $productSession->color = $this->getParam("productUploadColor");

// get images files
            $files = array();
            $file = $_FILES['productUploadImages'];

            for ($i = 0; $i < count($file["name"]); $i++) {
                $destination = "images/products/tmp/" . $file['name'][$i];
                move_uploaded_file($file["tmp_name"][$i], $destination);
                $destination = $file['name'][$i];
                $files[] = $destination;
            }

            $productSession->images = $files;

// get product sizes amount
            $sizeDb = new Application_Model_DbTable_Size();
            $results = $sizeDb->fetchAll();
            $array = array();

            foreach ($results->toArray() as $result) {
                $size = $result['indicator'];
                if ($this->getParam($size) != "" && $this->getParam($size) != 0) {
                    $array[$size] = $this->getParam($size);
                }
            }
            $productSession->productUploadSizes = $array;
            $this->view->productUploadSizes = $array;
        }


        $defaultImage = ROOT_URI . "/images/products/default.jpg";


        $this->view->productUploadName = (isset($productSession->name)) ? $productSession->name : "";
        $this->view->productUploadPrice = (isset($productSession->price)) ? Zend_Registry::get("currency")->_convertToString($productSession->price) : "";
        $this->view->productUploadImages = (isset($productSession->images)) ? $productSession->images : array($defaultImage);
        $this->view->productUploadMainCategory = (isset($productSession->mainCategory)) ? $productSession->mainCategory : "";
        $this->view->productUploadSubCategory = (isset($productSession->subCategory)) ? $productSession->subCategory : "";
        $this->view->productUploadSubSubCategory = (isset($productSession->subSubCategory)) ? $productSession->subSubCategory : "";
        $this->view->productUploadGender = (isset($productSession->gender)) ? $productSession->gender : "";

        $this->view->productUploadDescription = (isset($productSession->description)) ? $productSession->description : "";
        $this->view->productUploadBrand = (isset($productSession->brand)) ? $productSession->brand : "";
        $this->view->productUploadColor = (isset($productSession->color)) ? $productSession->color : "";

// render to product upload
    }

    public function indexAction() {
        /*
         * Purpose: Handle the justComment & justDeleteComment alert
         */
        $alertSession = new Zend_Session_Namespace("alert");
        if ($alertSession->justComment) {
            $this->view->justComment = true;
            $alertSession->justComment = false;
        }
        if ($alertSession->justDeleteComment) {
            $this->view->justDeleteComment = true;
            $alertSession->justDeleteComment = false;
        }
        /*
         * Purpose: Declare some important variables
         */
        $db = Zend_Registry::get("db"); // the database adapter, can be used to do direct QUERY
        /*
         * Purpose: check that the request contains a parameter "id" which is the id of the product
         */
        if ($this->_getParam("id") != "") {
            $pid = $this->_getParam("id"); // the pid of the product selected to view
            /*
             * Purpose: get product according to the id given in the parameter
             */
            $productDb = new Application_Model_DbTable_Product();
            $result = $productDb->find($pid);
            if ($result->count() < 1) {
                // product with specific id is not found
                throw new Zend_Controller_Action_Exception("NotLogin", EXCEPTION_NO_LOGIN);
            }
            $items = $result->toArray();
            $product = $items[0]; // $product is the array that contains the information of product table 
            $product['formattedPrice'] = Zend_Registry::get("currency")->_convertToString($product['price']);

            /*
             * Purpose: try to get the product images now 
             */
            $imagesResult = $db->query("select i.img from prodimg p, image i where p.imgid = i.imgid and pid = ?", array($pid));
            /* $images is the variable that contains the images now,
             *  use print_r($images) to see it. If the result is Array(), means no 
             * image was uploaded for this product. 
             */
            $images = $imagesResult->fetchAll();

            /*
             * Purpose: get all of the available sizes
             */
            $sizesResult = $db->query("SELECT s.sizeid, s.indicator, p.quantityleft 
                FROM prodsize p, size s 
                WHERE p.sizeid = s.sizeid AND p.pid = ?", array($pid));
            /*
             * array which contains the sizes that still available
             * Method to access: Use foreach() - $sizes[0]['sizeid'], $sizes[1]['sizeid']
             */
            $sizes = $sizesResult->fetchAll();

            /*
             * Purpose: get rating
             */
            $rateResult = $db->query("SELECT count(uid) AS numberrate, sum(rate) AS totalrate
                FROM rate
                WHERE pid = ?", array($pid));
            $rate = $rateResult->fetchAll();
            $authSession = new Zend_Session_Namespace("auth");
            $uid = $authSession->uid;
            $myRate = 0;
            if ($uid) {
                $myRateResult = $db->query("SELECT rate 
                    FROM rate
                    WHERE uid = ?", array($uid));
                $myRateArray = $myRateResult->fetchAll();
                if (!empty($myRateArray)) {
                    $myRate = $myRateArray[0]['rate'];
                }
            }
            $rate[0]['myrate'] = $myRate;

            /*
             * Purpose: check if the product is still availabe (has sizes)
             */
            $this->view->isAvailable = false;
            foreach ($sizes as $size) {
                if ($size['quantityleft'] > 0) {
                    $this->view->isAvailable = true;
                    break;
                }
            }

            /*             * ***********************************************
             * Get the comments for the product here
             */
            $commentDb = new Application_Model_DbTable_Comment();
            $sql = $commentDb->select()
                    ->from($commentDb)
                    ->where("pid = ?", $pid);
            $resultset = $sql->query();
            $comments = $resultset->fetchAll();

            /*
             * Purpose: retrieve profile picture and username together
             */
            $commentsTmp = $comments;
            foreach ($commentsTmp as $key => $comment) {
                $rs = $db->query("SELECT nickname, img FROM user WHERE uid = ?", array($comment['uid']));
                $user = $rs->fetchAll()[0];
                $comments[$key]['nickname'] = $user['nickname'];
                $comments[$key]['img'] = $user['img'];
            }

            $this->view->rate = $rate[0];
            $this->view->product = $product;
            $this->view->images = $images;
            $this->view->sizes = $sizes;
            $this->view->comments = $comments;
            $this->view->uid = $authSession->uid;
            $this->view->appid = Zend_Registry::get("fb")["appId"];
        }
    }

    public function uploadAction() {
// get all sizes
        $sizeDb = new Application_Model_DbTable_Size();
        $this->view->sizes = $sizeDb->fetchAll();

// get main categories
        $categoryDb = new Application_Model_DbTable_Category();
        $stm = $categoryDb->select()
                ->from("category", "maincat")
                ->distinct();
        $results = $stm->query()->fetchAll();
        $this->view->maincat = $results;
    }

    public function confirmuploadAction() {
// action body
    }

    public function uploadedAction() {
// action body        
        $productSession = new Zend_Session_Namespace("productUpload");
        $userSession = new Zend_Session_Namespace("auth");
        if ($this->_getParam("key") == "" || $this->_getParam("key") != md5($productSession->name)) {
            throw new Zend_Controller_Action_Exception("NotLogin", EXCEPTION_NO_LOGIN);
        } else {
            // insert brand | get brand id (bid)
            $brand = $productSession->brand;
            $brandDb = new Application_Model_DbTable_Brand();
            $sql = $brandDb->select()
                    ->from($brandDb)
                    ->where("bname = ?", $brand);
            $result = $sql->query();
            if ($result->rowCount() < 1) {
                // means the brand is not in our database
                $brands["bname"] = $brand;
                $bid = $brandDb->insert($brands);
            } else {
                $brands = $result->fetchObject();
                $bid = $brands->bid;
            }

            // insert category | get category id (catid)
            isset($productSession->mainCategory) ? $mainCat = $productSession->mainCategory : $mainCat = "";
            isset($productSession->subCategory) ? $subCat = $productSession->subCategory : $subCat = "";
            isset($productSession->subSubCategory) ? $subSubCat = $productSession->subSubCategory : $subSubCat = "";
            $catDb = new Application_Model_DbTable_Category();
            $sql2 = $catDb->select()
                    ->from($catDb)
                    ->where("maincat = ?", $mainCat)
                    ->where("subcat = ? ", $subCat)
                    ->where("subsubcat = ? ", $subSubCat);
            $result2 = $sql2->query();
            $catId = "";
            if ($result2->rowCount()) {
                $catId = $result2->fetchObject()->catid;
            }

            // determine forGender value
            $gender = $productSession->gender;
            switch ($gender) {
                case "male":
                    $gender = "M";
                    break;
                case "female":
                    $gender = "F";
                    break;
                default:
                    $gender = "";
            }

            // insert to product first
            $products = array("pname" => $productSession->name,
                "desc" => $productSession->description,
                "price" => $productSession->price,
                "close" => 0,
                "forgender" => $gender,
                "bid" => $bid,
                "catid" => $catId,
                "uid" => $userSession->uid);
            $productDb = new Application_Model_DbTable_Product();
            $pid = $productDb->insert($products);


            // insert colors
            $colorDb = new Application_Model_DbTable_Color();
            $prodColDb = new Application_Model_DbTable_Prodcolor();
            foreach ($productSession->color as $color) {
                $color = str_replace("#", "", $color);
                $sql3 = $colorDb->select()
                        ->from($colorDb)
                        ->where("colcode = ?", $color);
                $result3 = $sql3->query();
                $colId = "";
                if ($result3->rowCount() < 1) {
                    $colors = array("colcode" => $color);
                    $colId = $colorDb->insert($colors);
                } else {
                    $colorObject = $result3->fetchObject();
                    $colId = $colorObject->colid;
                }
                if ($colId != "") {
                    $prodCols = array("pid" => $pid, "colid" => $colId);
                    $prodColDb->insert($prodCols);
                }
            }

// insert product sizes
            $sizeDb = new Application_Model_DbTable_Size();
            $prodSizeDb = new Application_Model_DbTable_Prodsize();
            ChromePhp::log($productSession->productUploadSizes);
            foreach ($productSession->productUploadSizes as $sizeKey => $sizeValue) {
                $sql4 = $sizeDb->select()
                        ->from($sizeDb)
                        ->where("indicator = ?", $sizeKey);
                $result4 = $sql4->query();
                if ($result4->rowCount() > 0) {
                    $result4 = $result4->fetchObject();
                    $sizeId = $result4->sizeid;
                    $prodSizes = array("pid" => $pid, "sizeid" => $sizeId, "quantity" => $sizeValue, "quantityleft" => $sizeValue);
                    $prodSizeDb->insert($prodSizes);
                }
            }

//  insert imag es
            $imageDb = new Application_Model_DbTable_Image();
            $prodImgDb = new Application_Model_DbTable_Prodimg();
            foreach ($productSession->images as $image) {
                $path_parts = pathinfo($image);
                $extension = $path_parts['extension'];
                $newFileName = uniqid() . "." . $extension;
                copy("images/products/tmp/" . $image, "images/products/" . $newFileName);
                unlink("images/produ cts/tmp/" . $image);


                $images = array("date" => date("Y-m-d H:i:s"), "img" => $newFileName);
                $imgId = $imageDb->insert($images);
                $prodImgs = array("imgid" => $imgId, "pid" => $pid);
                $prodImgDb->insert($prodImgs);
            }

// unset the product upload session
            $productSession->unlock();
            Zend_Session::namespaceUnset("productUpload");

// get product URL
            $this->view->productUrl = get_tiny_url("http://localhost/iknowu/public/product?id=" . $pid);
        }
    }

}

