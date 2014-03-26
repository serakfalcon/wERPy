<?php

// Display demo user name and password within login form if $AllowDemoMode is true
//include ('LanguageSetup.php');
$this->getHeader(); 
?>
<link rel="stylesheet" href="<?php echo $this->getStyleLink();?>/login.css" type="text/css" />
</head><body>
<?php
if (get_magic_quotes_gpc()){
	echo '<p style="background:white">';
	echo _('Your webserver is configured to enable Magic Quotes. This may cause problems if you use punctuation (such as quotes) when doing data entry. You should contact your webmaster to disable Magic Quotes');
	echo '</p>';
}

?>

<div id="container">
	<div id="login_logo"></div>
	<div id="login_box">
    <?php 
        $LoginForm = $this->createForm();
        $LoginForm->setAction(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'));
        if (is_array($CompanyList)) {
            //find the default
            foreach ($CompanyList as $key => $CompanyEntry) {
                if ($DefaultDatabase == $CompanyEntry['database']) {
                        $CompanyNameField = $key;
                        $DefaultCompany = $CompanyEntry['company'];
                        break;
                    }
            }
        } else {
            $CompanyNameField = $DefaultCompany; //backwards compatibility, default is already set
        }
        
        if ($AllowCompanySelectionBox === 'Hide'){
            // do not show input or selection box
            $LoginForm->addHiddenControl('CompanyNameField',$CompanyNameField);
        } elseif ($AllowCompanySelectionBox === 'ShowInputBox'){
            // show input box
            // addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null,$row = null,$order = null,$dimensions = null)
            $LoginForm->addHiddenControl('CompanyNameField',$CompanyNameField);
            $LoginForm->addControl(1,1,'text',_('Company') .':', [  'name' => 'DefaultCompany', 
                                                                    'autofocus' => true, 
                                                                    'required' => true,
                                                                    'disabled' => true, //use disabled input for display consistency
                                                                    'value' => htmlspecialchars($DefaultCompany ,ENT_QUOTES,'UTF-8')]);
        } else { //selection of multiple companies
            
            $LoginForm->addControl(1,1,'select',('Company') .':',['name' => 'CompanyNameField']);
            if (is_array($CompanyList)) { //if we have a company list
                foreach ($CompanyList as $key => $CompanyEntry){
                    if (is_dir('companies/' . $CompanyEntry['database']) ){
                        //label = htmlspecialchars($CompanyEntry['company'],ENT_QUOTES,'UTF-8') ?
                         //addControlOption($key,$text,$value,$isSelected = null,$parentID = null,$id = null)
                        $LoginForm->addControlOption(1,htmlspecialchars($CompanyEntry['company'],ENT_QUOTES,'UTF-8'),$key,($CompanyEntry['database'] == $DefaultDatabase));
                    }
                }
            } else { //for backwards compatibility, remove when we have a reliable upgrade for config.php
                $Companies = scandir('companies/', 0);
                foreach ($Companies as $CompanyEntry){
                    if (is_dir('companies/' . $CompanyEntry) AND $CompanyEntry != '..' AND $CompanyEntry != '' AND $CompanyEntry!='.svn' AND $CompanyEntry!='.'){
                        // label = $CompanyEntry?
                         $LoginForm->addControlOption(1,$CompanyEntry,$CompanyEntry,($CompanyEntry['database'] == $DefaultDatabase));
                    }
                }
            }
        }
        
        $LoginForm->addControl(2,2,'text',_('User name') . ':',[    'name' => 'UserNameEntryField', 
                                                                    'required' => true,
                                                                    'autofocus' => true,
                                                                    'maxlength' => 20,
                                                                    'placeholder' => _('User name') . ':']);
        $LoginForm->addControl(3,3,'password',_('Password') . ':',[ 'name' => 'Password', 'required' => true, 'placeholder' => _('Password')]);
        if ((isset($AllowDemoMode)) && ($AllowDemoMode == true)) {
            if (!isset($demo_text)) {
                $demo_text = _('Login as user') .': <i>' . _('admin') . '</i><br />' ._('with password') . ': <i>' . _('weberp') . '</i>';
            }
            $LoginForm->addControl(4,-1,'content',null,['text' => '<div id="demo_text">' . $demo_text . '</div>' ]);
        }
        $LoginForm->addControl(5,4,'submit',_('Login'),['value' => _('Login'), name=> "SubmitUser"]);
        $LoginForm->display();
	?>
   
	</div>
	<br />
	<div style="text-align:center"><a href="https://sourceforge.net/projects/web-erp"><img src="https://sflogo.sourceforge.net/sflogo.php?group_id=70949&amp;type=8" width="80" height="15" alt="Get webERP Accounting &amp; Business Management at SourceForge.net. Fast, secure and Free Open Source software downloads" /></a></div>
</div>
</body>
</html>
