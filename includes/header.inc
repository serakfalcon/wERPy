<?php
/* $Id: header.inc 6573 2014-02-14 23:09:45Z vvs2012 $ */

	// Titles and screen header
	// Needs the file config.php loaded where the variables are defined for
	//  $RootPath
	//  $Title - should be defined in the page this file is included with
    	if (!isset($RootPath)){
		$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
		if ($RootPath == '/' OR $RootPath == "\\") {
			$RootPath = '';
		}
	}
    //viewcontroller is included in session.inc, but if session.inc is not included the viewcontroller must be added
    include_once($_SERVER['DOCUMENT_ROOT'] . $RootPath . '/views/viewcontroller.php');
    $MainView->getHeader();
    echo '</head>';
	echo '<body>';

	echo '<div id="CanvasDiv">';
	echo '<input type="hidden" name="Lang" id="Lang" value="'.$Lang.'" />';
	echo '<div id="HeaderDiv">';
	echo '<div id="HeaderWrapDiv">';


	if (isset($Title)) {


		echo '<div id="AppInfoDiv">'; //===HJ===
			echo '<div id="AppInfoCompanyDiv">';
				echo '<img src="'.$RootPath.'/css/'.$Theme.'/images/company.png" title="'._('Company').'" alt="'._('Company').'"/>' . stripslashes($_SESSION['CompanyRecord']['coyname']);
			echo '</div>';
			echo '<div id="AppInfoUserDiv">';
				echo '<a href="'. $RootPath.'/UserSettings.php"><img src="'.$RootPath.'/css/'.$Theme.'/images/user.png" title="User" alt="'._('User').'"/>' . stripslashes($_SESSION['UsersRealName']) . '</a>';
			echo '</div>';
			echo '<div id="AppInfoModuleDiv">';
				// Make the title text a class, can be set to display:none is some themes
				echo $Title;
			echo '</div>';
		echo '</div>'; // AppInfoDiv


		echo '<div id="QuickMenuDiv"><ul>';

		echo '<li><a href="'.$RootPath.'/index.php">' . _('Main Menu') . '</a></li>'; //take off inline formatting, use CSS instead ===HJ===

		if (count($_SESSION['AllowedPageSecurityTokens'])>1){
			echo '<li><a href="'.$RootPath.'/SelectCustomer.php">' . _('Customers') . '</a></li>';
			echo '<li><a href="'.$RootPath.'/SelectProduct.php">' ._('Items')     . '</a></li>';
			echo '<li><a href="'.$RootPath.'/SelectSupplier.php">' . _('Suppliers') . '</a></li>';

			$DefaultManualLink = '<li><a rel="external" accesskey="8" href="' .  $RootPath . '/doc/Manual/ManualContents.php'. $ViewTopic . $BookMark. '">' . _('Manual') . '</a></li>';

			if (mb_substr($_SESSION['Language'],0,2) != 'en'){
				if (file_exists('locale/'.$_SESSION['Language'].'/Manual/ManualContents.php')){
					echo '<li><a target="_blank" href="'.$RootPath.'/locale/'.$_SESSION['Language'].'/Manual/ManualContents.php'. $ViewTopic . $BookMark. '">' . _('Manual') . '</a></li>';
				} else {
					echo $DefaultManualLink;
				}
			} else {
					echo $DefaultManualLink;
			}
		}

		echo '<li><a href="'.$RootPath.'/Logout.php" onclick="return confirm(\''._('Are you sure you wish to logout?').'\');">' . _('Logout') . '</a></li>';

		echo '</ul></div>'; // QuickMenuDiv
	}
	echo '</div>'; // HeaderWrapDiv
	echo '</div>'; // Headerdiv
	echo '<div id="BodyDiv">';
	echo '<div id="BodyWrapDiv">';

?>
