<?php 
 /* footer file for Views, includes Bootstrap and Tablesorter */
?>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="includes/bootstrap/js/bootstrap.min.js"></script>
<!-- Table sorter plugin -->
<script src="includes/DataTables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">

$(document).ready(function() 
    { 
        <?php
            $tables = $GLOBALS['MainView']->getInstances('table');
            if ($tables && count($tables) > 0) {
                //apply dataTable to each table instance
                foreach ($tables as $table) {
                    if (isset($table->id) && $table->sortable) { ?>
                        $("#<?php echo $table->id; ?>").dataTable({
                        <?php echo ($table->sortSettings) ? $table->sortSettings . ',' : ''; ?>
                        aoColumnDefs:[ {
                            aTargets:[ 'no-sort' ],
                            bSortable:false,
                            bSearchable:false
                            }]
                        }); 
                <?php 
                    }
                }
            } ?>
        
        initial();
        
    } 
);
</script>