<?php 
/*****************************
Variables used for the form
formView variables
id: what form it is
action: form action
method: form method
hiddencontrols: array of name and value for hidden controls. hidden controls have no settings.
formtitle: title of the form
controls: array of control objects.
controlrow: multiarray of rows, and order within those rows. values are control references.
for this template caption and display properties are all that is required.
For more, see controls.html.php

Templates are formatted to make the HTML structure obvious, with PHP to insert information as needed
*/
?>
<form method="post" id="<?php echo $this->id; ?>" action="<?php echo $this->action; ?>" class="form-horizontal" role="form">
    <table>
    <?php
        if($this->formTitle) { 
        ?>
            <caption>
                <?php echo $this->formTitle; ?>
            </caption>
        <?php
        } 
    ?>
    <input type="hidden" name="FormID" value="<?php echo $this->FormID; ?>" />
        <?php foreach ($this->hiddenControls as $hiddencontrol) { ?>
            <input type="hidden" name="<?php echo $hiddencontrol['name']; ?>" value="<?php echo $hiddencontrol['value'] ?>" />
        <?php
        } /* end of foreach loop */
        
            foreach ($this->controlRow as $controlRow) { ?>
                <tr>
                    <?php foreach ($controlRow as $controlkey) {
                        $this->controls[$controlkey]->display();
                    } // end of controls foreach loop
                    ?>
                </tr>
             <?php
             } // end of controlRow foreach loop 
             ?>
        <br />
    </table>
</form>