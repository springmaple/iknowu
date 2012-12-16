<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
    /*
      protected function _initDatabase(){
      $config = $this->getOptions();
      $db = Zend_Db::factory($config['resources']['db']['adapter'], $config['resources']['db']['params']);
      Zend_Db_Table::setDefaultAdapter($db);
      Zend_Registry::set("db", $db);
      }
     * */

    protected function _initDoctype() {
        $this->bootstrap('view');
        $this->getResource('view')->doctype('HTML5');
    }

    protected function _initAutoload() {
        $moduleLoader = new Zend_Application_Module_Autoloader(array(
                    'namespace' => '',
                    'basePath' => APPLICATION_PATH));
        return $moduleLoader;
    }

    protected function _initZendRegistry() {
        // fb 
        $config = $this->getOptions();
        $fbConfig = $config['facebook'];
        Zend_Registry::set("fb", $fbConfig);

        // currency
        $currency = new MoneyFormat();
        Zend_Registry::set("currency", $currency);

        // database
        $dbConfig = $this->getOptions();
        $db = Zend_Db::factory($dbConfig['resources']['db']['adapter'], $dbConfig['resources']['db']['params']);
        Zend_Db_Table::setDefaultAdapter($db);
        Zend_Registry::set("db", $db);

        // email
        $emailConfig = $config['email'];
        Zend_Registry::set("email", $emailConfig); 
        
        // jcart
        if (!Zend_Registry::isRegistered("jcart_config")) {
            $jcartConfig = $config['jcart'];
            Zend_Registry::set("jcart_config", $jcartConfig);
        }

        Zend_Session::start();
        // $jcartSession = new Zend_Session_Namespace('jcart');
        if (!isset($_SESSION['jcart']) || !is_object($_SESSION['jcart'])) {
            $_SESSION['jcart'] = new Jcart($jcartConfig);
        }
        $jcartSession = $_SESSION['jcart'];

        if (!Zend_Registry::isRegistered("jcart")) {
            Zend_Registry::set("jcart", $jcartSession);
        }
    }

}

