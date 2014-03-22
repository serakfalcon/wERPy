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
<table class="selection <?php echo $this->classes['table'];?>" <?php echo $this->attributes; ?>>
<?php 
if(!empty($this->title)) { ?>
    <caption>
        <?php echo $this->title; ?>
    </caption>
<?php
} //end title statement
if(!empty($this->headers)) {
?>
    <tr> 
        <?php
        foreach ($this->headers as $columnhead) {
            echo '<th';
            //miscFunctions does not support multiple classes for table headers
            if ($this->sortable) {
                echo ' class="ascending"';
            } elseif ($columnhead['class']) {
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
        ?>
    </tr>
<?php } // end headers statement
    $i = true; //iterator for odd/even rows
    foreach ($this->rows as $row) {
        ?>
        <tr class="<?php echo ($i) ? 'OddTableRows' : 'EvenTableRows'; ?>
                   <?php echo $row->htmlclass ? ' ' . $row->htmlclass : ''; ?>"
                   <?php echo $row->attributes ? ' ' . $row->attributes : '';?>>
            <?php
            //switch between Odd and Even
            $i = !$i;
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
</table>