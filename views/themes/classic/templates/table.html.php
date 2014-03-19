<?php 
/*****************************
Variables used for the table
tableclass variables
$classes->table (for table)
$classes->headers (default for headers)

$attributes (customizable table attributes)


Templates are formatted to make the HTML structure obvious, with PHP to insert information as needed
*/
?>
<div class="table-responsive">
<table class="table table-striped table-hover <?php echo $this->classes['table'];?>" <?php echo $this->attributes; ?>>
<?php 
if(!empty($this->title)) { ?>
    <caption class="text-left">
        <h4>
            <?php echo $this->title; ?>
        </h4>
    </tr>
<?php
} //end title statement
if(!empty($this->headers)) { ?>
    <!-- header row -->
    <thead>
        <?php
        if (!empty($this->headers)) { ?>
            <tr> 
                <?php
                foreach ($this->headers as $columnhead) {
                    echo '<th';
                    if ($columnhead['class']) {
                        echo ' class="' . $columnhead['class'] . '"'; 
                    }
                    if ($columnhead['span'] > 1) {
                        echo ' colspan="' . $columnhead['span'] . '"';
                    }
                    echo '>';
                    echo $columnhead['content']; ?>
                    </th>
                    <?php
                }
                ?><!-- <?php echo $this->columnCount . ' vs. ' . $this->headerCount; ?> --><?php
                if ($this->columnCount > $this->headerCount) {
                    $i = $this->headerCount;
                    while ($i < $this->columnCount) {
                        echo '<th class="no-sort"> </th>';
                        $i++;
                    }
                }
                ?>
            </tr>
        <?php } // end headers statement ?>
    </thead>
<?php
} // end thead if statement ?>
    <!-- table content -->
    <tbody>
        <?php 
        foreach ($this->rows as $row) {
            ?>
            <tr <?php echo $row->htmlclass ? 'class="' . $row->htmlclass . '"' : '';
                      echo $row->attributes ? ' ' . $row->attributes : '';
            ?>>
                <?php
                foreach ($row->columns as $column) {
                    if ($column['isheader']) {
                        echo '<th';
                    } else {
                        echo '<td';
                    }
                    if ($column['span'] != 1) {
                        echo ' colspan="' . $column['span'] . '"';
                    }
                    if ($column['class']) {
                        echo ' class="' . $column['class'] . '"';
                    }
                    echo '>';
                    if ($column['link']) {
                        if ($column['attributes']) {
                            echo '<a href="' . $column['link'] . '" ' . $column['attributes'] . '>' . $column['content'] . '</a>';
                        } else {
                            echo '<a href="' . $column['link'] . '">' . $column['content'] . '</a>';
                        }
                    } else {
                        echo $column['content'];
                    }
                    if ($column['isheader']) {
                        echo '</th>';
                    } else {
                        echo '</td>';
                    }
                }
                ?>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>
</div>