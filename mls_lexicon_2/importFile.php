<?php
$kv_errors = array();

function lexicon_load($dir, $type, $cols_to_add) {
    //echo '<script type="text/javascript">alert("In lexicon load function")</script>';

    $directory = opendir($dir);
    while ($archive = readdir($directory)) {
        if ($archive != '.' && $archive != '..') {
            switch ($type) {
                case 'lang':
                    $x = lexicon_load_lang($dir, $archive, $cols_to_add);
                    break;
                case 'course':
                    $x = lexicon_load_course($dir, $archive);
                    break;
                default:
            }
        }
    }
    closedir($directory);
    echo "Done";
}

function lexiconSingleBit2Two($param) {
    if (strlen($param) == 2) {
        return $param;
    } else if (strlen($param) == 1) {
        return '0' . $param;
    }
}

function lexiconSingleBit2Three($param) {
    if (strlen($param) == 3) {
        return $param;
    } else if (strlen($param) == 2) {
        return '0' . $param;
    } else if (strlen($param) == 1) {
        return '00' . $param;
    }
}

function lexicon_load_lang($dir, $lang_name, $cols_to_add) {
    global $wpdb;
    $databaseName = $wpdb->dbname;
    $absolutepath = $dir . $lang_name;
    echo $absolutepath;
    $sqlsTemp = "";
    //$sqls = array();
    //load file
    $data = file($absolutepath);
    $isFirst = true;
    $cols_to_add_word = $cols_to_add . '_word';
    $cols_to_add_phrase = $cols_to_add . '_phrase';
    $languageExistCheck = $wpdb->get_results("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='$databaseName' AND TABLE_NAME='" . _LEXICON_WORD_DETAILS . "' and (column_name='$cols_to_add_word' or column_name='$cols_to_add_phrase');");
    lexicon_add_language($cols_to_add);
    //echo print_r($languageExistCheck);
    foreach ($data as $line) {
        //Remove last CVC comma & new line
        $lineTemp = rtrim($line);
        $lineTempNew = rtrim($lineTemp, ",");
        if ($isFirst) {
            $isFirst = false;
            continue;
        }
        $entry_data = explode(';', $lineTempNew);

        //Data to process
        $file_word_id = $entry_data[0];
        $file_word_code = $entry_data[1];
        $file_word_level = $entry_data[2];
        $file_word_tn = $entry_data[3];
        $file_word_cl = $entry_data[4];
        $file_word_sc = $entry_data[5];
        $file_word_gr = $entry_data[6];
        $file_word_ej = $entry_data[7];
        $file_word_p = $entry_data[8];
        $file_word_unit = $entry_data[9];
        $file_word_theme = $entry_data[10];

        if (stristr($entry_data[11], "\xEF\xBF\xBD")) {
            $file_word_word = substr($entry_data[11], 0, -1);
            echo $file_word_word;
        } else {
            $file_word_word = $entry_data[11];
        }
        if (stristr($entry_data[12], "\xEF\xBF\xBD")) {
            $file_word_phrase = substr($entry_data[12], 0, -1);
            echo $file_word_phrase;
        } else {
            $file_word_phrase = $entry_data[12];
        }

        $file_word_cl = lexiconSingleBit2Two($file_word_cl);
        $file_word_sc = lexiconSingleBit2Two($file_word_sc);
        $file_word_gr = lexiconSingleBit2Two($file_word_gr);
        $file_word_ej = lexiconSingleBit2Two($file_word_ej);
        $file_word_p = lexiconSingleBit2Three($file_word_p);

        //FIRST TIME UPLOADING

        if ($file_word_word != '') {
            $word_digit = '1';
        } else if ($file_word_word == '') {
            $word_digit = '0';
        }

        if ($file_word_phrase != '') {
            $phrase_digit = '1';
        } else if ($file_word_phrase == '') {
            $phrase_digit = '0';
        }

        $file_word_code = $file_word_cl . $file_word_sc . $file_word_gr . $file_word_ej . $file_word_p;

        $result = $wpdb->get_results('SELECT * FROM ' . _LEXICON_WORD_CODE . ' WHERE id IS NOT NULL');

        $checkIdExist = $wpdb->get_results('SELECT * FROM ' . _LEXICON_WORD_CODE . ' WHERE id = ' . $file_word_id . '');

        if (count($result) == 0) {
            //>>>QUERY USED FOR THE FIRST IMPORTED FILE<<<
            $file_word_coexist = $word_digit . $phrase_digit;
            $sqlsTemp .= 'INSERT INTO ' . _LEXICON_WORD_CODE . '(id, code, level, t_n, word_coexist) values ("' . $file_word_id . '" , "' . $file_word_code . '"  , "' . $file_word_level . '"  , "' . $file_word_tn . '"  , "' . $file_word_coexist . '");'
                    . 'INSERT INTO ' . _LEXICON_WORD_DETAILS . '(code_id, c_l, s_c, g_r, e_j, p, unit, theme, ' . $cols_to_add_word . ', ' . $cols_to_add_phrase . ') values ("' . $file_word_id . '" , "' . $file_word_cl . '"  , "' . $file_word_sc . '"  , "' . $file_word_gr . '"  , "' . $file_word_ej . '"  , "' . $file_word_p . '"  , "' . $file_word_unit . '"  , "' . $file_word_theme . '" , "' . $file_word_word . '" , "' . $file_word_phrase . '");';
        } else if (count($result) != 0 && count($checkIdExist) == 1 && !$languageExistCheck) {
            //>>>QUERY USED FOR THE REST OF THE FILES IN CASE THERE ARE NO NEW WORDS IN THE CSV FILE AND THE LANGUAGE DOES NOT ALREADY EXIST<<<
            //>>>NOT DONE YET - PREPI NA KAMW TO FUNCTION GIA TO WORD COEXIST
            $wordCoExistTemp = $wpdb->get_results('SELECT word_coexist FROM ' . _LEXICON_WORD_CODE . ' WHERE id = ' . $file_word_id . ' LIMIT 1');
            //echo print_r($wordCoExistTemp);
            $wordCoExistTempValue = $wordCoExistTemp[0]->word_coexist;
            $file_word_coexist = $wordCoExistTempValue . $word_digit . $phrase_digit;
            $sqlsTemp .= 'UPDATE ' . _LEXICON_WORD_DETAILS . ' SET ' . $cols_to_add_word . ' = "' . $file_word_word . '", ' . $cols_to_add_phrase . ' = "' . $file_word_phrase . '" WHERE code_id = ' . $file_word_id . ';'
                    . 'UPDATE ' . _LEXICON_WORD_CODE . ' SET word_coexist = "' . $file_word_coexist . '" WHERE id = ' . $file_word_id . ';';
        } else if (count($result) != 0 && count($checkIdExist) == 1 && $languageExistCheck) {
            //>>>QUERY USED FOR THE REST OF THE FILES IN CASE THERE ARE NO NEW WORDS IN THE CSV FILE AND THE LANGUAGE ALREADY EXIST<<<
            //>>>NOT DONE YET - PREPI NA KAMW TO FUNCTION GIA TO WORD COEXIST
            $sqlsTemp .= 'UPDATE ' . _LEXICON_WORD_DETAILS . ' SET ' . $cols_to_add_word . ' = "' . $file_word_word . '", ' . $cols_to_add_phrase . ' = "' . $file_word_phrase . '" WHERE code_id = ' . $file_word_id . ';';
        } else if (count($result) != 0 && count($checkIdExist) == 0) {
            //>>>QUERY USED FOR THE REST OF THE FILES IN CASE THERE ARE NEW WORDS IN THE CSV FILE<<<
            $file_word_coexist .= $word_digit . $phrase_digit;
            $sqlsTemp .= 'INSERT INTO ' . _LEXICON_WORD_CODE . '(id, code, level, t_n, word_coexist) values ("' . $file_word_id . '" , "' . $file_word_code . '"  , "' . $file_word_level . '"  , "' . $file_word_tn . '"  , "' . $file_word_coexist . '");'
                    . 'INSERT INTO ' . _LEXICON_WORD_DETAILS . '(code_id, c_l, s_c, g_r, e_j, p, unit, theme, ' . $cols_to_add_word . ', ' . $cols_to_add_phrase . ') values ("' . $file_word_id . '" , "' . $file_word_cl . '"  , "' . $file_word_sc . '"  , "' . $file_word_gr . '"  , "' . $file_word_ej . '"  , "' . $file_word_p . '"  , "' . $file_word_unit . '"  , "' . $file_word_theme . '" , "' . $file_word_word . '" , "' . $file_word_phrase . '");';
        }
    }

    //echo $sqlsTemp;
    $sqls = explode(';', $sqlsTemp);
    $error = false;
    $wpdb->query('START TRANSACTION');
    foreach ($sqls as $sqlQuery) {
        if (!$wpdb->query($sqlQuery)) {
            $error = true;
            break;
        }
        if ($error) {
            $wpdb->query('ROLLBACK');
        } else {
            $wpdb->query('COMMIT');
        }
    }
    echo '<script type="text/javascript">alert("Your file was succesfully uploaded");</script>';
    unlink(LEXICON_FILE_TO_REMOVE);
}

function lexicon_add_language($cols_to_add) {

    global $wpdb;

    $cols_to_add_word = $cols_to_add . '_word';
    $cols_to_add_phrase = $cols_to_add . '_phrase';

    echo '<br/>';
    //echo $LEXICON_LANGUAGES_FOR_GET_COLUMNS;
    echo '<br/>';
    //echo $LEXICON_LANGUAGES_FOR_COLUMN_DEFAULT;
    echo '<br/>';

    $databaseName = $wpdb->dbname;
    $checkColExist = $wpdb->get_results("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='$databaseName' AND TABLE_NAME='" . _LEXICON_WORD_DETAILS . "' and column_name='$cols_to_add_word';");

    //echo print_r($result);

    if (!$checkColExist) {
        $add_cols_query = 'ALTER TABLE ' . _LEXICON_WORD_DETAILS . ' ADD ' . $cols_to_add_word . ' varchar(30) NOT NULL DEFAULT "";'
                . 'ALTER TABLE ' . _LEXICON_WORD_DETAILS . ' ADD ' . $cols_to_add_phrase . ' varchar(120) NOT NULL DEFAULT "";';
        echo $add_cols_query;
        $sqls = explode(';', $add_cols_query);
        $error = false;
        $wpdb->query('START TRANSACTION');
        foreach ($sqls as $sqlQuery) {
            if (!$wpdb->query($sqlQuery)) {
                $error = true;
                break;
            }
            if ($error) {
                $wpdb->query('ROLLBACK');
            } else {
                $wpdb->query('COMMIT');
            }
        }
    }
}

if ('POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['submit_form']) && isset($_POST['lex_lang'])) {

    /* $fields = array(
      'kv_name',
      'email',
      'message',
      'subject'
      );

      foreach ($fields as $field) {
      if (isset($_POST[$field]))
      $posted[$field] = stripslashes(trim($_POST[$field]));
      else
      $posted[$field] = '';
      }
      if ($posted['kv_name'] == null)
      array_push($kv_errors, sprintf('<strong>Notice</strong>: Please enter Your Name.', 'kvcodes'));

      if ($posted['email'] == null)
      array_push($kv_errors, sprintf('<strong>Notice</strong>: Please enter Your Email.', 'kvcodes'));

      if ($posted['message'] == null)
      array_push($kv_errors, sprintf('<strong>Notice</strong>: Please enter Your Message.', 'kvcodes'));

      if ($posted['subject'] == null)
      array_push($kv_errors, sprintf('<strong>Notice</strong>: Please enter Your Subject.', 'kvcodes'));

      $errors = array_filter($kv_errors); */

    if (!empty($_FILES['lexicon_file_to_upload'])) {

        if (!function_exists('wp_handle_upload')) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        $new_cols_name = $_POST['lex_lang'];

        //testingv1_get_word_details_cols();

        $uploadedfile = $_FILES['lexicon_file_to_upload'];

        $upload_overrides = array('test_form' => false);

        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        $new_filename = basename($movefile['url']);
        $lex_tempfile_url = $movefile['url'];
        $lex_tempfile_name_length = strlen($_FILES['lexicon_file_to_upload']['name']);

        if ($movefile && !isset($movefile['error'])) {

            //echo '<script type="text/javascript">alert("' . $movefile['url'] . '");</script>';
            $dir = LEXICON_UPLOAD_DIR;
            //echo LEXICON_UPLOAD_DIR;
            $lex_temp_file_loc = strstr($lex_tempfile_url, LEXICON_UPLOAD_DIR_NAME);
            //echo $lex_temp_file_loc;
            $dir = LEXICON_UPLOAD_DIR . $lex_temp_file_loc;
            $new_dir = str_replace('/', "\\", $dir);
            define('LEXICON_FILE_TO_REMOVE', $new_dir);
            $direc = substr($new_dir, 0, -$lex_tempfile_name_length);
            //echo $direc;

            lexicon_load($direc, 'lang', $new_cols_name);
            //wp_redirect(esc_url_raw(add_query_arg(array('page' => 'lexicon_testing'), admin_url('admin.php'))));
        } else {
            echo '<script type="text/javascript">alert("Your file was not succesfully uploaded");</script>';
            //wp_redirect(esc_url_raw(add_query_arg(array('page' => 'lexicon_testing'), admin_url('admin.php'))));
        }
    }
} else {

    $twoLetterLivingLanguages = $wpdb->get_results("SELECT * FROM " . _LEXICON_LANGUAGES . " WHERE Part1 <> '' AND Language_Type = 'L';");
    //print_r($twoLetterLivingLanguages);
    $smallLangList = ['es', 'cs', 'da', 'de', 'et', 'el', 'en', 'fr', 'ga', 'hr', 'it', 'lv', 'lt', 'hu', 'mt', 'nl', 'pl', 'pt', 'ro', 'sk', 'sl', 'fi', 'sv'];

    $moreLanguagesOption = '';
    $lessLanguagesOption = '';
    foreach ($twoLetterLivingLanguages as $langTable) {
        $moreLanguagesOption .= "<option value=" . "$langTable->Part1" . ">" . "$langTable->Ref_Name" . "</option>";
    }

    foreach ($twoLetterLivingLanguages as $langTable) {
        if (in_array($langTable->Part1, $smallLangList)) {
            $lessLanguagesOption .= "<option value=" . "$langTable->Part1" . ">" . "$langTable->Ref_Name" . "</option>";
        }
    }
    ?>
    <input type="hidden" value="<?php echo $moreLanguagesOption ?>" id="moreOpt"/>
    <input type="hidden" value="<?php echo $lessLanguagesOption ?>" id="lessOpt"/>
    <section id="sectionImportLang">
        <label>Language: </label>
        <?php //echo var_dump($lessLanguagesOption); ?>
        <select name="lex_lang" id="theLanguagesOptions">
            <?php
            echo $lessLanguagesOption;
            ?>
        </select>

        <br/>
        <br/>
        <div id="showLanguagesLink">
            <a href="#" onclick="showMoreLanguages(true)">Need more languages?</a>
        </div>
        <br/>
        <br/>

        <input type="file" name="lexicon_file_to_upload" id="lexicon_file_to_upload_id" />

        <br/> <br/>

        <input id="submit" class="button-primary" name="submit_form" type="submit" value="Submit">
    </section>

    <?php
}
