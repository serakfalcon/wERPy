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
    private $defaults = array();
    
    public function __construct($templatefolder = null) {
        //default settings for templates. can override.
        $this->classes['table'] = 'table.html.php';
        $this->classes['form'] = 'form.html.php';
        $this->classes['control'] = 'controls.html.php';
        $this->classes['menu'] = 'menu.html.php';
        $this->setTemplateFolder($templatefolder);
    }
    
    //set up template defaults. Mostly used for menu/class assignment
    public function setDefault($classtype,$setting,$value,$classID = null) {
        //if we have a template for this type of class, aka it is a valid class
        if (array_key_exists($classtype,$this->classes)) {
            if (isset($classID)) {
                //only apply to classes with a certain ID
                $this->defaults[$classtype][$classID][$setting] = $value;
            } else {
                //apply as default
                $this->defaults[$classtype]['~default'][$setting] = $value;
            }
        }
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
    
    public function getObject($objectclass,$key) {
        if(isset($this->instancearray[$objectclass][$key])) {
            return $this->instancearray[$objectclass][$key];
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
    
    //add a file to include into the header or footer
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
    
    //output all relevant header header files
    public function getHeader() {
        foreach ($this->headerincludes as $includefile) {
            include_once($includefile);
        }
    }
    
    //output all relevant footer files
    public function getFooter() {
        foreach ($this->footerincludes as $includefile) {
            include_once($includefile);
        }
    }
    
    //create a menu, and append the reference to the menu to this class.
    //if a key is specified, append the menu with that key. if the key is taken, return false which will certainly generate an error.
    public function createMenu($whatMenu = null,$key = null) {
        //if the theme has any defaults saved, pass them on to the menu
        if (isset($this->defaults['menu'][$whatMenu])) {
            $defaults = $this->defaults['menu'][$whatMenu];
        } else {
            $defaults = (isset($this->defaults['menu']['~default'])) ? $this->defaults['menu']['~default'] : null;
        }
        $menu = new menuView($this->templateFolder . 'templates/', $this->classes['menu'],$whatMenu,$defaults);
        if (isset($key)) {
            if (isset($this->instancearray['menu'][$key])) {
                //array key exists
                return false;
            } else {
                $this->instancearray['menu'][$key] = $menu;
                return $menu;
            }
        } else {
            $this->instancearray['menu'][] = $menu;
            return $menu;
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