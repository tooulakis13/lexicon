<?php
global $wpdb;
$lex_languages = $wpdb->get_results("SELECT * FROM " . _LEXICON_LANGUAGES . " WHERE Part1 <> '' AND Language_Type = 'L';");

if ('POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['submitLang'])) {

    if (isset($_POST['secondaryLang'])) {
        $secondaryLangChecked = $_POST['secondaryLang'];
        echo $secondaryLangChecked;
    } else {
        $secondaryLangChecked = "";
    }
    if (isset($_POST['additionalLang'])) {
        $additionalLangChecked = $_POST['additionalLang'];
        echo $additionalLangChecked;
    } else {
        $additionalLangChecked = "";
    }
    ?>

    <table>
        <tr>
            <td><b><?php echo _e('Primary Language:', 'mls_lexicon'); ?></b></td>
            <td><?php echo get_bloginfo("language"); ?></td>
        </tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr>
            <td><b><?php echo _e('Secondary Language:', 'mls_lexicon'); ?></b></td>
            <td>
                <select name="secondaryLang">
                    <?php
                    foreach ($lex_languages as $lang) {

                        if ($lang->id === $secondaryLangChecked) {
                            $secondaryLangCheckedValue = "selected";
                        }else{
                            $secondaryLangCheckedValue = "";
                        }
                        echo "<option $secondaryLangCheckedValue value=" . "$lang->id" . ">" . "$lang->Ref_Name" . "</option>";
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
            <td><b><?php echo _e('Additional Language:', 'mls_lexicon'); ?></b></td>
            <td>
                <select name="additionalLang">
                    <?php
                    foreach ($lex_languages as $lang) {

                        if ($lang->id === $additionalLangChecked) {
                            $additionalLangCheckedValue = "selected";
                        }
                        else{
                            $additionalLangCheckedValue = "";
                        }
                        echo "<option $additionalLangCheckedValue value=" . "$lang->id" . ">" . "$lang->Ref_Name" . "</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr></tr>
        <tr></tr>
    </table>
    <br/><br/>
    <input type="submit" value="Save" name="submitLang" class="button-primary" />

    <?php
} else {
    ?>

    <table>
        <tr>
            <td><b><?php echo _e('Primary Language:', 'mls_lexicon'); ?></b></td>
            <td><?php echo get_bloginfo("language"); ?></td>
        </tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr>
            <td><b><?php echo _e('Secondary Language:', 'mls_lexicon'); ?></b></td>
            <td>
                <select name="secondaryLang">
                    <?php
                    foreach ($lex_languages as $lang) {
                        echo "<option value=" . "$lang->id" . ">" . "$lang->Ref_Name" . "</option>";
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
            <td><b><?php echo _e('Additional Language:', 'mls_lexicon'); ?></b></td>
            <td>
                <select name="additionalLang">
                    <?php
                    foreach ($lex_languages as $lang) {
                        echo "<option value=" . "$lang->id" . ">" . "$lang->Ref_Name" . "</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr></tr>
        <tr></tr>
    </table>
    <br/><br/>
    <input type="submit" value="Save" name="submitLang" class="button-primary" />
    <?php
}
