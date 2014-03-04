<?php
/* THEMEHOOKS allows any theme designer to modify the default location of class files
themes can also have their own custom view classes.

to create a new view class, extend viewController and replace $MainView with the new class.
note that pages created with custom view classes will not be viewable by other themes.

tableView is by default implemented by table.html.php
formView is by default implemented by form.html.php
controlView is by default implemented by controls.html.php
you can change this by using $MainView->setClassView
Note that templates must be in themefolder/templates
$MainView->setClassView('tableView',$pathtotableView);
$MainView->setClassView('formView',$pathtoformView);

*/



/*

Add included files (must be in themefolder/includes/)
*/

$MainView->addInclude('header','header.php');
$MainView->addInclude('footer','footer.php');

?>