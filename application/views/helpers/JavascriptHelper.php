<?php 
class Zend_View_Helper_JavascriptHelper extends Zend_View_Helper_Abstract
{
    function javascriptHelper() {
        if (is_dir("scripts")) {
            $dh = opendir("scripts");
            while (($file = readdir($dh)) != false) {
                if($file!=".."&&$file!="."){
                    $this->view->headScript()->appendFile(ROOT_URI. "/scripts/" . $file);
                }
            }
            closedir($dh);
        }
        
        return $this->view->headScript();
    }
}