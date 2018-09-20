<?php

$kv_errors = array();
if ('POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['submit_form'])) {

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

        $uploadedfile = $_FILES['lexicon_file_to_upload'];

        $upload_overrides = array('test_form' => false);

        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            

            $dir = str_replace("\\", "/", LEXICON_DIR) . '/lexicon_languages';
            lexicon_load($dir, 'lang');
        }
    } else {
        echo '<script type="text/javascript">alert("Your file was not succesfully uploaded");</script>';
    }
}

function lexicon_load($dir, $type) {
        //echo '<script type="text/javascript">alert("In lexicon load function")</script>';

        $directory = opendir($dir);
        while ($archive = readdir($directory)) {
            if ($archive != '.' && $archive != '..') {
                switch ($type) {
                    case 'lang':
                        $x = lexicon_load_lang($dir, $archive);
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
    
    function lexicon_load_lang($dir, $lang_name) {
        global $wpdb;
        //echo '<script type="text/javascript">alert("In lexicon load lang function")</script>';
        //define('_LEXICON_WORDS', $wpdb->prefix . 'lexicon_words');
        $absolutepath = $dir . '/' . $lang_name;
        $lang = strstr($lang_name, '-', true);
        $lang_name = strstr($lang_name, '-');
        $lang_name = substr($lang_name, 1);
        $level = strstr($lang_name, '.', true);
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
            $sqlsTemp .= 'INSERT INTO ' . _LEXICON_WORDS . '(code, text, phrase, context, level, column_6, column_7, column_8, column_9, column_10, column_11, lang) values ("' . $entry_data[0] . '" , "' . $entry_data[1] . '" , "' . $entry_data[2] . '" , "' . $entry_data[3] . '", "' . $level . '" , "' . $entry_data[4] . '" , "' . $entry_data[5] . '" , "' . $entry_data[6] . '" , "' . $entry_data[7] . '" , "' . $entry_data[8] . '" , "' . $entry_data[9] . '" , "' . $lang . '");';
        }

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

    }


?>
<section>
    <label>Language: </label>

    <select>  
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
//$importLangCSV_nonce = wp_create_nonce('sp_import_lexicon_lang_CSV');
//$sendingPage = esc_attr($_REQUEST['page']);
//$ajaxurl = admin_url("admin-ajax.php");
?>
<!--<input type="hidden" id="admin_data_import" value="<?php echo $importLangCSV_nonce; ?>"/>-->
<!--<input type="hidden" id="sendingPage" value="<?php echo $sendingPage; ?>"/>-->
<!--<input type="hidden" id="varAjUrl" value="<?php echo $ajaxurl; ?>"/>-->
<!--<a class="button-primary" href="?page=<?php echo esc_attr($_REQUEST['page']) ?>&action=importLangCSV&_wpnonce=<?php echo $importLangCSV_nonce ?>" onclick="form.submit();">Upload</a>-->
<!--<input type="submit" onClick="location.href='?page=<?php echo esc_attr($_REQUEST['page']) ?>&action=importLangCSV&_wpnonce=<?php echo $importLangCSV_nonce ?>'" value='submit' class="button-primary">-->
<!--<input type="submit" name="submit" class="button-primary" value="Hello" />-->
