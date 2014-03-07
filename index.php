<?php
$PageSecurity=0;

include('includes/session.inc');
$Title=_('Main Menu');
include('includes/header.inc');


/*The module link codes are hard coded in a switch statement below to determine the options to show for each tab */
include('includes/MainMenuLinksArray.php');

if (isset($SupplierLogin) AND $SupplierLogin==1){
	echo '<table class="table_index">
			<tr>
			<td class="menu_group_item">
				<a href="' . $RootPath . '/SupplierTenders.php?TenderType=1">' . _('View or Amend outstanding offers') . '</a>
			</td>
			</tr>
			<tr>
			<td class="menu_group_item">
				<a href="' . $RootPath . '/SupplierTenders.php?TenderType=2">' . _('Create a new offer') . '</a>
			</td>
			</tr>
			<tr>
			<td class="menu_group_item">
				<a href="' . $RootPath . '/SupplierTenders.php?TenderType=3">' . _('View any open tenders without an offer') . '</a>
			</td>
			</tr>
		</table>';
	include('includes/footer.inc'); 
	exit;
} elseif (isset($CustomerLogin) AND $CustomerLogin==1){
	echo '<table class="table_index">
			<tr>
			<td class="menu_group_item">
				<a href="' . $RootPath . '/CustomerInquiry.php?CustomerID=' . $_SESSION['CustomerID'] . '">' . _('Account Status') . '</a>
			</td>
			</tr>
			<tr>
			<td class="menu_group_item">
				<a href="' . $RootPath . '/SelectOrderItems.php?NewOrder=Yes">' . _('Place An Order') . '</a>
			</td>
			</tr>
			<tr>
			<td class="menu_group_item">
				<a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedCustomer=' . $_SESSION['CustomerID'] . '">' . _('Order Status') . '</a>
			</td>
			</tr>
		</table>';

	include('includes/footer.inc');
	exit;
}

if (isset($_GET['Application'])){ /*This is sent by this page (to itself) when the user clicks on a tab */
	$_SESSION['Module'] = $_GET['Application'];
}


/* script to cache this file.
    Not yet implemented due to the following issue: either the cache will slowly increase over time due to new $_SESSION['FormID']s being generated
    OR the page will not be cached on a per-user basis
    
    
$cachefile = 'views\cache\index-' . $_SESSION['Module'] . '-' . $_SESSION['FormID'] . '.php';
$cachetime = 1800;
//if we've already compiled this page before, and we haven't reached the timeout, add the compiled file and skip all this
if (file_exists($cachefile) && time() - $cachetime < filemtime($cachefile) && !isset($_GET['clearCache'])) {
    include($cachefile);
} else {
    //start buffering output to save to cache
    ob_start();*/

    //=== MainMenuDiv =======================================================================
    //echo '<div id="MainMenuDiv"><ul>'; //===HJ===
    $MainMenu = $MainView->createMenu('MainMenu');
    $MainMenu->callingPage = htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8');
    $MainMenu->loadItems();
    $MainMenu->display(); //MainMenuDiv ===HJ===*/
    
    //=== SubMenuDiv (wrapper) ==============================================================================
    echo '<div id="SubMenuDiv">'; //===HJ===
    $TransMenu = $MainView->createMenu($_SESSION['Module']);
    $TransMenu->submenuID = 'Transactions';
    $TransMenu->loadItems();
    $TransMenu->display(); //=== TransactionsDiv ===

    //=== InquiriesDiv ===
    $InquiriesMenu = $MainView->createMenu($_SESSION['Module']);
    $InquiriesMenu->submenuID = 'Reports';
    $InquiriesMenu->loadItems();
    GetRptLinks($_SESSION['Module'],$InquiriesMenu); //=== GetRptLinks() must be modified!!! ===
    $InquiriesMenu->display(); //=== InquiriesDiv ===

    // MaintenanceDiv ===
    $MaintenanceMenu = $MainView->createMenu($_SESSION['Module']);
    $MaintenanceMenu->submenuID = 'Maintenance';
    $MaintenanceMenu->loadItems();
    $MaintenanceMenu->display(); // MaintenanceDive ===HJ===
    echo '</div>'; // SubMenuDiv ===HJ===
    
    /* bottom part of cache code, see above for outstanding issues
    //write file to cache
    file_put_contents($cachefile,ob_get_contents());
	// Send browser output    
	ob_end_flush();

}*/
include('includes/footer.inc');

function GetRptLinks($GroupID,$InquiriesMenu) {
/*
This function retrieves the reports given a certain group id as defined in /reports/admin/defaults.php
in the acssociative array $ReportGroups[]. It will fetch the reports belonging solely to the group
specified to create a list of links for insertion into a table to choose a report. Two table sections will
be generated, one for standard reports and the other for custom reports.
*/
	global $db, $RootPath, $ReportList;
	require_once('reportwriter/languages/en_US/reports.php');
	require_once('reportwriter/admin/defaults.php');
	$GroupID=$ReportList[$GroupID];
	$Title= array(_('Custom Reports'), _('Standard Reports and Forms'));

	$sql= "SELECT id,
				reporttype,
				defaultreport,
				groupname,
				reportname
			FROM reports
			ORDER BY groupname,
					reportname";
	$Result=DB_query($sql,$db,'','',false,true);
	$ReportList = '';
	while ($Temp = DB_fetch_array($Result)) $ReportList[] = $Temp;

	$RptLinks = '';
	for ($Def=1; $Def>=0; $Def--) {
        //addItem($content,$link,$isActive = null,$class = null,$attributes = null)
        $InquiriesMenu->addItem($Title[$Def],null,null,'menu_group_headers');
		$NoEntries = true;
		if ($ReportList) { // then there are reports to show, show by grouping
			foreach ($ReportList as $Report) {
				if ($Report['groupname']==$GroupID AND $Report['defaultreport']==$Def) {
                    $InquiriesMenu->addItem(_($Report['reportname']),$RootPath . '/reportwriter/ReportMaker.php?action=go&amp;reportid=' . $Report['id']);
					$NoEntries = false;
				}
			}
			// now fetch the form groups that are a part of this group (List after reports)
			$NoForms = true;
			foreach ($ReportList as $Report) {
				$Group=explode(':',$Report['groupname']); // break into main group and form group array
				if ($NoForms AND $Group[0]==$GroupID AND $Report['reporttype']=='frm' AND $Report['defaultreport']==$Def) {
                    //addItem($content,$link,$isActive = null,$class = null,$attributes = null)
                    $InquiriesMenu->addItem('<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/folders.gif" width="16" height="13" alt="" />' . 
                                            '&nbsp;' . $FormGroups[$Report['groupname']],$RootPath . '/reportwriter/FormMaker.php?id=' . $Report['groupname']);
					$NoForms = false;
					$NoEntries = false;
				}
			}
		}
		if ($NoEntries) $InquiriesMenu->addItem( _('There are no reports to show!'),null);
	}
	return $RptLinks;
}

?>