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
<div class="small-form">
<form method="post" id="<?php echo $this->id; ?>" action="<?php echo $this->action; ?>" class="form-horizontal" role="form">
    <div>
        <br />
         <input type="hidden" name="FormID" value="<?php echo $this->FormID; ?>" />
        <?php foreach ($this->hiddenControls as $hiddencontrol) { ?>
            <input type="hidden" name="<?php echo $hiddencontrol['name']; ?>" value="<?php echo $hiddencontrol['value'] ?>" />
        <?php
        } /* end of foreach loop */ ?>
            <?php
            if($this->formTitle) { ?>
                    <div class="form-group bg-primary">
                        <div class="col-xs-12">
                            <h4><?php echo $this->formTitle; ?></h4>
                        </div>
                    </div>
            <?php
            }
            foreach ($this->controlRow as $controlRow) { ?>
                <div class="form-group">
                    <?php foreach ($controlRow as $controlkey) { ?>
                        <div class="col-md-<?php echo $this->controls[$controlkey]->width;?> row<?php echo $this->controls[$controlkey]->height; ?>">
                            <?php
                                $this->controls[$controlkey]->display();
                            ?>
                        </div>
                    <?php
                    } // end of controls foreach loop
                    ?>
                </div>
             <?php
             } // end of controlRow foreach loop 
             ?>
        <br />
    </div>
</form>
</div>