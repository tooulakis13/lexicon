<?php
$kv_errors = array();

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
            wp_redirect(esc_url_raw(add_query_arg(array('page' => 'lexicon_testing'), admin_url('admin.php'))));
        } else {
            echo '<script type="text/javascript">alert("Your file was not succesfully uploaded");</script>';
            wp_redirect(esc_url_raw(add_query_arg(array('page' => 'lexicon_testing'), admin_url('admin.php'))));
        }
    }
} else {

    $twoLetterLivingLanguages = $wpdb->get_results("SELECT * FROM " . _LEXICON_LANGUAGES . " WHERE Part1 <> '' AND Language_Type = 'L';");
    //print_r($twoLetterLivingLanguages);
    $smallLangList = ['es', 'cs', 'da', 'de', 'et', 'el', 'en', 'fr', 'ga', 'hr', 'it', 'lv', 'lt', 'hu', 'mt', 'nl', 'pl', 'pt', 'ro', 'sk', 'sl', 'fi', 'sv'];

    $moreLanguagesOption = '';
    $lessLanguagesOption = '';
    foreach ($twoLetterLivingLanguages as $langTable) {
        $moreLanguagesOption .= "<option value=" . "$langTable->id" . ">" . "$langTable->Ref_Name" . "</option>";
    }

    foreach ($twoLetterLivingLanguages as $langTable) {
        if (in_array($langTable->Part1, $smallLangList)) {
            $lessLanguagesOption .= "<option value=" . "$langTable->id" . ">" . "$langTable->Ref_Name" . "</option>";
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
