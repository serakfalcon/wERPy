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
    private $instancearray = array();
    
    public function __construct($templatefolder = null) {
        //default settings for templates. can override.
        $this->classes['table'] = 'table.html.php';
        $this->classes['form'] = 'form.html.php';
        $this->classes['control'] = 'controls.html.php';
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
    
    public function getInstances($class) {
        if (isset($this->instancearray[$class]) && is_array($this->instancearray[$class])) {
            return $this->instancearray[$class];
        } else {
            return false;
        }
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
    
    //create a table, and append the reference to the table to this class.
    //if a key is specified, append the table with that key. if the key is taken, return false which will certainly generate an error
    public function createTable($key = null) {
        $table = new tableView($this->templateFolder . 'templates/',$this->classes['table']);
        if (isset($key)) {
            if (isset($this->instancearray['table'][$key])) {
                //array key exists
                return false;
            } else {
                $this->instancearray['table'][$key] = $table;
                return $table;
            }
        } else {
            $this->instancearray['table'][] = $table;
            return $table;
        }
    }
    
    //create a form, and append the reference to the form to this class.
    //if a key is specified, append the form with that key. if the key is taken, return false which will certainly generate an error
    public function createForm($key = null) {
        $form = new formView($this->templateFolder . 'templates/',$this->classes['form']);
        if (isset($key)) {
            if (isset($this->instancearray['form'][$key])) {
                //array key exists
                return false;
            } else {
                $this->instancearray['form'][$key] = $form;
                return $form;
            }
        } else {
            $this->instancearray['form'][] = $form;
            return $form;
        }
    }
    
    //create a control. Don't bother appending it to this class, since it is a child of the form that created it.
    public function createControl() {
        return new controlView($this->templateFolder . 'templates/',$this->classes['control']);
    }
    
}


?>