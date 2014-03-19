<?php

/* $Id: AccountSections.php 6312 2013-08-30 21:08:37Z daintree $*/

include('includes/session.inc');

$Title = _('Account Sections');

$ViewTopic = 'GeneralLedger';
$BookMark = 'AccountSections';

include('includes/header.inc');

// SOME TEST TO ENSURE THAT AT LEAST INCOME AND COST OF SALES ARE THERE
	$sql= "SELECT sectionid FROM accountsection WHERE sectionid=1";
	$result = DB_query($sql,$db);

	if( DB_num_rows($result) == 0 ) {
		$sql = "INSERT INTO accountsection (sectionid,
											sectionname)
									VALUES (1,
											'Income')";
		$result = DB_query($sql,$db);
	}

	$sql= "SELECT sectionid FROM accountsection WHERE sectionid=2";
	$result = DB_query($sql,$db);

	if( DB_num_rows($result) == 0 ) {
		$sql = "INSERT INTO accountsection (sectionid,
											sectionname)
									VALUES (2,
											'Cost Of Sales')";
		$result = DB_query($sql,$db);
	}
// DONE WITH MINIMUM TESTS


if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;
	$i=1;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (isset($_POST['SectionID'])) {
		$sql="SELECT sectionid
					FROM accountsection
					WHERE sectionid='".$_POST['SectionID']."'";
		$result=DB_query($sql, $db);

		if ((DB_num_rows($result)!=0 AND !isset($_POST['SelectedSectionID']))) {
			$InputError = 1;
			prnMsg( _('The account section already exists in the database'),'error');
			$Errors[$i] = 'SectionID';
			$i++;
		}
	}
	if (ContainsIllegalCharacters($_POST['SectionName'])) {
		$InputError = 1;
		prnMsg( _('The account section name cannot contain any illegal characters') ,'error');
		$Errors[$i] = 'SectionName';
		$i++;
	}
	if (mb_strlen($_POST['SectionName'])==0) {
		$InputError = 1;
		prnMsg( _('The account section name must contain at least one character') ,'error');
		$Errors[$i] = 'SectionName';
		$i++;
	}
	if (isset($_POST['SectionID']) AND (!is_numeric($_POST['SectionID']))) {
		$InputError = 1;
		prnMsg( _('The section number must be an integer'),'error');
		$Errors[$i] = 'SectionID';
		$i++;
	}
	if (isset($_POST['SectionID']) AND mb_strpos($_POST['SectionID'],".")>0) {
		$InputError = 1;
		prnMsg( _('The section number must be an integer'),'error');
		$Errors[$i] = 'SectionID';
		$i++;
	}

	if (isset($_POST['SelectedSectionID']) AND $_POST['SelectedSectionID']!='' AND $InputError !=1) {

		/*SelectedSectionID could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$sql = "UPDATE accountsection SET sectionname='" . $_POST['SectionName'] . "'
				WHERE sectionid = '" . $_POST['SelectedSectionID'] . "'";

		$msg = _('Record Updated');
	} elseif ($InputError !=1) {

	/*SelectedSectionID is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new account section form */

		$sql = "INSERT INTO accountsection (sectionid,
											sectionname
										) VALUES (
											'" . $_POST['SectionID'] . "',
											'" . $_POST['SectionName'] ."')";
		$msg = _('Record inserted');
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db);
		prnMsg($msg,'success');
		unset ($_POST['SelectedSectionID']);
		unset ($_POST['SectionID']);
		unset ($_POST['SectionName']);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'accountgroups'
	$sql= "SELECT COUNT(sectioninaccounts) AS sections FROM accountgroups WHERE sectioninaccounts='" . $_GET['SelectedSectionID'] . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_array($result);
	if ($myrow['sections']>0) {
		prnMsg( _('Cannot delete this account section because general ledger accounts groups have been created using this section'),'warn');
        echo '<div>';
		echo '<br />' . _('There are') . ' ' . $myrow['sections'] . ' ' . _('general ledger accounts groups that refer to this account section');
        echo '</div>';

	} else {
		//Fetch section name
		$sql = "SELECT sectionname FROM accountsection WHERE sectionid='".$_GET['SelectedSectionID'] . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_array($result);
		$SectionName = $myrow['sectionname'];

		$sql="DELETE FROM accountsection WHERE sectionid='" . $_GET['SelectedSectionID'] . "'";
		$result = DB_query($sql,$db);
		prnMsg( $SectionName . ' ' . _('section has been deleted') . '!','success');

	} //end if account group used in GL accounts
	unset ($_GET['SelectedSectionID']);
	unset($_GET['delete']);
	unset ($_POST['SelectedSectionID']);
	unset ($_POST['SectionID']);
	unset ($_POST['SectionName']);
}

if (!isset($_GET['SelectedSectionID']) AND !isset($_POST['SelectedSectionID'])) {

/* An account section could be posted when one has been edited and is being updated
  or GOT when selected for modification
  SelectedSectionID will exist because it was sent with the page in a GET .
  If its the first time the page has been displayed with no parameters
  then none of the above are true and the list of account groups will be displayed with
  links to delete or edit each. These will call the same page again and allow update/input
  or deletion of the records*/

	$sql = "SELECT sectionid,
			sectionname
		FROM accountsection
		ORDER BY sectionid";

	$ErrMsg = _('Could not get account group sections because');
	$result = DB_query($sql,$db,$ErrMsg);
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '<br /></p>';
    
    $accountSectionsTable = $MainView->createTable();
    $accountSectionsTable->sortable = true;
    $accountSectionsTable->id = 'SectionsTable';
    $tableHeaders[] = _('Section Number');
    $tableHeaders[] = _('Section Description');
    $accountSectionsTable->setHeaders($tableHeaders);
    

	while ($myrow = DB_fetch_array($result)) {
        $tablerow = array();
        $tablerow[] = $myrow['sectionid'];
        $tablerow[] = $myrow['sectionname'];
        $editRow['content'] = _('Edit');
        $editRow['link'] = htmlspecialchars($_SERVER['PHP_SELF'] . '?SelectedSectionID=' . urlencode($myrow['sectionid']), ENT_QUOTES, 'UTF-8');
        $tablerow[] = $editRow;
        if ($myrow['sectionid'] == '1' or $myrow['sectionid'] == '2') {
            $tablerow[] = '<b>' . _('Restricted') . '</b>';
        } else {
            $delRow['content'] = _('Delete');
            $delRow['link'] = htmlspecialchars($_SERVER['PHP_SELF'] . '?SelectedSectionID=' . urlencode($myrow['sectionid']) . '&delete=1', ENT_QUOTES, 'UTF-8')  . '&delete=1';
            $tablerow[] = $delRow;
        }
        
        $accountSectionsTable->addRow($tablerow);

	} //END WHILE LIST LOOP
	if (!$accountSectionsTable->display()) {
        echo "error displaying table";
    }
} //end of ifs and buts!


if (isset($_POST['SelectedSectionID']) or isset($_GET['SelectedSectionID'])) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Review Account Sections') . '</a></div>';
}

if (! isset($_GET['delete'])) {
    
    
    $accountSectionsForm = $MainView->createForm();
    $accountSectionsForm->id = 'AccountSections';
    $accountSectionsForm->setAction(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'));
    
	if (isset($_GET['SelectedSectionID'])) {
		//editing an existing section

		$sql = "SELECT sectionid,
				sectionname
			FROM accountsection
			WHERE sectionid='" . $_GET['SelectedSectionID'] ."'";

		$result = DB_query($sql, $db);
		if ( DB_num_rows($result) == 0 ) {
			prnMsg( _('Could not retrieve the requested section please try again.'),'warn');
			unset($_GET['SelectedSectionID']);
		} else {
			$myrow = DB_fetch_array($result);

			$_POST['SectionID'] = $myrow['sectionid'];
			$_POST['SectionName']  = $myrow['sectionname'];

            $accountSectionsForm->addHiddenControl('SelectedSectionID',$_POST['SectionID']);
            //addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null)
            $controlsettings['text'] = $_POST['SectionID'];
            $accountSectionsForm->addControl(1,0,'static', _('Section Number') . ':',$controlsettings);
		}

	}  else {

		if (!isset($_POST['SelectedSectionID'])){
			$_POST['SelectedSectionID']='';
		}
		if (!isset($_POST['SectionID'])){
			$_POST['SectionID']='';
		}
		if (!isset($_POST['SectionName'])) {
			$_POST['SectionName']='';
		}
        
         
        $controlsettings['name'] = 'SectionID';
        $controlsettings['value'] = $_POST['SectionID'];
        $controlsettings['autofocus'] = true;
        $controlsettings['required'] = true;
        $controlsettings['size'] = 4;
        $controlsettings['maxlength'] = 4;
        //addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null)
        $accountSectionsForm->addControl(1,1,'text', _('Section Number') . ':',$controlsettings,'number ' . (in_array('SectionID',$Errors) ?  'inputerror' : '' ));
	}
    
    //reuse this variable for clarity, but don't want any settings to carry over
    unset($controlsettings);
    $controlsettings['name'] = 'SectionName';
    $controlsettings['value'] = $_POST['SectionName'];
    $controlsettings['required'] = true;
    $controlsettings['size'] = 30;
    $controlsettings['maxlength'] = 30;
    //addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null)
    $accountSectionsForm->addControl(2,2,'text',_('Section Description') . ':',$controlsettings,(in_array('SectionName',$Errors) ?  'inputerror' : '' ));
    
    unset($controlsettings);
    $controlsettings['name'] = 'submit';
	$accountSectionsForm->addControl(3,3,'submit',_('Enter Information'),$controlsettings);
    
    $accountSectionsForm->display();

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>
