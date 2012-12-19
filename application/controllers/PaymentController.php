<?php

class PaymentController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        $this->view->jcart = Zend_Registry::get("jcart");
    }

    public function paypalAction() {
        $config = Zend_Registry::get("jcart_config");
        $jcart = Zend_Registry::get("jcart");

////////////////////////////////////////////////////////////////////////////
        /*

          A malicious visitor may try to change item prices before checking out.

          Here you can add PHP code that validates the submitted prices against
          your database or validates against hard-coded prices.

          The cart data has already been sanitized and is available thru the
          $jcart->get_contents() method. For example:

          foreach ($jcart->get_contents() as $item) {
          $itemId	    = $item['id'];
          $itemName	= $item['name'];
          $itemPrice	= $item['price'];
          $itemQty	= $item['qty'];
          }

         */
////////////////////////////////////////////////////////////////////////////
// For now we assume prices are valid
        $validPrices = true;

////////////////////////////////////////////////////////////////////////////
// If the submitted prices are not valid, exit the script with an error message
        if ($validPrices !== true) {
            die($config['text']['checkoutError']);
        }
        // Price validation is complete
        // Send cart contents to PayPal using their upload method, for details see: http://j.mp/h7seqw
        elseif ($validPrices === true) {
            // Paypal count starts at one instead of zero
            $count = 1;

            // ***** @Do: add a token for verifying if the user really came back from paypal after payment
            $token = uniqid();
            $config['paypal']['returnUrl'] .= "?token={$token}";
            $paypalSession = new Zend_Session_Namespace("paypal");
            $paypalSession->token = $token;

            // Build the query string
            $queryString = "?cmd=_cart";
            $queryString .= "&upload=1";
            $queryString .= "&charset=utf-8";
            $queryString .= "&currency_code=" . urlencode($config['currencyCode']);
            $queryString .= "&business=" . urlencode($config['paypal']['id']);
            $queryString .= "&return=" . urlencode($config['paypal']['returnUrl']);
            $queryString .= '&notify_url=' . urlencode($config['paypal']['notifyUrl']);

            foreach ($jcart->get_contents() as $item) {
                $queryString .= '&item_number_' . $count . '=' . urlencode($item['id']);
                $queryString .= '&item_name_' . $count . '=' . urlencode($item['name']);
                $queryString .= '&amount_' . $count . '=' . urlencode($item['price']);
                $queryString .= '&quantity_' . $count . '=' . urlencode($item['qty']);
                // Increment the counter
                ++$count;
            }

            // Confirm that a PayPal id is set in config.php
            if ($config['paypal']['id']) {
                // Add the sandbox subdomain if necessary
                $sandbox = '';
                if ($config['paypal']['sandbox'] === true) {
                    $sandbox = '.sandbox';
                }

                // Use HTTPS by default
                $protocol = 'https://';
                if ($config['paypal']['https'] == false) {
                    $protocol = 'http://';
                }

                // Send the visitor to PayPal
                @header('Location: ' . $protocol . 'www.sandbox.paypal.com/cgi-bin/webscr' . $queryString);
            } else {
                die('Couldn&rsquo;t find a PayPal ID in <strong>config.php</strong>.');
            }
        }
    }

    public function successAction() {
        // ******************************************************
        // ************ Variables Initializations ***************
        // ******************************************************
        $error = "";
        $jcart = Zend_Registry::get("jcart");
        $purchaseDb = new Application_Model_DbTable_Purchase;
        $purchaseListDb = new Application_Model_DbTable_Purchaselist;
        $productSizeDb = new Application_Model_DbTable_Prodsize;
        $authSession = new Zend_Session_Namespace("auth");
        $paypalSession = new Zend_Session_Namespace("paypal");
        $token = $this->_getParam("token");

        // ******************************************************
        // ************ Function Logics *************************
        // ******************************************************
        // Array ( [0] => Array ( [id] => 3-2 [name] => Product Testing 2 (XS) [price] => 100 [qty] => 2 [url] => http://tinyurl.com/carwu59 [subtotal] => 200 ) )
        if ($token == "") {
            $error = "You do not have a valid access token.";
        }
        if (!isset($authSession->uid)) {
            throw new Zend_Controller_Action_Exception("NotLogin", EXCEPTION_NO_LOGIN);
        }
        $contents = $jcart->get_contents();
        if (count($contents) == 0) {
            $error = "You don't have any items in your cart.";
        } else {
            if (isset($paypalSession->token) && $paypalSession->token == $token) {
                // ***** @Do: create a purchase record for the purchase list
                $data = array("uid" => $authSession->uid, "date" => date("Y-m-d H:i:s"));
                $purchaseid = $purchaseDb->insert($data);
                foreach ($contents as $purchase) {
                    // quantity, pid, purchaseid, sizeid, managed, datemanaged
                    // ***** @Do: break the id into pid and sizeid
                    $ids = explode("-", $purchase['id']);
                    $list = array("quantity" => $purchase["qty"], "pid" => $ids[0], "sizeid" => $ids[1], "purchaseid" => $purchaseid, "managed" => 0);
                    // ***** @Do: insert into purchase list
                    $isInserted = $purchaseListDb->insert($list);
                    // ***** @Do: update quantityleft in product_size table
                    if ($isInserted) {
                        $quantityLeft = $productSizeDb->select()
                                        ->from($productSizeDb, "quantityleft")
                                        ->where("pid = ?", $ids[0])
                                        ->where("sizeid = ?", $ids[1])
                                        ->query()
                                        ->fetchAll()[0]["quantityleft"];
                        $quantityLeft -= $purchase['qty'];
                        $isUpdated = $productSizeDb->update(array("quantityleft" => $quantityLeft), array("pid = ?" => $ids[0], "sizeid = ?" => $ids[1]));
                        if($isUpdated) {
                            // ***** @Do: send message to inform the user
                            // TODO
                        }
                    }
                }
                // ***** @Do: clear the items in the cart after succesfully insert
                $jcart->empty_cart();
                // ***** @Do: clear the token session after inserted the purchase list into the database.
                $paypalSession->unlock();
                Zend_Session::namespaceUnset("paypal");
            } else {
                $error = "Invalid access token.";
            }
        }

        // ******************************************************
        // ************ Returns and Assignment ******************
        // ******************************************************
        $this->view->error = $error;
    }

}

