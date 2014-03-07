<?php
/*****************************
Variables used for the menu
$htmlID, the ID for the <div> containing the menu
$items consisting of menu items containing:
['link'] for the URL
['content'] for the content
['active'] if the link is active
Templates are formatted to make the HTML structure obvious, with PHP to insert information as needed
*/

//code describing menus
?>
<div id="<?php echo $this->htmlID; ?>">
    <ul>
    <?php
        if (isset($this->header['content']) || isset($this->header['image'])) { ?>
            <li class="<?php echo $this->header['class']; ?>">
                <?php 
                    if (isset($this->header['src'])) { 
                ?>
                    <img src="<?php echo $this->header['src']; ?>" <?php echo $this->header['imageattributes']; ?>/>
                <?php } // end if for header image 
                    if (isset($this->header['content'])) {
                        echo $this->header['content'];
                    } ?>
            </li>
    <?php
        } //end IF statement for headers
        
        foreach ($this->items as $menuitem) { ?>
            <li class="<?php 
                            if($menuitem['isActive']) {
                                echo $this->classes['active'];
                            } else {
                                echo ($menuitem['class']) ? $menuitem['class'] : $this->classes['default'];
                            }?>" <?php echo $menuitem['attributes']; ?>>
                <?php if ($menuitem['link']) { ?>
                    <a href="<?php echo $menuitem['link']; ?>">
                        <?php echo $menuitem['content']; ?>
                    </a>
                <?php } else {
                    echo $menuitem['content'];
                } ?>
            </li>
        <?php 
        } /* end foreach loop */ ?>
    </ul>
</div>