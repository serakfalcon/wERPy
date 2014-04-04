<?php

class criticalException extends Exception {
    private $type;
    private $Prefix;
    public function __construct($message,$type = 'error',$Prefix = null,$code = 0,Exception $previous = null) {
        $this->type = $type;
        $this->Prefix = $Prefix;
        parent::__construct($message,$code,$previous)
    }
    
    public function display() {
        return getMsg($this->message,$this->type,$this->Prefix);
    }
    
}


class ErrorHandler {
    public $title;
    private $cssLink;
    private $path;
    private $error;
    
    public function __construct($templatefolder,$classfile,$styleLink,$error = _('Undefined Error'),$title = _('Critical Error')) {
        $this->cssLink = $styleLink;
        $themeerrorpage = 'views/' . $MainView->getTheme() . '/templates/' . (isset($errorpagename)) ? $errorpagename : 'criticalerrors.html.php';
        //error handling is too important to leave to the theme, if the theme can't implement it
        if (file_exists($themerrorpage)) {
            $this->path = $themerrorpage;
        } else {
            $this->path = 'views/errorhandler.php';
        }
    }
    
    public function useException($PHPError) {
        
    }
    
    public function throwException() {
    
    }
    
    public function display() {
        include($this->path);
        exit;
        
    }

}


?>