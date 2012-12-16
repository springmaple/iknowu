<?php

class IndexController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        // ************ Variables Initializations
        $db = Zend_Registry::get("db");

        // ************ Function Logics
        /*
         * @Do: Get all products
         * 
         * @return: $availableProducts [pid, pname, indicator, quantity]
         */
        $results = $db->query("SELECT * FROM get_available_product_view");
        $availableProducts = $results->fetchAll();
        /* *****************************************************************
         * @Do: Iterate products to get image
         * *****************************************************************/
        $tmpAvailableProducts = $availableProducts;
        foreach ($tmpAvailableProducts as $key => $availableProduct) {
            $id = $availableProduct['pid'];
            $imageResult = $db->query("SELECT i.img 
                                FROM prodimg p, image i 
                                WHERE p.imgid = i.imgid AND p.pid = ?
                                LIMIT 1", array($id));
            $image = $imageResult->fetchAll();
            $availableProducts[$key]['image'] = empty($image[0]['img'])?"default.jpg":$image[0]['img'];
        }

        // ************ Returns and Assignment
        $this->view->products = $availableProducts;
    }

}

