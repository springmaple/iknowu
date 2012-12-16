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

}

