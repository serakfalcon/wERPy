<?php 
/*****************************
Variables used for the control
type -> what kind of control
tabindex -> tabindex value
attributes -> custom html attributes given to the control (class excluded)
htmlclass -> any class extensions (since class is defined here)

for select, use the options array. Options are arrays containing:
['selected'] (true/false)
['value'] (option value)
['attributes'] pre-compiled value and selected. Use this unless there's good reason to access selected / value
['text'] (what the user sees inside the option)

Templates are formatted to make the HTML structure obvious, with PHP to insert information as needed
*/

switch($this->type) { 
    case 'select':
        //fallthrough for yesno: could have custom setting but for now we're keeping it the same as the other select boxes
    /* Display of comboboxes (selection boxes) here */
?>
<div class="col-xs-4 control-label">
    <label><?php echo $this->caption; ?></label>
</div>
<div class="col-xs-8">
    <select tabindex="<?php echo $this->tabindex; ?>" class="form-control <?php echo $this->htmlclass; ?>" <?php echo $this->attributes; ?> >
        <?php foreach ($this->options as $option) { ?>
            <option <?php echo  $option['attributes']; ?>>
                <?php echo $option['text']; ?>
            </option>
        <?php
        } //end option foreach loop
        ?>
    </select>
</div>
<?php
        break;
    case 'yesno':
?>
<div class="col-xs-5 control-label">
    <label><?php echo $this->caption; ?></label>
</div>
<div class="col-xs-7">
    <select tabindex="<?php echo $this->tabindex; ?>" class="form-control <?php echo $this->htmlclass; ?>" <?php echo $this->attributes; ?> >
        <?php foreach ($this->options as $option) { ?>
            <option <?php echo  $option['attributes']; ?>>
                <?php echo $option['text']; ?>
            </option>
        <?php
        } //end option foreach loop
        ?>
    </select>
</div>
<?php
        break;
    case 'submit':
    /*display of submit button here */
?>
<div class="centre">
    <button tabindex="<?php echo $this->tabindex; ?>" type="submit" class="btn btn-default <?php echo $this->htmlclass; ?>" <?php echo $this->attributes; ?>>
        <?php echo $this->caption; ?>
    </button>
</div>
<?php
        break;
    case 'number':
    /* display number box here, different from textbox? */
?>
<div class="col-xs-5 control-label">
    <label><?php echo $this->caption; ?></label>
</div>
<div class="col-xs-7">
    <input tabindex="<?php echo $this->tabindex; ?>" type="number" class="form-control <?php echo $this->htmlclass; ?>" <?php echo $this->attributes; ?> />
</div>
<?php
        break;
    case 'static':
    /* static information, cannot be edited */
?>
<div class="col-xs-4 control-label">
    <label><?php echo $this->caption; ?></label>
</div>
<div class="col-xs-8">
    <p class="form-control form-control-static">
        <?php echo $this->text; ?>
    </p>
</div>
<?php
        break;
    case 'text':
    default:
    //fallthrough, textbox is default
    /* Display of textboxes here : */
?>
<div class="col-xs-4 control-label">
    <label><?php echo $this->caption; ?></label>
</div>
<div class="col-xs-8">
    <input tabindex="<?php echo $this->tabindex; ?>" type="text" class="form-control <?php echo $this->htmlclass; ?>" <?php echo $this->attributes; ?> />
</div>
<?php
    break;
} // end switch of type 
?>