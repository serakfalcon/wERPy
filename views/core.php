<?php
interface View {
    
    function display();

}

//viewController is the factory class for all other views. Only instantiate once, in viewcontroller.php
//if a theme has custom classes, extend viewController and replace $MainView by using new class in themehooks.php
class viewController {
    private $templateFolder;
    private $classes = array();
    private $headerincludes = array();
    private $footerincludes = array();
    
    public function __construct($templatefolder = null) {
        //default settings for templates. can override.
        $this->classes['tableView'] = 'table.html.php';
        $this->classes['formView'] = 'form.html.php';
        $this->classes['controlView'] = 'controls.html.php';
        $this->setTemplateFolder($templatefolder);
    }
    
    //set the template folder. TODO: make sure only valid folders can be set
    public function setTemplateFolder($templatefolder) {
        $this->templateFolder = (isset($templatefolder)) ? $templatefolder : 'themes/default/';
    }
    
    //return the template folder, in case it's needed.
    public function getTemplateFolder() {
        return 'themes/default/';
    }
    
    //override class views in the event the theme has a different naming scheme
    public function setClassView($class,$viewlocation) {
        if(isset($this->classes[$class])) {
            $this->classes[$class] = $viewlocation;
            return true;
        } else {
            //error, could not set classView
            return false;
        }
    }
    
    public function addInclude($includewhere,$includefile) {
        switch($includewhere) {
            case 'header':
                $this->headerincludes[] = $this->templateFolder . 'includes/' . $includefile;
                break;
            case 'footer':
                $this->footerincludes[] = $this->templateFolder . 'includes/' . $includefile;
                break;
        }
    }
    
    public function getHeader() {
        foreach ($this->headerincludes as $includefile) {
            include_once($includefile);
        }
    }
    
    public function getFooter() {
        foreach ($this->footerincludes as $includefile) {
            include_once($includefile);
        }
    }
    
    public function createTable() {
        return new tableView($this->templateFolder . 'templates/',$this->classes['tableView']);
    }
    
    public function createForm() {
        return new formView($this->templateFolder . 'templates/',$this->classes['formView']);
    }
    
    public function createControl() {
        return new controlView($this->templateFolder . 'templates/',$this->classes['controlView']);
    }
    
}


?>