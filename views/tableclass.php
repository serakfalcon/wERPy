<?php
class row {
    public $id;
    public $columns = array();
    public $htmlclass;
    public $attributes;
}

//table view construct. use to display tables.
class tableView {
    private $classes = array();
    private $headers = array();
    private $rows = array();
    private $maxRowWidth;
    private $emptyMessage;
    private  $justifyheaders;
    private $title;
    private $titlespan;
    public  $themefolder;
    
    public function __construct() {
        $this->classes['table'] = "";
        $this->classes['headers'] = "";
        $this->classes['columns'] = "";
        $this->classes['rows'] = "";
        $this->emptyMessage = "no content!";
        $this->themefolder = $GLOBALS['templateFolder'];
        $this->justifyheaders = true;
    }
    
    public function setTitle($title,$span) {
        $this->title = $title;
        $this->titlespan = $span;
    }
    
    //sets table component classes, if necessary.
    function setClass($whatclass,$classvalue) {
        switch($whatclass) {
            //valid input from $whatclass
            case "table":
            case "headers":
            case "columns":
            case "rows":
                //fallthrough since output is the same
                $this->classes[$whatclass] = $classvalue;
                $result = true;
                break;
            //invalid input
            default:
                $result = false;
                break;
        }
        return $result;
    }
    
    //take column of input and ensure it's formatted correctly.
    //assume, if $column is not an array, that it is the content for the cell.
    function parseColumns($cell,$isHeader = false) {
        $result = array();
        if (is_array($cell)) {
            //if an array, there are multiple values to store, if any value is found, store it
            //if a value is mandatory give default value if no value is found, if optional report false
            $result['content'] = isset($cell['content']) ? $cell['content'] : "";
            $result['link'] = isset($cell['link']) ? $cell['link'] : false;
            $result['attributes'] = isset($cell['attributes']) ? $cell['attributes'] : false;
            $result['span'] = isset($cell['span']) ? $cell['span'] : 1;
            $result['class'] = isset($cell['class']) ? $cell['class'] : false;
            
            //$isHeader overrides cell settings since it's assumed the header is for a single <thead> row
            if(isset($cell['isheader'])) {
                $result['isheader'] = $isHeader ? $isHeader : $cell['isheader'];
            } else {
                $result['isheader'] = $isHeader;
            }
            
            if(isset($cell['class'])) {
                $result['class'] = $cell['class'];
            } elseif ($isHeader) {
                $result['class'] = $this->classes['headers'];
            } // do nothing if normal table, normal table td/th's don't have class
        } else {
            $result['content'] = $cell;
            $result['link'] = false;
            $result['span'] = 1;
            $result['attributes'] = false;
            $result['isheader'] = $isHeader;
            if ($isHeader) {
                $result['class'] = isset($this->classes['headers']) ? $this->classes['headers'] : false;
            } else {
                $result['class'] = false;
            }
        }
        return $result;
    }
    
    //adds a row to the tableview object. reports success or failure of addition.
    //$id & class are optional, class for css, id if there is a need to use delRow for some reason.
    function addRow($columns,$id = null,$class = null,$attributes = null) {
        $newrow = new row;
        if (is_array($columns)) {
            foreach ($columns as $cell) {
                $newrow->columns[] = $this->parseColumns($cell);
            }
            $newrow->htmlclass = $class ? $class : false;
            $newrow->attributes = $attributes ? $attributes : false;
            if (isset($id)) {
                $this->rows[$id] = $newrow;
            } else {
                $this->rows[] = $newrow;
            }

            return true;
        } else {
            return false;
        }
    }
    
    //replaces the contents of a row with new data. returns false if the id could not be found, or if $columns is not an array.
    function setRow($columns,$id,$class= null,$attributes = null) {
        if(array_key_exists($id,$this->rows) && is_array($columns)) {
            $editrow = $this->rows[$id];
            $editrow->columns = array();
            foreach ($columns as $cell) {
                $editrow->columns[] = $this->parseColumns($cell);
            }

            if (isset($class)) {
                $editrow->htmlclass = $class;
            }
            
            if (isset($attributes)) {
                $editrow->attributes = $attributes;
            }
            return true;
        } else {
            return false;
        }
    }
    
    //removes a row referenced by the key $id. returns true if row is deleted.
    //if no row is found to delete, returns false.
    function delRow($id) {
        if (array_key_exists($id,$this->rows)) {
            unset($this->rows[$id]);
            //reindexing array
            $this->rows = array_values($this->rows);
            return true;
        } else {
            return false;
        }
    }
    
    /* add headers to the tableview object. reports success or failure of addition.
    * If any header is an array, will use the key 'content' for content and 'class' for class. Ignores other keys.
    * If any header is not an array, it will be treated as content.
    * used for a single row inside a <thead> tag. for complicated structures use addRow            */
    function setHeaders($inheaders) {
        
        if (is_array($inheaders)) {
            //clear the array if not empty
            if (!empty($this->headers)) {
                unset($this->headers);
                $this->headers = array();
            }
            foreach ($inheaders as $header) {
                $this->headers[] = $this->parseColumns($header,true);
            }
            return true;
        } else {
            return false;
        }
    }
    
    function display() {
        //if there are headers OR rows, display the table
        if (!empty($this->headers) || empty($this->rows)) {
            
            include($this->themefolder . '/table.html.php');
            return true;
        } else {
        //Neither headers nor rows, table cannot be displayed, return failure
            return false;
        }
    }
    
    
}
?>