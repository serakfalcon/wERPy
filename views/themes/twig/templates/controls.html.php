<?php
    // render a control- control-singlerender.twig calls the macro in controls.twig
    global $twiggy;
echo $twiggy->render('control-singlerender.twig',array( 'type' => $this->type,
                                                        'tabindex' => $this->tabindex,
                                                        'width' => $this->width,
                                                        'height' => $this->height,
                                                        'htmlclass' => $this->htmlclass,
                                                        'attributes' => $this->attributes,
                                                        'caption' => $this->caption,
                                                        'text' => $this->text,
                                                        'options' => ((isset($this->options)) ? $this->options : false)));
?>