wERPy
=====

WebERP GUI changes, view code / working code separation project

Current Status: Work In Progress
===

* [Getting Started](https://github.com/serakfalcon/wERPy/wiki/Getting-Started-%28Developing%29)
* [Goals](https://github.com/serakfalcon/wERPy/wiki/Developing-wERPy)


Goals:
------

 * Refactor existing code to separate out View components (i.e. implementing a MVP design pattern)
 * Integrate jQuery and Bootstrap for a more slick user experience
 * Create 'classic' theme that doesn't have jQuery or Bootstrap (aka it will be indistinguishable from the current version as far as the user is concerned)
 * Delegate includes to be theme-specific
 * separate DB code out from Presentation layer, have Presentation layer reference the API / make the API the go-to place for all database function calls
 
Here is the rough version of menu, table, form and control classes with bootstrap and DataTables.

 * The table class should be able to handle most tables in webERP though some more complicated structures 
(e.g. the Trial Balance) may need customizations.
 * The form and control classes currently support multiple controls per line, but I haven't figured out control height yet. Acceptable for most use-cases.
 * I have been using the aguapop theme to implement the tableviews etc. so some features may look strange with other themes.

Use:
----
 1. copy-paste into your webERP folder. Do not do this on a production site, the code isn't ready.
 2. As a note this will overwrite a number of relatively important files, like header.inc, session.inc, even index.php.
 2. After the last div but still inside the body tag in footer.inc, add:

``` php
$MainView->getFooter();
```

Usage of classes
----------------

*New* See the [wiki](https://github.com/serakfalcon/wERPy/wiki)!
Tables are instantiated by assigning `$MainView->createTable()` to the table variable (e.g. `$tablevar = $MainView->createTable()`)
Forms are instantiated by assigning `$MainView->createForm()` to the  form variable
Menus are instantiated by assigning `$MainView->createMenu()` to the menu variable. a Menu ID can be passed,
which will allow the system to automatically load the appropriate menu items from `$MenuItems`  (`MainMenuLinksArray.php`).
items can be appended/deleted to the menu after it is loaded.
Controls are not instantiated directly, this is handled through the Form's `addControl`/`setControl`/`delControl` functions.
Work To Do:
----

 * Continue to extend formView / create more complicated extension of formView class to cover all use cases
 * Figure out a good way to store and retrieve the menus, allow them to be user-customizable
 * Headers/footers should also be rolled into the themes themselves
 * 'Classic' themes that will cause webERP to render the same way it does now
 * Code refactoring to take advantage of the view classes
