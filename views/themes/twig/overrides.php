<?php
//overrides to get the new objects
class twigMainView extends viewController {
    
        //create a menu, and append the reference to the menu to this class.
    //if a key is specified, append the menu with that key. if the key is taken, return false which will certainly generate an error.
    public function createMenu($whatMenu = null,$key = null) {
        //if the theme has any defaults saved, pass them on to the menu
        if (isset($this->defaults['menu'][$whatMenu])) {
            $defaults = $this->defaults['menu'][$whatMenu];
        } else {
            $defaults = (isset($this->defaults['menu']['~default'])) ? $this->defaults['menu']['~default'] : null;
        }
        $menu = new twigMenuView('views/' . $this->currentTheme . '/templates/', $this->classes['menu'],$whatMenu,$defaults);
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
        $table = new twigTableView('views/' . $this->currentTheme . '/templates/',$this->classes['table']);
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
        $form = new twigFormView('views/' . $this->currentTheme . '/templates/',$this->classes['form'],$defaults);

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
        $control =  new twigControlView('views/' . $this->currentTheme . '/templates/',$this->classes['control']);
        $this->instancearray['control'][] = $control;
        return $control;
    }
        
}

class twigMenuView extends menuView {
    function display() {
        global $twiggy;
        parent::display();
    }
}

class twigTableView extends tableView {

    //changes: include $twiggy for rendering
    function display() {
        global $twiggy;
        parent::display();
            echo $twiggy->render($this->path,array( 'classes' => $this->classes,
                                                        'attributes' => $this->attributes,
                                                        'title' => $this-> title,
                                                        'headers' => $this->headers,
                                                        'columnCount' => $this->columnCount -1, //offset to make the template happy
                                                        'headerCount' => $this->headerCount,
                                                        'rows' => $this->rows
                                                        ));
    }
}

class twigFormView extends formView {
    function display() {
        global $twiggy;
    }
}

class twigControlView extends controlView {
    function display() {
        global $twiggy;
    }
}


?>