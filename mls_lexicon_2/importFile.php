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
    $absolutepath = $dir . $lang_name;
    echo $absolutepath;
    $sqlsTemp = "";
    //$sqls = array();
    //load file
    $data = file($absolutepath);
    $isFirst = true;
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

        $cols_to_add_word = $cols_to_add . '_word';
        $cols_to_add_phrase = $cols_to_add . '_phrase';

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

        $file_word_coexist = $word_digit . $phrase_digit;

        $file_word_code = $file_word_cl . $file_word_sc . $file_word_gr . $file_word_ej . $file_word_p;

        $result = $wpdb->get_results('SELECT * FROM ' . _LEXICON_WORD_CODE . ' WHERE id IS NOT NULL');

        $checkIdExist = $wpdb->get_results('SELECT * FROM ' . _LEXICON_WORD_CODE . ' WHERE id = ' . $file_word_id . '');

        if (count($result) == 0) {
            //>>>QUERY USED FOR THE FIRST IMPORTED FILE<<<
            $sqlsTemp .= 'INSERT INTO ' . _LEXICON_WORD_CODE . '(id, code, level, t_n, word_coexist) values ("' . $file_word_id . '" , "' . $file_word_code . '"  , "' . $file_word_level . '"  , "' . $file_word_tn . '"  , "' . $file_word_coexist . '");'
                    . 'INSERT INTO ' . _LEXICON_WORD_DETAILS . '(code_id, c_l, s_c, g_r, e_j, p, unit, theme, ' . $cols_to_add_word . ', ' . $cols_to_add_phrase . ') values ("' . $file_word_id . '" , "' . $file_word_cl . '"  , "' . $file_word_sc . '"  , "' . $file_word_gr . '"  , "' . $file_word_ej . '"  , "' . $file_word_p . '"  , "' . $file_word_unit . '"  , "' . $file_word_theme . '" , "' . $file_word_word . '" , "' . $file_word_phrase . '");';
        } else if (count($result) != 0 && count($checkIdExist) == 1) {
            //>>>QUERY USED FOR THE REST OF THE FILES IN CASE THERE ARE NO NEW WORDS IN THE CSV FILE<<<
            $sqlsTemp .= 'UPDATE ' . _LEXICON_WORD_DETAILS . ' SET ' . $cols_to_add_word . ' = "' . $file_word_word . '", ' . $cols_to_add_phrase . ' = "' . $file_word_phrase . '" WHERE code_id = ' . $file_word_id . ';';
        } else if (count($result) != 0 && count($checkIdExist) == 0) {
            //>>>QUERY USED FOR THE REST OF THE FILES IN CASE THERE ARE NEW WORDS IN THE CSV FILE<<<
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

    $testingv1_languages_for_get_columns = "";
    $testingv1_languages_for_column_default = "";

    $testingv1_languages_for_get_columns = [
        "'$cols_to_add_word'" => __("'$cols_to_add word'", 'sp'),
        "'$cols_to_add_phrase'" => __("'$cols_to_add phrase'", 'sp'),
    ];



    //echo print_r($testingv1_languages_for_get_columns);

    /*$filename = 'lexiconColumns.txt';

    if (file_exists($filename)) {
        file_put_contents(LEXICON_DIR . "\lexiconColumns.txt", $testingv1_languages_for_get_columns, FILE_APPEND);
    } else {
        file_put_contents(LEXICON_DIR . "\lexiconColumns.txt", $testingv1_languages_for_get_columns, FILE_APPEND);
    }*/
    
    //echo LEXICON_DIR;

    $testingv1_languages_for_column_default .= "case '" . $cols_to_add_word . "':
            case '" . $cols_to_add_phrase . "':";

    if ($testingv1_languages_for_get_columns != "" && $testingv1_languages_for_column_default != "") {
        $LEXICON_LANGUAGES_FOR_GET_COLUMNS = $testingv1_languages_for_get_columns;
        $LEXICON_LANGUAGES_FOR_COLUMN_DEFAULT = $testingv1_languages_for_column_default;
    } else {
        $LEXICON_LANGUAGES_FOR_GET_COLUMNS = "hello";
        $LEXICON_LANGUAGES_FOR_COLUMN_DEFAULT = "hello";
    }

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

        lexicon_add_language($new_cols_name);
        //mysql_query("ALTER TABLE birthdays ADD street CHAR(30)");
        
        testingv1_get_word_details_cols();

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
            //echo '<script type="text/javascript">location.reload();</script>';
        }
    } else {
        echo '<script type="text/javascript">alert("Your file was not succesfully uploaded");</script>';
        //echo '<script type="text/javascript">location.reload();</script>';
    }
} else {
    ?>
    <section>
        <label>Language: </label>

        <select name="lex_lang">  
            <option value=af>Afrikaans</option>
            <option value=sq>Albanian</option>
            <option value=am>Amharic</option>
            <option value=ar>Arabic</option>
            <option value=hy>Armenian</option>
            <option value=az>Azerbaijani</option>
            <option value=eu>Basque</option>
            <option value=be>Belarusian</option>
            <option value=bn>Bengali</option>
            <option value=bs>Bosnian</option>
            <option value=bg>Bulgarian</option>
            <option value=ca>Catalan</option>
            <option value=ceb>Cebuano</option>
            <option value=ny>Chichewa</option>
            <option value=zh-CN>Chinese</option>
            <option value=co>Corsican</option>
            <option value=hr>Croatian</option>
            <option value=cs>Czech</option>
            <option value=da>Danish</option>
            <option value=nl>Dutch</option>
            <option value=en>English</option>
            <option value=eo>Esperanto</option>
            <option value=et>Estonian</option>
            <option value=tl>Filipino</option>
            <option value=fi>Finnish</option>
            <option value=fr>French</option>
            <option value=fy>Frisian</option>
            <option value=gl>Galician</option>
            <option value=ka>Georgian</option>
            <option value=de>German</option>
            <option value=el>Greek</option>
            <option value=gu>Gujarati</option>
            <option value=ht>Haitian Creole</option>
            <option value=ha>Hausa</option>
            <option value=haw>Hawaiian</option>
            <option value=iw>Hebrew</option>
            <option value=hi>Hindi</option>
            <option value=hmn>Hmong</option>
            <option value=hu>Hungarian</option>
            <option value=is>Icelandic</option>
            <option value=ig>Igbo</option>
            <option value=id>Indonesian</option>
            <option value=ga>Irish</option>
            <option value=it>Italian</option>
            <option value=ja>Japanese</option>
            <option value=jw>Javanese</option>
            <option value=kn>Kannada</option>
            <option value=kk>Kazakh</option>
            <option value=km>Khmer</option>
            <option value=ko>Korean</option>
            <option value=ku>Kurdish (Kurmanji)</option>
            <option value=ky>Kyrgyz</option>
            <option value=lo>Lao</option>
            <option value=la>Latin</option>
            <option value=lv>Latvian</option>
            <option value=lt>Lithuanian</option>
            <option value=lb>Luxembourgish</option>
            <option value=mk>Macedonian</option>
            <option value=mg>Malagasy</option>
            <option value=ms>Malay</option>
            <option value=ml>Malayalam</option>
            <option value=mt>Maltese</option>
            <option value=mi>Maori</option>
            <option value=mr>Marathi</option>
            <option value=mn>Mongolian</option>
            <option value=my>Myanmar (Burmese)</option>
            <option value=ne>Nepali</option>
            <option value=no>Norwegian</option>
            <option value=ps>Pashto</option>
            <option value=fa>Persian</option>
            <option value=pl>Polish</option>
            <option value=pt>Portuguese</option>
            <option value=pa>Punjabi</option>
            <option value=ro>Romanian</option>
            <option value=ru>Russian</option>
            <option value=sm>Samoan</option>
            <option value=gd>Scots Gaelic</option>
            <option value=sr>Serbian</option>
            <option value=st>Sesotho</option>
            <option value=sn>Shona</option>
            <option value=sd>Sindhi</option>
            <option value=si>Sinhala</option>
            <option value=sk>Slovak</option>
            <option value=sl>Slovenian</option>
            <option value=so>Somali</option>
            <option value=es>Spanish</option>
            <option value=su>Sundanese</option>
            <option value=sw>Swahili</option>
            <option value=sv>Swedish</option>
            <option value=tg>Tajik</option>
            <option value=ta>Tamil</option>
            <option value=te>Telugu</option>
            <option value=th>Thai</option>
            <option value=tr>Turkish</option>
            <option value=uk>Ukrainian</option>
            <option value=ur>Urdu</option>
            <option value=uz>Uzbek</option>
            <option value=vi>Vietnamese</option>
            <option value=cy>Welsh</option>
            <option value=xh>Xhosa</option>
            <option value=yi>Yiddish</option>
            <option value=yo>Yoruba</option>
            <option value=zu>Zulu</option>
        </select>

        <br/> <br/>

        <input type="file" name="lexicon_file_to_upload" id="lexicon_file_to_upload_id" />

        <br/> <br/>

        <input type="button" onclick="backToLexicon()" class="button-secondary" value="Cancel"/>
        <input id="submit" class="button-primary" name="submit_form" type="submit" value="Submit">
    </section>

    <?php

}
