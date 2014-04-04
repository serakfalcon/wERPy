<?php
global $twiggy;
echo $twiggy->render('menu.twig',array( 'htmlID' => $this->htmlID,
                                        'header' => $this->header,
                                        'items' => $this->items,
                                        'classes' => $this->classes));
?>