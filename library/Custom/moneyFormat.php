<?php

class MoneyFormat {
    private $locale = "MY";
    
    function __construct($locale = "MY") {
        $this->_setLocale($locale);
    }
    
    public function _convertToString($number) {
        switch ($this->locale) {
            case "MY":
                return 'RM ' . number_format($number, 2, '.', ', ');
                break;
        }
    }
    
    public function _setLocale($locale) {
        $locale = strtoupper($locale);
        $this->locale = $locale;
    }
}