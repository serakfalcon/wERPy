<?php 
 /* footer file for Views, includes Bootstrap and Tablesorter */
?>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="includes/bootstrap/js/bootstrap.min.js"></script>
<!-- Table sorter plugin -->
<script src="includes/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript">
$(document).ready(function() 
    { 
        $(".tablesorter").tablesorter(); 
        initial();
        
    } 
); 
</script>