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
    case 'content':
    //special type for inserting content into the form, could be explanatory text, a picture, what have you.
?>
<td colspan="<?php echo $this->width * 2; ?>" rowspan="<?php echo $this->height; ?>">
    <div class="<?php echo $this->htmlclass; ?>" <?php echo $this->attributes; ?>>
        <?php echo $this->text; ?>
    </div>
</td>
<?php
        break;
    case 'select':
    /* Display of comboboxes (selection boxes) here */
?>
<td colspan="<?php echo $this->width; ?>" rowspan="<?php echo $this->height; ?>">
    <label><?php echo $this->caption; ?></label>
</td>
<td colspan="<?php echo $this->width; ?>" rowspan="<?php echo $this->height; ?>">
    <select tabindex="<?php echo $this->tabindex; ?>" class="form-control <?php echo $this->htmlclass; ?>" <?php echo $this->attributes; ?> >
        <?php foreach ($this->options as $option) { ?>
            <option <?php echo  $option['attributes']; ?>>
                <?php echo $option['text']; ?>
            </option>
        <?php
        } //end option foreach loop
        ?>
    </select>
</td>
<?php
        break;
    case 'yesno':
?>
<td colspan="<?php echo $this->width; ?>" rowspan="<?php echo $this->height; ?>">
    <label><?php echo $this->caption; ?></label>
</td>
<td colspan="<?php echo $this->width; ?>" rowspan="<?php echo $this->height; ?>">
    <select tabindex="<?php echo $this->tabindex; ?>" class="form-control <?php echo $this->htmlclass; ?>" <?php echo $this->attributes; ?> >
        <?php foreach ($this->options as $option) { ?>
            <option <?php echo  $option['attributes']; ?>>
                <?php echo $option['text']; ?>
            </option>
        <?php
        } //end option foreach loop
        ?>
    </select>
</td>
<?php
        break;
    case 'submit':
    /*display of submit button here */
?>
<td colspan="<?php echo $this->width * 2; ?>" rowspan="<?php echo $this->height; ?>">
    <div class="centre">
        <button tabindex="<?php echo $this->tabindex; ?>" type="submit" class="btn btn-default <?php echo $this->htmlclass; ?>" <?php echo $this->attributes; ?>>
            <?php echo $this->caption; ?>
        </button>
    </div>
</td>
<?php
        break;
    case 'number':
    /* display number box here, different from textbox? */
?>
<td colspan="<?php echo $this->width; ?>" rowspan="<?php echo $this->height; ?>">
    <label><?php echo $this->caption; ?></label>
</td>
<td colspan="<?php echo $this->width; ?>" rowspan="<?php echo $this->height; ?>">
    <input tabindex="<?php echo $this->tabindex; ?>" type="number" class="form-control <?php echo $this->htmlclass; ?>" <?php echo $this->attributes; ?> />
</td>
<?php
        break;
    case 'static':
    /* static information, cannot be edited */
?>
<td colspan="<?php echo $this->width; ?>" rowspan="<?php echo $this->height; ?>">
    <label><?php echo $this->caption; ?></label>
</td>
<td colspan="<?php echo $this->width; ?>" rowspan="<?php echo $this->height; ?>">
    <p class="form-control form-control-static">
        <b><?php echo $this->text; ?></b>
    </p>
</td>
<?php
        break;
    case 'radio':
    /* radio controls: allow controlOptions */
    if (!empty($this->options)) {
?>
    <td colspan="<?php echo $this->width * 2; ?>" rowspan="<?php echo $this->height; ?>">
        <label tabindex="<?php echo $this->tabindex; ?>"><?php echo $this->caption;?></label>
    </tr>
    <tr>
    <td colspan="<?php echo $this->width * 2; ?>" rowspan="<?php echo $this->height; ?>">
        <?php
            $i = 0;
            foreach ($this->options as $option) {
                ?>
                <input tabindex="-1" type="radio" class="form-control <?php echo $this->htmlclass; ?>" <?php echo $option['attributes']; echo $this->attributes; ?> />
                    <?php echo $option['text']; ?>
                <?php echo ($i) ? '<br />' : ''; 
                    $i++;
            }
    } else { 
    ?>
        <td colspan="<?php echo $this->width; ?>" rowspan ="<?php echo $this->height; ?>">
            <label><?php echo $this->caption; ?></label>
        </td>
        <td colspan="<?php echo $this->width; ?>" rowspan="<?php echo $this->height; ?>">
            <input tabindex="<?php echo $this->tabindex; ?>" type="radio" class="form-control <?php echo $this->htmlclass; ?>" <?php echo $this->attributes; ?> />
        </td>
    <?php
    }
        break;
    case 'text':
    default:
    //fallthrough, textbox is default
    /* Display of textboxes here : */
?>
<td colspan="<?php echo $this->width; ?>" rowspan="<?php echo $this->height; ?>">
    <label><?php echo $this->caption; ?></label>
</td>
<td colspan="<?php echo $this->width; ?>" rowspan="<?php echo $this->height; ?>">
    <input tabindex="<?php echo $this->tabindex; ?>" type="<?php echo $this->type; ?>" class="form-control <?php echo $this->htmlclass; ?>" <?php echo $this->attributes; ?> />
</td>
<?php
    break;
} // end switch of type 
?>