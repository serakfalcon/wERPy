<?php

class criticalErrorsView uses Views {
    public $title;
    private $cssLink;
    private $path;
    
    
    public function __construct($errorpagename = null) {
        global $MainView;
        $this->cssLink = $MainView->getStyleLink();
        $themeerrorpage = 'views/' . $MainView->getTheme() . '/templates/' . (isset($errorpagename)) ? $errorpagename : 'criticalerrors.html.php';
        //error handling is too important to leave to the theme, if the theme can't implement it
        if (file_exists($themerrorpage)) {
            $this->path = $themerrorpage;
        } else {
            $this->path = 'views/errorhandler.php';
        }
    }
    
    public function display() {
        include($this->path);
        exit;
        
    }

}


?>