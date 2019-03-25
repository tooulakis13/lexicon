<?php
/* vars for export */
// database record to be exported
//$db_record = 'words';
// optional where query
//$where = 'WHERE 1 ORDER BY 1';
// filename for export
//$csv_filename = 'db_export_' . $db_record . '_' . date('Y-m-d') . '.csv';
// database variables
//$conn = mysqli_connect($hostname, $user, $password, $database, $port);
// create empty variable to be filled with export data
// query to get data from database
$csv_export = '';
$query = "SELECT *
          FROM " . _LEXICON_WORD_CODE . "
          INNER JOIN " . _LEXICON_WORD_DETAILS . " ON " . _LEXICON_WORD_CODE . ".id=" . _LEXICON_WORD_DETAILS . ".code_id";
//$field = mysqli_field_count($conn);
$result = $wpdb->get_results($query, 'ARRAY_A');
$checkFirst = 0;
?>
<div>
    <?php
    global $wpdb;
    $databaseName = $wpdb->dbname;

    $word_details_cols = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS "
            . "WHERE TABLE_SCHEMA='$databaseName' "
            . "AND (TABLE_NAME='" . _LEXICON_WORD_DETAILS . "' OR TABLE_NAME='" . _LEXICON_WORD_CODE . "') AND (COLUMN_NAME NOT IN ('code_id', 'code'));");
    
    for ($counter = 0; $counter <= (count($result) - 1); $counter++) {
        $res = $result[$counter];
        if ($checkFirst <= (count($word_details_cols) - 1)) {
            for ($i = 0; $i <= count($word_details_cols) - 1; $i++) {
                $column_nameA = $word_details_cols[$i]->COLUMN_NAME;
                $csv_export .= "$column_nameA";
                if ($i == count($word_details_cols) - 1) {
                    $csv_export .= ',';
                    $csv_export .= '<br/>';
                } else {
                    $csv_export .= ';';
                }
                $checkFirst++;
            }
        }

        for ($i = 0; $i <= count($word_details_cols) - 1; $i++) {
            $column_nameA = $word_details_cols[$i]->COLUMN_NAME;
            $csv_export_temp = $res["$column_nameA"];
            $csv_export .= "$csv_export_temp";
            if ($i == count($word_details_cols) - 1) {
                $csv_export .= ',';
                $csv_export .= '<br/>';
            } else {
                $csv_export .= ';';
            }
        }
    }
    //echo $csv_export;
    //printf('<a href= "#" class="button-primary" onClick="export_data_to_CSV(\'%s\');">Export</a> ', var_dump($csv_export));
    //echo sprintf('<a href= "#" class="button-primary" onClick="export_data_to_CSV(%s);">Export</a>', $csv_export);
    //echo $csv_export;
    // newline (seems to work both on Linux & Windows servers)
    //$csv_export.= '
    //';
    ?>
    

</div>
<div style="display: none" id="data_to_export"><?php echo $csv_export ?></div>
<a href= "#" class="button-primary" onClick="export_data_to_CSV('<?php echo count($result) ?>');">Export</a>
<?php
// Export the data and prompt a csv file for download
//header('Content-Encoding: UTF-8');
//header("Content-type: text/csv");
//header("Content-Disposition: attachment; filename=".$csv_filename."");
//echo($csv_export);
