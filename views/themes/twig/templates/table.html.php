<?php
global $twiggy;
echo $twiggy->render('table.twig',array( 'classes' => $this->classes,
                                                        'attributes' => $this->attributes,
                                                        'title' => $this-> title,
                                                        'headers' => $this->headers,
                                                        'columnCount' => $this->columnCount -1, //offset to make the template happy
                                                        'headerCount' => $this->headerCount,
                                                        'rows' => $this->rows
                                                        ));

?>