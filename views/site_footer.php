<?php 
 /* footer file for Views, required includes only */
?>
<script type="text/javascript">
<?php
// get all the settings from select inputs that are children of other selects
$controls = $GLOBALS['MainView']->getInstances('control');
if (is_array($controls)) {
    foreach ($controls as $control) {
        if ($control->getType() == 'select') {
            $controlID = $control->getSetting('id');
            if ($control->getSetting('hasparent') && isset($controlID)) {
                echo 'var ' . $controlID . '_json = ' . $control->getOptions() . ';'; // JSON.parse(' . "'" . $control->getOptions() . "'" . '
            }
        }
    }
    //initialize each child select with the currently selected value of the parent
    foreach ($controls as $control) {
        $childselect = $control->getSetting('childselect');
        if ( $childselect != false) {
           echo 'filterChild(' . "'" . $childselect . "'" . ',' . $control->getSelectedOption() . ',' . $childselect . '_json);';
        }
    }
} ?>
</script>