<?php  
class Zend_View_Helper_CssHelper extends Zend_View_Helper_Abstract 
{
    function cssHelper() { 
        if (is_dir("styles")) {
         $dh = opendir("styles");
         while (($file = readdir($dh)) != false) {
             if($file!=".."&&$file!="."&&$file!="images"){
                 $this->view->headLink()->appendStylesheet(ROOT_URI . "/styles/" . $file);
             }
         }
            closedir($dh);
        }
    
        return $this->view->headLink(); 
         
    } 
}