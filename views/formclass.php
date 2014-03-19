<?php

class controlView implements View {
    
    public $htmlclass;
    public $tabindex;
    public $caption;
    public $text;
    private $type;
    private $settings; 
    private $attributes; //compiled from settings
    private $options = array();
    private $path;
    public $height;
    public $width;
    
    //viewcontroller will load the appropriate path for the display file
    public function __construct($templatefolder,$classfile) {
        $this->path = $templatefolder . $classfile;
        $this->height = 1;
        $this->width = 1;
    }
    
    public function setType($Type) {
        switch($Type) {
            case 'text':
            case 'select':
            case 'yesno':
            case 'static':
            case 'number':
            case 'submit':
            case 'checkbox':
            case 'button':
            case 'date':
            case 'datetime':
            case 'datetime-local':
            case 'radio':
            case 'range':
            case 'search':
            case 'time':
            case 'file':
            case 'email':
            case 'password':
            case 'url':
            case 'reset':
            case 'hidden':
            case 'content':
                //fallthrough: all valid inputs are treated the same way
                $this->type = $Type;
                return true;
                break;
            default:
                //invalid input, do nothing, report the failure
                return false;
                break;
        }
    }
    
    public function getType() {
        return (isset($this->type)) ? $this->type : false;
    }
    
    /*addOption will add append a new option to the end of the option array. May optionally be given an ID.
     If given an ID, will not overwrite information if the value already exists. */
    public function addOption($text,$value,$isSelected = null,$parentID = null,$id = null) {
        $result['text'] = $text;
        $result['value'] = $value;
        if (isset($isSelected)) {
            $result['selected'] = ($isSelected) ? true : false;
        } else {
            $result['selected'] = false;
        }
        if (isset($parentID)) {
            $result['parentID'] = $parentID;
        } else {
            $result['parentID'] = null;
        }
        if (isset($id)) {
            if (array_key_exists($id,$this->options)) {
                //this option key already exists, do nothing --> add will not add the same ID twice
                return false;
            } else {
                $this->options[$id] = $result;
                return true;
            }
        } else {
            $this->options[] = $result;
            return true;
        }
    }
    
    // setOption will insert or replace an option into the options array based on the key given.
    public function setOption($id,$text = null,$value = null,$isSelected = null,$parentID = null) {
        if(isset($this->options[$id])) {
            if(isset($text)) {
                $this->options[$id]['text'] = $text;
            }
            if(isset($value)) {
                $this->options[$id]['value'] = $value;
            }
            if(isset($isSelected)) {
                $this->options[$id]['selected'] = $isSelected;
            }
            return true;
        } else {
            $result['text'] = isset($text) ? $text : '';
            $result['value'] = isset($value) ? $value : '';
            if (isset($isSelected)) {
                $result['selected'] = ($isSelected) ? true : false;
            } else {
                $result['selected'] = false;
            }
            $this->options[$id] = $result;
            return true;
        }
    }
    
    public function getOptions() {
        $result = array();
        if ($this->getSetting('hasparent')) {
            foreach ($this->options as $option) {   
                $parentID = $option['parentID'];
                $result['o' . $parentID][] = $option;
            }
        } else {
            $result = $this->options;
        }
        return json_encode($result);
    }
    
    //delOption will delete an option from the options array if it exists. returns false if key was non-existent.
    public function delOption($id) {
        if(array_key_exists($id,$this->options)) {
            unset($this->options[$id]);
            return true;
        } else {
            return false;
        }
    }
    
    public function setDimensions($dimensions) {
        if (is_array($dimensions)) {
            $this->height = ($dimensions['height']) ? $dimensions['height'] : 1;
            $this->width = ($dimensions['width']) ? $dimensions['width'] : 1; 
            return true;
        } else {
            //if not array, assume width
            $this->width = ($dimensions) ? $dimensions : 1;
        }
    }
    
    //add a setting value (to be used as html node attributes)
    public function setSetting($setting,$value) {
        switch ($setting) {
            case 'childselect':
            case 'name':
            case 'title':
            case 'value':
            case 'id':
            case 'data-type':
            case 'placeholder':
            case 'max':
            case 'min':
            case 'maxlength':
            case 'pattern':
            case 'size':
            
                //fallthrough, all these settings are treated the same way
                $this->settings[$setting] = $value;
                return true;
                break;
            case 'autofocus':
            case 'required' :
            case 'hasparent':
                /*fallthrough: autofocus, required & hasparent must be true or false.
                this current setup allows the input of setSetting('required','required') to work */
                $this->settings[$setting] = ($value) ? true : false;
                return true;
                break;
                
                //selectYes and selectNo can BOTH be false, but selectYes and selectNo cannot BOTH be true.
            case 'selectYes':
                //for 'yesno' type only
                $this->settings['selectYes'] = ($value) ? true : false;
                if ($value) {
                    $this->settings['selectNo'] = false;
                }
                return true;
                break;
            case 'selectNo':
                //for 'yesno' type only
                $this->settings['selectNo'] = ($value) ? true : false;
                if ($value) {
                    $this->settings['selectYes'] = false;
                }
                return true;
                break;
            case 'text':
                $this->text = $value;
                return true;
                break;
            default:
                //invalid input, do nothing, report the failure.
                return false;
                break;
        }
    }
    
    public function getSetting($setting) {
        if (isset($this->settings[$setting])) {
            return $this->settings[$setting];
        } else {
            return null;
        }
    }
    //take settings (from workhorse logic) and prepare for output to controls.html.php 
    private function compileSettings() {
        $this->attributes = '';
        if (is_array($this->settings)) {
            foreach ($this->settings as $key => $setting) {
                switch($key) {
                    case 'name':
                    case 'title':
                    case 'value':
                    case 'data-type':
                    case 'placeholder':
                    case 'min':
                    case 'max':
                    case 'maxlength':
                    case 'size':
                    case 'id':
                    case 'data-type':
                    case 'pattern':
                    case 'size':
                        //fallthrough as these are treated the same way
                        $this->attributes .= ' ' . $key . '="' . $setting . '"';
                        break;
                    case 'autofocus':
                    case 'required' :
                         //fallthrough as these are treated the same way
                        $this->attributes .= ($setting) ? ' ' . $key . '="' . $key . '"' : '';
                        break;
                    case 'childselect':
                        $this->attributes .= ' onChange="filterChild(' . "'" . $setting . "'" . ',this.selectedIndex,' . $setting . '_json);"';
                    default:
                        //any other attribute is not recognized.
                        break;
                }
            } // end foreach on settings
        }
        
            //add Yes No options if it's a yesno control
        if ($this->type == 'yesno') {
            //if selectYes and selectNo exist, only one can be true, the other is not selected.
            if (isset($this->settings['selectYes']) && isset($this->settings['selectNo'])) {
                if($this->settings['selectYes']) {
                    $this->setOption(1,_('Yes'),1,true);
                    $this->setOption(2,_('No'),0,false);
                } else {
                    $this->setOption(1,_('Yes'),1,false);
                    $this->setOption(2,_('No'),0,true);
                }
            } else {
                //either selectYes or selectNo exists, which likely means neither is selected, but, just in case
                if (isset($this->settings['selectYes'])) {
                    $this->setOption(1,_('Yes'),1,$this->settings['selectYes'] ? true : false);
                    $this->setOption(2,_('No'),0);
                } elseif (isset($this->settings['selectNo'])) {
                    $this->setOption(1,_('Yes'),1);
                    $this->setOption(2,_('No'),0,$this->settings['selectNo'] ? true : false);
                } else {
                    $this->setOption(1,_('Yes'),1);
                    $this->setOption(2,_('No'),0);
                }
                
            }
        } //End if yesno
        
        if (is_array($this->options)) {
            foreach ($this->options as $key => $option) {
                $this->options[$key]['attributes'] = isset($option['value']) ? ' value="' . $option['value'] . '"' : '';
                $this->options[$key]['attributes'] .= ($option['selected']) ? ' selected="selected"' : '';
            }
        }
    }
    
    public function display() {
        if($this->type && isset($this->tabindex)) {
            //prepare all settings for the template so it can use them all as $this->attributes
            $this->compileSettings();
            include($this->path);
            return true;
        } else {
            //error, could not display control, invalid settings
            return false;
        }
    }
    
}

class formView implements View {

    public $id;
    public $formTitle;
    private $FormID;
    private $action;
    private $controls = array();
    private $controlRow = array();
    private $hiddenControls = array();
    private $path;
    private $usingBootstrap;
    
    //initialize from MainView with path to this class's file, and 
    public function __construct($templatefolder,$classfile,$defaults) {
        $this->path = $templatefolder . $classfile;
        $this->FormID = $_SESSION['FormID']; // for session security
        $this->parseDefaults($defaults);
    }
    
    private function parseDefaults($defaults) {
        if (is_array($defaults)) {
            foreach($defaults as $setting => $value) {
                switch($setting) {
                    case 'usingBootstrap':
                        $this->usingBootstrap = ($value) ? true : false;
                }
            }
        } elseif (isset($defaults)) {
            //if defaults is not an array, its a value, what value should it be??
        }
    }
    
    public function setAction($whataction) {
        $this->action = $whataction;
    }
    
    //adds a hidden control, to allow the form/javascript to pass values back to the server on $_POST
    //hidden controls can be specified manually using addControl, if you need more settings.
    //will not overwrite a hidden control if it exists already.
    public function addHiddenControl($name,$value,$id = null) {
        $result['name'] = $name;
        $result['value'] = $value;
        if(isset($id)) {
            if(array_key_exists($id,$this->hiddenControls)) {
                //error: $id already exists
                return false;
            } else {
                $this->hiddenControls[$id] = $result;
                return true;
            }
        } else {
            $this->hiddenControls[] = $result;
            return true;
        }
    }
    
    //edit an existing hidden control. Insert if key doesn't exist.
    public function setHiddenControl($id,$name = null,$value = null) {
        if (array_key_exists($id,$this->hiddenControls)) {
            if (isset($name)) {
                $this->hiddenControls[$id]['name'] = $name;
            }
            if (isset($value)) {
                $this->hiddenControls[$id]['value'] = $value;
            }
            return true;
        } else {
            if(isset($id) && isset($name) && isset($value)) {
                $result['name'] = $name;
                $result['value'] = $value;
                $this->hiddenControls[$id] = $result;
                return true;
            } else {
                //error, could not set hiddencontrol: name, value or id missing
                return false;
            }
        }
    }
    
    //remove a hidden control
    public function delHiddenControl($id) {
        if(array_key_exists($id,$this->hiddenControls)) {
            unset($this->hiddenControls[$id]);
            return true;
        } else {
            //no key $id exists
            return false;
        }
    }
    
    //adds a control to the form. $key and $tabindex are required.
    public function addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null,$row = null,$order = null,$dimensions = null) {
        $newControl = $GLOBALS['MainView']->createControl();
        if(array_key_exists($key,$this->controls)) {
            //report error: could not add control : key exists already
            return false;
        } elseif (!$newControl->setType($type)) {
            //report error: could not add control : type not valid 
            return false;
        } else {
            $newControl->tabindex = (is_int($tabindex)) ? $tabindex : 0;
            $newControl->caption = (isset($caption)) ? $caption : '';
            $newControl->htmlclass = (isset($htmlclass)) ? $htmlclass : '';
            if (is_array($settings)) {
                foreach ($settings as $whatSetting => $settingValue) {
                    $newControl->setSetting($whatSetting,$settingValue);
                }
            }
            $newControl->setDimensions($dimensions);
            $this->controls[$key] = $newControl;
            //do we have positional information?
            if(isset($row)) {
                if(isset($order)) {
                    //does an element already exist in this position?
                    if (isset($this->controlRow[$row][$order])) {
                        //yes, so append this right after that element
                        $result = array_slice($this->controlRow[$row],0,$order,true) + array($order => $key) + array_slice($this->controlRow[$row],$order,null,true);
                        $this->controlRow[$row] = $result;
                    } else {
                        //no, so we can assign the order to this element
                        $this->controlRow[$row][$order] = $key;
                    }
                } else {
                    $this->controlRow[$row][] = $key;
                } 
            } else {
                if(isset($order)) {
                    $this->controlRow[][$order] = $key;
                } else {
                    $this->controlRow[][0] = $key;
                }
            }
            return true;
        }
    }
    
    //edits an existing control. if control doesn't exist, create it with the specific key. Any arguments not passed will be ignored.
    public function setControl($key,$tabindex = null,$type = null,$caption = null,$settings = null,$htmlclass = null,$row = null,$order = null, $dimensions = null) {
        if (array_key_exists($key,$this->controls)) {
            $editControl = $this->controls[$key];
            if (isset($tabindex)) {
                $editControl->tabindex = $tabindex;
            }
            if (isset($type)) {
                $editControl->setType($type);
            }
            if (isset($caption)) {
                $editControl->caption = $caption;
            }
            if (isset($settings) && is_array($settings)) {
                foreach ($settings as $whatSetting => $settingValue) {
                    $editControl->setSetting($whatSetting,$settingValue);
                }
            }
            if (isset($htmlclass)) {
                $editControl->htmlclass = $htmlclass;
            }
            if (isset($dimensions)) {
                $editControl->setDimensions($dimensions);
            }
            //if there is a row value set
            if (isset($row)) {
                    //find this key
                $found = false;
                foreach ($this->controlRow as $rowControl) {
                    foreach ($rowControl as $controlOrder => $controlKey) {
                        if ($controlKey === $key) {
                            $found = true;
                            break 2;
                        }
                    }
                }
                //location in array is $rowControl $controlOrder
                
                //does this position already exist
                if (isset($order) && isset($this->controlRow[$row][$order])) {
                    
                    if ($this->controlRow[$row][$order] !== $key) {
                        //if the position exists, and it's not this key, add this key after the existing order (if it is this key, do nothing)
                        $result = array_slice($this->controlRow[$row],0,$order,true) + array($order => $key) + array_slice($this->controlRow[$row],$order,null,true);
                        $this->controlRow[$row] = $result;
                        
                        if ($found) {
                            //if this key was already in the controlRow array somewhere, remove it
                            unset($this->controlRow[$rowControl][$controlOrder]);
                        }
                    }
                    
                } else {
                    //order does not exist yet, or is not set
                    if (isset($order)) {
                        $this->controlRow[$row][$order] = $key;
                    } else {
                        //order is not set, append to the end
                        $this->controlRow[$row][] = $key;
                    }
                    
                    if ($found) {
                        //if this key was already in the controlRow array somewhere, remove it
                        unset($this->controlRow[$rowControl][$controlOrder]);
                    }
                }
                
            } // end if isset($row)
            
            return true;
        } else {
            if (isset($key) && isset($type)) {
                return $this->addControl($key,$tabindex,$type,$caption,$settings,$htmlclass,$row,$order,$dimensions);
            } else {
                //error: control could not be added
                return false;
            }
        }
    }
    
    
    //remove an existing control
    public function delControl($key) {
        if (isset($this->controls[$key])) {
            unset($this->controls[$key]);
            return true;
        } else {
            return false;
        }
    }
    
    //add an option to a control (only used for select controls)
    public function addControlOption($key,$text,$value,$isSelected = null,$parentID = null,$id = null) {
        if (isset($this->controls[$key])) {
            //report success or failure based on addOption
            //addOption($text,$value,$isSelected = null,$id = null)
            return $this->controls[$key]->addOption($text,$value,$isSelected,$parentID,$id);
        } else {
            //error, key not found
            return false;
        }
    }
    
    //overwrite or create a new option for the control
    public function setControlOption($key,$id,$text = null,$value = null,$isSelected = null,$parentID = null) {
        if (array_key_exists($key,$this->controls)) {
            //report success or failure based on setoption
            return $this->controls[$key]->setOption($id,$text,$value,$isSelected,$parentID);
        } else {
            //error, key not found
            return false;
        }
    }
    
    //remove a specific option from control options
    public function delControlOption($key,$id) {
        if(array_key_exists($key,$this->controls)) {
            //report success or failure of delOption
            return $this->controls[$key]->delOption($id);
        } else {
            //error, key not found
            return false;
        }
    }
    
    public function display() {
        //if the minimal amount of information required to set this form has been prepared
        if (isset($this->FormID) && isset($this->action)) {
            if ($this->usingBootstrap) {
                //pad out the widths of any control rows that are less than 12 long (if 12 or more, ignore)
                //12 is a magic number from bootstrap that means 100% width
                foreach($this->controlRow as $controlRow) {
                    $count = 0;
                    foreach($controlRow as $key) {
                        $count += $this->controls[$key]->width;
                    }
                    if ($count < 12) {
                        //if the sum of the widths are greater than 0, it's safe to divide by it, adjust all widths accordingly
                        if ($count > 0) {
                            $widthmultiplier = 12 / $count;
                            foreach($controlRow as $key) {
                                $this->controls[$key]->width = intval($this->controls[$key]->width * $widthmultiplier);
                            }
                        } else {
                            //the sum of the widths is zero, some joker thought it'd be funny, make them all equal
                            $widthresult = 12 / count($controlRow);
                            foreach($controlRow as $key) {
                                $this->controls[$key]->width = intval($widthresult);
                            }
                        }
                    }
                }
            } else {
                $maxcount = 1;
                //find the widest row (also keep track of the widths of each row)
                $i = 0;
                $rowwidth = array();
                foreach($this->controlRow as $controlRow) {
                    $count = 0;
                    foreach ($controlRow as $key) {
                        $count += $this->controls[$key]->width;
                    }
                    $rowwidth[$i] = ($count) ? $count : 1;
                    if ($count > $maxcount) {
                        $maxcount = $count;
                    }
                    $i++;
                }
                //adjust all columns to fit
                $i = 0;
                foreach($this->controlRow as $controlRow) {
                    $count = 0;
                    foreach($controlRow as $key) {
                        //adjust each control row to be the full width
                        $this->controls[$key]->width = intval($this->controls[$key]->width * ( $maxcount / $rowwidth[$i]));
                        $count += $this->controls[$key]->width;
                    }
                    if ($count < $maxcount) {
                        //if this row is smaller (due to rounding) expand the last control box
                        $this->controls[$key]->width += $maxcount - $count;
                    }
                    $i++; //iterator to keep track of previously calculated row widths
                }
            }
            include($this->path);
        } else {
            //error, form not properly set up
            return false;
        }
    }

}

?>