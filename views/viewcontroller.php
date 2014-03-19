<?php

//TODO: get settings from config.php so user can set themes from front-end

include_once('core.php');
$MainView = new viewController('themes/classic/'); //only instantiate this once, right here.
/*
to assign template folder to something other than the default:
either alter creation of $MainView to $MainView = new viewController($themefolder); or instantiate as is and
$MainView->setTemplateFolder($templatefolder); 
*/


//pass control to theme to allow it to alter settings
include_once($MainView->getTheme() . 'themehooks.php');

//add all other required class constructor files
include_once('tableclass.php');
include_once('formclass.php');
include_once('menuclass.php');

?>