<?php

class IndexController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        // ************ Variables Initializations
        $db = Zend_Registry::get("db");
        $authSession = new Zend_Session_Namespace("auth");
        $productDb = new Application_Model_DbTable_Product;
        $categoryDb = new Application_Model_DbTable_Category;
        $brandDb = new Application_Model_DbTable_Brand;
        $prodColorDb = new Application_Model_DbTable_Prodcolor;
        $colorDb = new Application_Model_DbTable_Color;

        // ************ Function Logics
        /*
         * @Do: Get all products
         * 
         * @return: $availableProducts [pid, pname, indicator, quantity]
         */
        $results = $db->query("SELECT * FROM get_available_product_view ORDER BY date DESC");
        $availableProducts = $results->fetchAll();
        if (isset($authSession->uid)) {
            // **********************************************************************
            // ***** @Do: initialize the recommendation calculation here.  *********
            // **********************************************************************
            $category = $brand = $color = $ratedpid = array();
            $rates = $db->query("SELECT * FROM rate WHERE uid = {$authSession->uid}")->fetchAll();
            foreach ($rates as $rr) {
                $ratedpid[$rr["pid"]] = $rr["pid"];
            }
            $ratedpidlist = implode(",", $ratedpid);
            foreach ($rates as $rate) {
                $pid = $rate["pid"];
                $prod = $productDb->find($pid)->toArray()[0];

                // ***** @Do: calculate for category
                $catid = $prod["catid"];
                $cat = $categoryDb->find($catid)->toArray()[0];
                if ($cat['maincat'] != "") {
                    if (!isset($category[$cat['maincat']])) {
                        $category[$cat['maincat']] = 0;
                    }
                    $category[$cat['maincat']] += ($rate['rating'] - 0.3);
                }
                if ($cat['subcat'] != "") {
                    if (!isset($category[$cat['subcat']])) {
                        $category[$cat['subcat']] = 0;
                    }
                    $category[$cat['subcat']] += ($rate['rating'] - 0.3);
                }
                if ($cat['subsubcat'] != "") {
                    if (!isset($category[$cat['subsubcat']])) {
                        $category[$cat['subsubcat']] = 0;
                    }
                    $category[$cat['subsubcat']] += ($rate['rating'] - 0.3);
                }

                // ***** @Do: calculate for brand
                $bra = $brandDb->find($prod["bid"])->toArray()[0];
                $bra["bname"] = strtolower($bra["bname"]);
                if (!isset($brand[$bra["bname"]])) {
                    $brand[$bra["bname"]] = 0;
                }
                $brand[$bra["bname"]] += ($rate['rating'] - 0.3);

                // ***** @Do: calculate for color
                $col = $prodColorDb->select()->from($prodColorDb)->where("pid = ?", $pid)->query()->fetchAll();
                foreach ($col as $c) {
                    $co = $colorDb->find($c["colid"])->toArray();
                    foreach ($co as $cc) {
                        if (!isset($color[$cc["colcode"]])) {
                            $color[$cc["colcode"]] = 0;
                        }
                        $color[$cc["colcode"]] += ($rate['rating'] - 0.3);
                    }
                }

                // ***** @Do: calculate the weight for each item
                $r = $db->query("SELECT * FROM get_available_product_view WHERE uid != '{$authSession->uid}' AND pid NOT IN ({$ratedpidlist}) ORDER BY date DESC")
                        ->fetchAll();
                $tmp_r = $r;


                $productIdWithWeight = array();

                foreach ($tmp_r as $key => $prod1) {
                    $prodid = $prod1['pid'];

                    // ***** @Do: initiate all product with weight 0
                    $productIdWithWeight[$prodid] = 0;
                    $prod = $productDb->find($prodid)->toArray()[0];
                    $catid = $prod["catid"];

                    // ***** @Do: serialize the category to calculate
                    $cat = $categoryDb->find($catid)->toArray()[0];
                    $catString = $cat['maincat'] . " " . $cat['subcat'] . " " . $cat['subsubcat'];
                    foreach ($category as $k => $b) {
                        if (strpos($catString, $k)) {
                            $productIdWithWeight[$prodid] += $b;
                        }
                    }

                    // ***** @Do: calculate for brand
                    $bra = $brandDb->find($prod["bid"])->toArray()[0];
                    $bra["bname"] = strtolower($bra["bname"]);
                    foreach ($brand as $k => $b) {
                        if (strpos($bra["bname"], $k)) {
                            $productIdWithWeight[$prodid] += $b;
                        }
                    }

                    // ***** @Do: calculate for color
                    $ccc = $db->query("SELECT c.colcode FROM prodcolor p, color c WHERE p.colid  = c.colid AND p.pid = {$prodid}")
                            ->fetchAll();
                    $colString = "";
                    foreach ($ccc as $b) {
                        $colString .= " {$b["colcode"]}";
                    }
                    foreach ($color as $k => $b) {
                        if (strpos($colString, $k)) {
                            $productIdWithWeight[$prodid] += $b;
                        }
                    }
                }

                arsort($productIdWithWeight);
                $availableProducts = array();
                foreach ($productIdWithWeight as $k => $b) {
                    $arr = $db->query("SELECT * FROM get_available_product_view WHERE pid = {$k}")->fetchAll()[0];
                    array_push($availableProducts, $arr);
                }
            }
        }

        /*         * **************************************************************** *
         * @Do: Iterate products to get image   **************************** *
         * ***************************************************************** */
        $tmpAvailableProducts = $availableProducts;
        foreach ($tmpAvailableProducts as $key => $availableProduct) {
            $id = $availableProduct['pid'];
            $imageResult = $db->query("SELECT i.img 
                                FROM prodimg p, image i 
                                WHERE p.imgid = i.imgid AND p.pid = ?
                                LIMIT 1", array($id));
            $image = $imageResult->fetchAll();
            $availableProducts[$key]['image'] = empty($image[0]['img']) ? "default.jpg" : $image[0]['img'];
        }

        // ***** @Do: iterate to get color
        $brandArray = array();
        $colorArray = array();
        $categoryArray = array();
        foreach ($tmpAvailableProducts as $key => $availableProduct) {
            $id = $availableProduct['pid'];
            $ccc = $db->query("SELECT c.colcode FROM prodcolor p, color c WHERE p.colid  = c.colid AND p.pid = {$id}")
                    ->fetchAll();
            $colArr = array();
            foreach ($ccc as $b) {
                array_push($colArr, $b["colcode"]);
            }
            $colorArray[$id] = $colArr;

            // ***** @Do: get brands
            $bbid = $availableProduct['bid'];
            $bra = $brandDb->find($bbid)->toArray()[0];
            $brandArray[$id] = $bra["bname"];

            // ***** @Do: get category
            $ccid = $availableProduct['catid'];
            $cat = $categoryDb->find($ccid)->toArray()[0];
            $catString = $cat['maincat'] . ", " . $cat['subcat'] . ", " . $cat['subsubcat'];
            $categoryArray[$id] = $catString;
        }



        // ************ Returns and Assignment
        $this->view->category = $categoryArray;
        $this->view->brands = $brandArray;
        $this->view->colors = $colorArray;
        $this->view->products = $availableProducts;
    }

}

