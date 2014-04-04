<?php
global $twiggy;
echo $twiggy->render('form.twig',array( 'id' => $this->id,
                                                        'action' => $this->action,
                                                        'formTitle' => $this->formTitle,
                                                        'FormID' => $this->FormID,
                                                        'hiddencontrols' => $this->hiddenControls,
                                                        'controlRow' => $this->controlRow,
                                                        'controls' => $this->controls));

?>