<?php
global $wpdb;

function testingv1_assign_nums() {
    global $wpdb;
    //$langNumRelation = array();
    $allLanguages = $wpdb->get_results("SELECT * FROM " . _LEXICON_LANGUAGES . ";");
    $counter = 0;
    //session_start();
    foreach ($allLanguages as $language) {
        $languageId = $language->id;
        $langNumRelation["$languageId"] = array("All" => $counter,
            "Untranslated" => $counter + 1,
            "Waiting" => $counter + 2,
            "Fuzzy" => $counter + 3);
        $counter = $counter + 10;
    }
    //$_SESSION["giannakis"] = $langNumRelation;
}
?>

<br />

<?php
if ('POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['formSubmit'])) {
    if(isset($_POST['language'])){
        $aDoor = $_POST['language'];
    }else{
        $aDoor = "";
    }
    if (empty($aDoor)) {
        echo("You didn't select any language.");
    } else {
        $N = count($aDoor);

        echo("You selected $N language(s): ");
    }
} else {
    $allLanguages = $wpdb->get_results("SELECT * FROM " . _LEXICON_LANGUAGES . " WHERE Part1 <> '' AND Language_Type = 'L';");
    $counter = 0;
    ?>
    <table>
        <?php
        foreach ($allLanguages as $language) {
            $languageId = $language->id;
            $languageRefName = $language->Ref_Name;
            if ($counter <= 5) {
                ?>
                <td><input type="checkbox" name="language[]" value="<?php echo $languageId ?>" /></td>
                <td><span style=";width: 120px;"><?php echo $languageRefName ?></span></td>


                <?php
                $counter++;
            } else {
                $counter = 0;
                ?>
                <tr></tr>
                <tr></tr>
                <?php
            }
        }
        ?>
    </table>
    <br/>
    <br/>
    <input class="button-primary" type="submit" name="formSubmit" value="Submit" />


    <?php
}
//session_start();
//print_r($_SESSION["giannakis"]);
//$petros = LEXICON_LANGNUM_RELATION;
//echo var_dump(unserialize(_LEXICON_LANGNUM_RELATION));
