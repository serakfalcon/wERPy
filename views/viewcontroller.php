<?php

include_once('classes/core.php');

$MainView = new viewController($_SESSION['Theme'],$_SESSION['Style']); //only instantiate this once, right here.
/*
to assign template folder to something other than the default:
either alter creation of $MainView to $MainView = new viewController($themefolder); or instantiate as is and
$MainView->setTemplateFolder($templatefolder); 
*/
//add all other required class constructor files
include_once('classes/tableclass.php');
include_once('classes/formclass.php');
include_once('classes/menuclass.php');

//pass control to theme to allow it to alter settings/classes
include_once($MainView->getTheme() . '/themehooks.php');



?>