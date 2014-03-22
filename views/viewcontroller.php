<?php

//TODO: get settings from config.php so user can set themes from front-end

include_once('core.php');

if (!isset($_SESSION['Style'])) {
    //until sure can replace $SESSION_['Theme'] with style entirely use this
    $_SESSION['Style'] = $_SESSION['Theme'];
}
$MainView = new viewController('classic',$_SESSION['Style']); //only instantiate this once, right here.
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