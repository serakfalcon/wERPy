<?php
/* THEMEHOOKS allows any theme designer to modify the default location of class files
themes can also have their own custom view classes.

to create a new view class, extend viewController and replace $MainView with the new class.
note that pages created with custom view classes will not be viewable by other themes.

*/

/* prepare TWIG templater */
require_once('includes/Twig/lib/Twig/Autoloader.php');
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem('views/themes/twig/templates');
global $twiggy;
$twiggy = new Twig_Environment($loader, array(
    'cache' => 'views/cache/twigcache',
    'autoescape' => false
));

function twigGetControl(&$control) {
    $control->display();
    return true;
}


$twiggy->registerUndefinedFunctionCallback('twigGetControl');
/*

tableView is by default implemented by table.html.php
formView is by default implemented by form.html.php
controlView is by default implemented by controls.html.php
you can change this by using $MainView->setClassView
Note that templates must be in themefolder/templates
$MainView->setClassView('tableView',$pathtotableView);
$MainView->setClassView('formView',$pathtoformView);

in this case, we'll call TWIG from within each default file, to reference the real twig file

*/

//Set up theme defaults for menus
//setDefault($classtype,$setting,$value,$classID = null) ($classId for specific menus only)
$MainView->setDefault('menu','classdefault','menu_group_item');
$MainView->setDefault('menu','classdefault','main_menu_unselected','MainMenu');
$MainView->setDefault('menu','classactive','main_menu_selected','MainMenu');
$MainView->setDefault('form','usingBootstrap',false);
/*

Add files that will be directly inserted into the header and footer (must be in themefolder/includes/)
*/

$MainView->addInclude('header','header.php');
//$MainView->addInclude('footer','footer.php');


?>