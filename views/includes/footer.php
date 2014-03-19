<?php 
 /* footer file for Views, required includes only */
?>
<script type="text/javascript">
<?php
// get all the settings from select inputs that are children of other selects
$controls = $GLOBALS['MainView']->getInstances('control');
if (count($controls) > 0) {
    foreach ($controls as $control) {
        if ($control->getType() == 'select') {
            $controlID = $control->getSetting('id');
            if ($control->getSetting('hasparent') && isset($controlID)) {
                echo 'var ' . $controlID . '_json = ' . $control->getOptions() . ';'; // JSON.parse(' . "'" . $control->getOptions() . "'" . '
            }
        }
    }
} ?> 
</script>