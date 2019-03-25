<?php
global $wpdb;
$lex_languages = $wpdb->get_results("SELECT * FROM " . _LEXICON_LANGUAGES . " WHERE Part1 <> '' AND Language_Type = 'L';");

$lex_userId = get_current_user_id();
$lex_userMetaSet = get_user_meta($lex_userId, "secondaryLang", true);
//update_user_meta($lex_userId, "additionalLang", ""); //reset languages
$lex_userMetaSetAdd = get_user_meta($lex_userId, "additionalLang", true);
//write_log("hey " . $lex_userMetaSetAdd . " hey");
if ($lex_userMetaSetAdd === "") {
    $hideIfUnchecked = 'class="hidden"';
    $checkedIfEmpty = '';
} else {
    $hideIfUnchecked = "";
    $checkedIfEmpty = 'checked';
}
$additionalLangChecked = "";
?>
<div id="languageChangeMessages" class="update-nag" style="margin: 0px 0px 0px 0px; display: none"></div>
<br/>
<br/>
<?php
if ('POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['submitLang'])) {

    if (isset($_POST['secondaryLang'])) {
        $secondaryLangCheckedTemp = $_POST['secondaryLang'];
        $secondaryLangChecked = substr($secondaryLangCheckedTemp, 0, 3);

        if (isset($lex_userMetaSet)) {
            update_user_meta($lex_userId, "secondaryLang", $secondaryLangChecked);
            lexicon_add_language_inDB($secondaryLangChecked, "no");
        } else {
            add_user_meta($lex_userId, "secondaryLang", $secondaryLangChecked);
            lexicon_add_language_inDB($secondaryLangChecked, "no");
        }
        //echo $secondaryLangChecked;GIANNAKIS
    } else {

        if ($lex_userMetaSet) {
            $secondaryLangChecked = get_user_meta($lex_userId, "secondaryLang", true);
        } else {
            $secondaryLangChecked = "";
        }
    }

    if (isset($_POST['additionalLang'])) {
        $additionalLangCheckedTemp = $_POST['additionalLang'];
        //write_log($additionalLangCheckedTemp);

        if ($additionalLangCheckedTemp !== "") {
            if (isset($lex_userMetaSetAdd)) {
                $additionalLangChecked = substr($additionalLangCheckedTemp, 0, 3);
                update_user_meta($lex_userId, "additionalLang", $additionalLangChecked);
                lexicon_add_language_inDB($additionalLangChecked, "no");
            } else {
                add_user_meta($lex_userId, "additionalLang", $additionalLangChecked);
                lexicon_add_language_inDB($additionalLangChecked, "no");
            }
            $hideIfUnchecked = "";
            $checkedIfEmpty = 'checked';
        } else if ($additionalLangCheckedTemp === "") {
            if (isset($lex_userMetaSetAdd)) {
                update_user_meta($lex_userId, "additionalLang", "");
            } else {
                add_user_meta($lex_userId, "additionalLang", "");
            }
			update_user_meta($lex_userId, "additionalLang", "");
            $hideIfUnchecked = 'class="hidden"';
            $checkedIfEmpty = '';
        }

        //echo $additionalLangChecked;
    } else {
        if ($lex_userMetaSetAdd !== "") {
            $additionalLangChecked = get_user_meta($lex_userId, "additionalLang", true);
            $hideIfUnchecked = 'class="hidden"';
            $checkedIfEmpty = '';
        } else {
			update_user_meta($lex_userId, "additionalLang", "");
            $additionalLangChecked = "";
            $hideIfUnchecked = "";
            $checkedIfEmpty = 'checked';
        }
    }
    ?>


    <table id="showOptions">
        <tr>
            <td><b><?php echo _e('Primary Language:', 'mls_lexicon'); ?></b></td>
            <td><?php echo "English"; ?></td>
        </tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr>
            <td><b><?php echo _e('Secondary Language:', 'mls_lexicon'); ?></b></td>
            <td>
                <select id="secondaryLanguageDrop" name="secondaryLang">
                    <?php
                    foreach ($lex_languages as $lang) {
                        $langId = $lang->id;
                        $langRefNameTemp = $lang->Ref_Name;
                        $langRefNameBrackCheck = strpos($langRefNameTemp, "(");
                        if (!$langRefNameBrackCheck) {
                            $langRefName = $langRefNameTemp;
                        } else {
                            $langRefName = substr($langRefNameTemp, 0, strpos($langRefNameTemp, "(") - 1);
                        }
                        if ($langId === $secondaryLangChecked) {
                            $secondaryLangCheckedValue = " selected";
                        } else {
                            $secondaryLangCheckedValue = "";
                        }
                        echo '<option' . $secondaryLangCheckedValue . ' value="' . $langId . $langRefName . '">' . $langRefName . '</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr>
            <td id="additionalLangPlaceholder" <?php echo $hideIfUnchecked ?>><b><?php echo _e('Additional Language:', 'mls_lexicon'); ?></b></td>
            <td id="additionalLangSelect" <?php echo $hideIfUnchecked ?>>
                <select id="additionalLanguageDrop" name="additionalLang">
                    <?php
                    foreach ($lex_languages as $lang) {
                        $langId = $lang->id;
                        $langRefNameTemp = $lang->Ref_Name;
                        $langRefNameBrackCheck = strpos($langRefNameTemp, "(");
                        if (!$langRefNameBrackCheck) {
                            $langRefName = $langRefNameTemp;
                        } else {
                            $langRefName = substr($langRefNameTemp, 0, strpos($langRefNameTemp, "(") - 1);
                        }
                        if ($langId === $additionalLangChecked) {
                            $additionalLangCheckedValue = " selected";
                        } else {
                            $additionalLangCheckedValue = "";
                        }
                        echo '<option' . $additionalLangCheckedValue . ' value="' . $langId . $langRefName . '">' . $langRefName . '</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr></tr>
        <tr>
            <td>
                <div style="display: inline-flex;">
                    <input style="align-self: center;" type="checkbox" <?php echo $checkedIfEmpty ?> id="allowAdditionalLang"/> 
                    <div style="width: 100px;">Allow additional language</div>
                </div>
            </td>
        </tr>
    </table>
    <br/>
    <input type="button" value="Save" onclick="settingsPageChangeLang()" class="button-primary" />

    <?php
} else {
    ?>

    <table id="showOptions">
        <tr>
            <td><b><?php echo _e('Primary Language:', 'mls_lexicon'); ?></b></td>
            <td><?php echo "English"; ?></td>
        </tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr>
            <td><b><?php echo _e('Secondary Language:', 'mls_lexicon'); ?></b></td>
            <td>
                <select id="secondaryLanguageDrop" name="secondaryLang">
                    <?php
                    if (!empty($lex_userMetaSet)) {
                        $secondaryLangChecked = get_user_meta($lex_userId, "secondaryLang", true);
                    } else {
                        $secondaryLangChecked = "";
                    }

                    foreach ($lex_languages as $lang) {
                        $langId = $lang->id;
                        $langRefNameTemp = $lang->Ref_Name;
                        $langRefNameBrackCheck = strpos($langRefNameTemp, "(");
                        if (!$langRefNameBrackCheck) {
                            $langRefName = $langRefNameTemp;
                        } else {
                            $langRefName = substr($langRefNameTemp, 0, strpos($langRefNameTemp, "(") - 1);
                        }
                        if ($langId == $secondaryLangChecked) {
                            $secondaryLangCheckedValue = " selected ";
                        } else {
                            $secondaryLangCheckedValue = "";
                        }
                        echo '<option' . $secondaryLangCheckedValue . ' value="' . $langId . $langRefName . '">' . $langRefName . '</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr>
            <td id="additionalLangPlaceholder" <?php echo $hideIfUnchecked ?>><b><?php echo _e('Additional Language:', 'mls_lexicon'); ?></b></td>
            <td id="additionalLangSelect" <?php echo $hideIfUnchecked ?>>
                <select id="additionalLanguageDrop" name="additionalLang" >

                    <?php
                    if (!empty($lex_userMetaSetAdd)) {
                        $additionalLangChecked = get_user_meta($lex_userId, "additionalLang", true);
                    } else {
                        $additionalLangChecked = "";
                    }

                    foreach ($lex_languages as $lang) {
                        $langId = $lang->id;
                        $langRefNameTemp = $lang->Ref_Name;
                        $langRefNameBrackCheck = strpos($langRefNameTemp, "(");
                        if (!$langRefNameBrackCheck) {
                            $langRefName = $langRefNameTemp;
                        } else {
                            $langRefName = substr($langRefNameTemp, 0, strpos($langRefNameTemp, "(") - 1);
                        }
                        if ($langId == $additionalLangChecked) {
                            $additionalLangCheckedValue = " selected ";
                        } else {
                            $additionalLangCheckedValue = "";
                        }
                        echo '<option' . $additionalLangCheckedValue . ' value="' . $langId . $langRefName . '">' . $langRefName . '</option>';
                    }
                    ?>
                </select>
            </td>
            <td>
            </td>
        </tr>
        <tr></tr>
        <tr>
            <td>
                <div style="display: inline-flex;">
                    <input style="align-self: center;" type="checkbox" <?php echo $checkedIfEmpty ?> id="allowAdditionalLang"/> 
                    <div style="width: 100px;">Allow additional language</div>
                </div>
            </td>
        </tr>
    </table>
    <br/>
    <input type="button" value="Save" onclick="settingsPageChangeLang()" class="button-primary" />
    <?php
}