<?php


class menuView implements View {
    public $menuID;
    public $submenuID;
    public $htmlID;
    public $callingPage;
    private $items = array();
    private $header = array();
    private $path;
    private $classes = array();
    
    //constructor function
    public function __construct($templatefolder,$classfile,$menuID = null,$defaults = null) {
        $this->path = $templatefolder . $classfile;
        if (isset($menuID)) {
            $this->menuID = $menuID;
        }
        $this->parseDefaults($defaults);
     }
    
    //set the header for this menu
    public function setHeader($headercontent = null,$headerimage = null) {
        if(isset($headercontent) || isset($headerimage)) {
            
            if (is_array($headercontent) && isset($headercontent['content'])) {
                //header content exists and is an array
                $this->header['content'] = $headercontent['content'];
                $this->header['class'] = isset($headercontent['class']) ? $headercontent['class'] : false;
                $this->header['attributes'] = isset($headercontent['attributes']) ? $headercontent['attributes'] : false;
                
            } elseif (isset($headercontent)) {
                //header content is not an array, treat as a string
                $this->header['content'] = $headercontent;
            } elseif ((is_array($headerimage) && !isset($headerimage['src'])) || !isset($headerimage)) {
                //error: header content or image exists but the variables are not formatted properly
                return false;
            }
            
            //if we made it this far, either headerimage is has a value or is an array with a value for 'src'
            if (is_array($headerimage)) {
                //check URL for validity?
                $this->header['src'] = $headerimage['src'];
                $this->header['imageattributes'] = isset($headerimage['attributes']) ? $headerimage['attributes'] : false; 
            } else {
                $this->header['src'] = $headerimage;
            }
            return true;
        } else {
            //error, no header image or content
            return false;
        }
    }
    
    //set the css classes for this menu
    public function setClasses($what,$value) {
        switch($what) {
            case 'active':
            case 'default':
                //fallthrough for valid entries
                $this->classes[$what] = $value;
                return true;
                break;
            default:
                return false;
                break;
        }
    }
    
    //append but do not replace a menu item
    public function addItem($content,$link = null,$isActive = null,$class = null,$attributes = null) {
        if (isset($content)) { 
            $result['content'] = $content;
            $result['link'] = isset($link) ? $link : false; // check if valid URL?
            $result['isActive'] = ($isActive) ? true : false;
            $result['class'] = ($class) ? $class : false;
            $result['attributes'] = isset($attributes) ? $attributes : false;
            $this->items[] = $result;
        }
    }
    
    //insert or replace a menu item
    private function setItem($key,$content = null,$link = null,$isActive = null,$class = null,$attributes = null) {
        if (isset($this->items[$key])) {
            if (isset($content)) {
                $this->items[$key]['content'] = $content;
            }
            if (isset($link)) {
                $this->items[$key]['link'] = $link;
            }
            if (isset($isActive)) {
                 $this->items[$key]['isActive'] = ($isActive) ? true : false;
            }
            if (isset($class)) {
                $this->items[$key]['class'] = $class;
            }
            if (isset($attributes)) {
                $this->items[$key]['attributes'] = $attributes;
            }
        } else {
            $result['content'] = (isset($content)) ? $content : false;
            $result['link'] = (isset($link)) ? $link : false; //ensure is a valid URL?
            $result['isActive'] = ($isActive) ? true : false;
            $result['class'] = (isset($class)) ? $class : false;
            $result['attributes'] = (isset($attributes)) ? $attributes : false;
            $this->items[$key] = $result;
        }
    }
    
    //load menu items from MainMenuLinksArray
    private function loadFromMMLA() {
            global $RootPath;
            global $MenuItems;
            global $ModuleLink;
            global $ModuleList;
        if (isset($this->menuID) && $this->menuID !='MainMenu' && isset($this->submenuID)) {
            $this->htmlID = $MenuItems[$this->menuID][$this->submenuID]['id'];
            $header['class'] = 'menu_group_headers';
            $header['content'] = $MenuItems[$this->menuID][$this->submenuID]['title'];
            $headerimg['src'] = $MenuItems[$this->menuID][$this->submenuID]['src'];
            $headerimg['attributes'] = ' alt="' . $header['content'] . '" title="' . $header['content'] . '"';
            $this->setHeader($header,$headerimg);
            if (is_array($MenuItems[$this->menuID][$this->submenuID]['menu'])) {
                $i = 0;
                foreach ($MenuItems[$this->menuID][$this->submenuID]['menu'] as $url => $caption) {
                    $ScriptNameArray = explode('?', substr($url,1));
                    $PageSecurity = $_SESSION['PageSecurityArray'][$ScriptNameArray[0]];
                    if ((in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PageSecurity))) {
                        $this->setItem($i,$caption,$RootPath . $url);
                    }
                    $i++;
                }
                return true;
            } else {
                return false;
            }
        } elseif ($this->menuID == 'MainMenu') {
            $this->htmlID = 'MainMenuDiv';
            foreach($ModuleLink as $key => $Module) {
                //if user is allowed to see this module
                if ($_SESSION['ModulesEnabled'][$key]==1) {
                    $this->setItem($key,$ModuleList[$key],$this->callingPage . '?Application='. $ModuleLink[$key]);
                    if ($ModuleLink[$key] == $_SESSION['Module']) {
                        //set this module as active
                        $this->setItem($key,null,null,true);
                        //if no active module, set the first allowed module as the active module
                    } elseif (!isset($_SESSION['Module']) OR $_SESSION['Module']=='') {
                        $_SESSION['Module']=$ModuleLink[$key];
                        //set this module as active
                        $this->setItem($key,null,null,true);
                    }
                }
            }
        }
    }
    
        //internalize defaults sent through the constructor function
    private function parseDefaults($defaults = null) {
        if (isset($defaults)) {
            if (is_array($defaults)) {
                foreach($defaults as $setting => $value) {
                    switch($setting) {
                        case 'classactive':
                            $this->setClasses('active',$value);
                            break;
                        case 'classdefault':
                            $this->setClasses('default',$value);
                            break;
                        default:
                    }
                }
            }
        }
    }
    
    public function loadItems($how = null) {
        switch($how) {
            
            default:
                $this->loadfromMMLA();
                break;
        }
    }
    
    function display() {
        if (isset($this->htmlID)) {
            include($this->path);
        }
    }

}


?>