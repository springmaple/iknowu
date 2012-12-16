<?php
// Define the root uri of the website
header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
// Define custom error code for not logged in
defined('EXCEPTION_NO_LOGIN')
    || define('EXCEPTION_NO_LOGIN', 5000);

// Define root path to the project
defined('ROOT_URI')
    || define('ROOT_URI', '/iknowu/public');

// Define Domain
defined('ROOT_DOMAIN')
    || define('ROOT_DOMAIN', 'http://www.iknowu.com/iknowu/public');
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';
require_once 'Facebook/facebook.php';
require_once 'Jcart/jcart.php';
require_once 'Custom/bcrypt.php';
require_once 'Custom/mail.php';
require_once 'Custom/moneyFormat.php';
require_once 'Custom/chromephp.php';
require_once 'Custom/tinyurl.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();