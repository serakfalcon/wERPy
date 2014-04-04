    <?php
interface View {
    
    function display();

}

//the themeController trait manages themes & styles
trait ThemeController {
    
    private $currentTheme;
    private $currentStyle;
    private $themes;
        //constructor function (call this inside the constructor if you use this trait)
    private function constructTheme($Theme = null,$Style = null) {
        $this->setTheme($Theme);
        $this->setStyle($Style);
    }
    
        //return the current theme
    public function getTheme() {
        return $this->currentTheme;
    }
    
        //set the template folder. TODO: make sure only valid folders can be set
    public function setTheme($aTheme) {
        $this->currentTheme = ($aTheme) ? 'themes/' . $aTheme : 'themes/default';
    }
     
        //return the current style
    public function getStyle() {
        return $this->currentStyle;
    }
    
        //set the theme style. TODO: make sure only valid styles can be set
    public function setStyle($Style) {
        $this->currentStyle = ($Style) ? $Style : 'aguapop';
    }
       
       //return a link to the default css page
    public function getStyleLink() {
        global $RootPath;
        return $Rootpath . 'views/' . $this->getTheme() . '/styles/' . $this->getStyle();
    }
        
        //return all the available styles and the
    public function getTemplates($refresh = false) {
        global $RootPath;
        
        if (!$refresh && is_array($this->themes)) {
            //not the first time this function has been run in this context
            return $this->themes;
        } elseif (!$refresh && isset($_SESSION['~Themes'])) {
            //not the first time this function has been run (without changes) in this session
            $this->themes = json_decode($_SESSION['~Themes']);
            return $this->themes;
        } else {
            //either a refresh is needed or the function is being run for the first time
            $themes = scandir('views/themes');
            $this->themes = array(); //clear themes if they exist
            foreach ($themes as $theme) {
                //only return valid themes (include xml infos once built)
                $themestyle = array();
                if (is_dir('views/themes/' . $theme) && $theme != '.' && $theme != '..' && $theme != '.svn' && file_exists('views/themes/' . $theme . '/themehooks.php')) {
                    if (is_dir('views/themes/' . $theme . '/styles')) {
                        //theme has styles
                        $styles = scandir('views/themes/' . $theme . '/styles');
                        foreach ($styles as $style) {
                            //only return folders that have default.css so that we're guaranteed of at least having some styling
                            if ( $style != '.' && $style != '..' && $style != '.svn' && file_exists('views/themes/' . $theme . '/styles/' . $style . '/default.css') ) {
                                $themestyle[] = $style; 
                            }
                        }
                        if (count($themestyle) < 1) {
                            $themestyle = false;
                        }
                    } else {
                        $themestyle = false;
                    }

                    $this->themes[] = array('themename' => $theme,
                                            'themefolder' =>$theme, 
                                            'styles' => $themestyle);
                }
            }
            $_SESSION['~Themes'] = json_encode($this->themes);
            return $this->themes;
        }
    }
    
}

//the SiteViewController trait manages general site loading, headers, footers, critical error pages, the company logo and login redirect rendering
trait SiteViewController {
    
    private $headerincludes = array();
    private $footerincludes = array();
    
        //constructor function (call this inside the constructor if you use this trait)
    private function constructSiteView() {
        if (file_exists('views/site_footer.php')) {
            $this->footerincludes[] = 'views/site_footer.php';
        }
        
        if (file_exists('views/site_header.php')) {
            $this->headerincludes[] = 'views/site_header.php';
        }
    }
    
        //get the current company logo if it exists, or return null
    public function getLogo() {
        global $RootPath;
        if(!empty($_SESSION['LogoFile'])) {
            return $_SESSION['LogoFile'];
        } elseif (isset($_SESSION['DatabaseName'])) {
            $dir = $RootPath.'companies/' . $_SESSION['DatabaseName'] . '/';
            $logo = false;
            //is there a logo file (.jpg or .png) in the folder? is it readable? (make sure it's not a directory, for completeness but also probably not needed)
            if (is_readable($dir . 'logo.png') && !is_dir($dir . 'logo.png')) {
                $logo = $dir . 'logo.png';
            } elseif (is_readable($dir . '/logo.jpg') && !is_dir($dir . 'logo.jpg')) {
                $logo = $dir . 'logo.jpg';
            }
                
            if (!$logo) {
                unset($_SESSION['LogoFile']);
                return null;
            } else {
                $_SESSION['LogoFile'] = $logo;
                return $logo;
            }
        } else {
            //no logo because no company! (use webERP logo?)
            return null;
        }
    
    }
    
        //output all relevant header header files
    public function getHeader() {
        global $RootPath;
        global $ViewTopic;
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
    
       //add a file to include into the header or footer
    public function addInclude($includewhere,$includefile) {
        global $RootPath;
        switch($includewhere) {
            case 'header':
                $this->headerincludes[] =  'views/' . $this->currentTheme . '/includes/' . $includefile;
                break;
            case 'footer':
                $this->footerincludes[] = 'views/' . $this->currentTheme . '/includes/' . $includefile;
                break;
        }   
    }
    
        //Login requested. If the current theme has a custom login screen, show that, if not, fall back to default.
    public function displayLogin($loginerr = null) {
        global $Title;
        global $CompanyList;
        $Title = 'webERP Login screen';
        $ThemeLoginTemplate = 'views/' . $this->getTheme() . '/templates/Login.html.php';
        if (file_exists($ThemeLoginTemplate)) {
            include($ThemeLoginTemplate);
            exit;
        } else {
            include('views/Login.php');
            exit;
        }
    }
    
        //there is a critical error that needs to block the rest of the site from rendering.
        //if the current theme has an error handler, use that, if not, fall back to default.
    public function throwCriticalError($title,$message) {
        $errorpage = new criticalErrorsView;
        $errorpage->title = $title;
        $errorpage->display();
    }
}

//the ObjectController trait manages template objects, thus, menus, tables, forms, controls etc.
trait ObjectController {

    private $classes = array();
    private $instancearray = array();
    private $defaults = array();
    
    private function constructObject() {
        //default settings for templates. can override.
        $this->classes['table'] = 'table.html.php';
        $this->classes['form'] = 'form.html.php';
        $this->classes['control'] = 'controls.html.php';
        $this->classes['menu'] = 'menu.html.php';
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
    
        //create a menu, and append the reference to the menu to this class.
    //if a key is specified, append the menu with that key. if the key is taken, return false which will certainly generate an error.
    public function createMenu($whatMenu = null,$key = null) {
        //if the theme has any defaults saved, pass them on to the menu
        if (isset($this->defaults['menu'][$whatMenu])) {
            $defaults = $this->defaults['menu'][$whatMenu];
        } else {
            $defaults = (isset($this->defaults['menu']['~default'])) ? $this->defaults['menu']['~default'] : null;
        }
        $menu = new menuView('views/' . $this->currentTheme . '/templates/', $this->classes['menu'],$whatMenu,$defaults);
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
        $table = new tableView('views/' . $this->currentTheme . '/templates/',$this->classes['table']);
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
        $defaults = (isset($this->defaults['form']['~default'])) ? $this->defaults['form']['~default'] : null;
        $form = new formView('views/' . $this->currentTheme . '/templates/',$this->classes['form'],$defaults);

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
    
    //create a control and append the reference to the control to this class.
    // don't use keys since controls can be referenced by their parent form.
    public function createControl() {
        $control =  new controlView('views/' . $this->currentTheme . '/templates/',$this->classes['control']);
        $this->instancearray['control'][] = $control;
        return $control;
    }
    
}

/*viewController is the factory class for all other views. Only instantiate once, in viewcontroller.php
* if a theme has custom classes, extend viewController and replace $MainView by using new class in themehooks.php
* viewController's functionality is split into traits so you can use the traits you don't want to modify.
* Likewise, a function declared inside a class will override a trait function of the same name
* so if you only want to override a function or two, you can do that simply */
class viewController {
    use ThemeController;
    use SiteViewController;
    use ObjectController;
    
    public function __construct($Theme = null,$Style = null) {
        $this->constructTheme($Theme,$Style);
        $this->constructSiteView();
        $this->constructObject();
        
    }
    
}


?>