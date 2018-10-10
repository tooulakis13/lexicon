<?php
/*
  Plugin Name: testingv1
  Description: Testing
  Version: 1.0
 */

global $wpdb;

$uploads = wp_upload_dir();

// Location variables
define('LEXICON_DIR', dirname(__FILE__));
define('LEXICON_UPLOAD_DIR_NAME', wp_basename($uploads['baseurl']));
define('LEXICON_DIR_RELATIVO', dirname(plugin_basename(__FILE__)));
define('LEXICON_URL', plugin_dir_url(__FILE__));
define('LEXICON_UPLOAD_DIR', str_replace('plugins\testingv1', '', LEXICON_DIR));

define('_LEXICON_COURSE', $wpdb->prefix . 'lexicon_course');
define('_LEXICON_COURSE_STUDENT', $wpdb->prefix . 'lexicon_course_student');
define('_LEXICON_COURSE_SUTDENT_CARD', $wpdb->prefix . 'lexicon_course_student_card');
define('_LEXICON_COURSE_AUTHOR', $wpdb->prefix . 'lexicon_course_author');
define('_LEXICON_COURSE_CODES', $wpdb->prefix . 'lexicon_course_codes');
define('_LEXICON_WORDS', $wpdb->prefix . 'lexicon_words');
define('_LEXICON_WORD_CODE', $wpdb->prefix . 'lexicon_word_code');
define('_LEXICON_WORD_DETAILS', $wpdb->prefix . 'lexicon_word_details');

//Links to necessary files
require_once(LEXICON_DIR . '/includes/lexicon_functions.php');
require_once(LEXICON_DIR . '/includes/lexicon_ajax.php');

if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Lexicon_words_List extends WP_List_Table {

    /** Class constructor */
    public function __construct() {
        parent::__construct([
            'singular' => __('Lexicon word', 'sp'), //singular name of the listed records
            'plural' => __('Lexicon words', 'sp'), //plural name of the listed records
            'ajax' => true //does this table support ajax?
        ]);
    }

    /**
     * Retrieve customers data from the database
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public static function get_lexicon_words($per_page = 5, $page_number = 1) {
        global $wpdb;

        $sql = "SELECT *
                FROM " . _LEXICON_WORD_CODE . "
                INNER JOIN " . _LEXICON_WORD_DETAILS . " ON " . _LEXICON_WORD_CODE . ".id=" . _LEXICON_WORD_DETAILS . ".code_id";

        //$sql = "SELECT * FROM {$wpdb->prefix}lexicon_words";
        if (!empty($_REQUEST['orderby'])) {
            $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
            $sql .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        }
        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
        $result = $wpdb->get_results($sql, 'ARRAY_A');
        return $result;
    }

    /**
     * Delete a customer record.
     *
     * @param int $id customer ID
     */
    public static function delete_lexicon_word($id) {
        global $wpdb;
        $wpdb->delete(
                "{$wpdb->prefix}lexicon_words", array('id' => $id)
        );
    }

    /**
     * Edit a customer record.
     *
     * @param int $id customer ID
     */
    public function lexicon_load_course($dir, $course_name) {
        global $wpdb;
        $absolutepath = $dir . '/' . $course_name;
        $level = strstr($course_name, '-', true);
        $course_name = strstr($course_name, '-');
        $course_name = substr($course_name, 1);
        $lang = strstr($course_name, '-', true);
        $course_name = strstr($course_name, '-');
        $course_name = substr($course_name, 1);
        $author = strstr($course_name, '.', true);
        $langs = $wpdb->get_results("SELECT DISTINCT lang FROM " . _LEXICON_WORDS . " WHERE lang <> '" . $lang . "'");

        if (count($langs) > 0) {
            $user_id = 1;

            foreach ($langs as $lang_z) {
                $sqls = array();
                $course_id = set_courses($lang, $lang_z->lang, $level, 'A course by ' . $author . '');
                set_course_teacher($user_id, $course_id);
                //load file
                $data = file($absolutepath);
                $isFirst = true;
                foreach ($data as $line) {
                    //Remove last CVC comma & new line
                    $line = rtrim($line);
                    $line = rtrim($line, ",");
                    if ($isFirst) {
                        $isFirst = false;
                        continue;
                    }
                    $entry_data = explode(';', $line);
                    $csvToTable .= $entry_data[0] . ';' . $entry_data[1] . ';';
                    $sqls[] = 'INSERT INTO ' . _LEXICON_COURSE_CODES . '(code, context, course_id) values ("' . $entry_data[0] . '" , "' . $entry_data[1] . '" , "' . $course_id . '")';
                }
            }
            /* $tempTable = array();
              $csvToTableLength = count($csvToTable);
              $csvToTableSeperated = explode(';', $csvToTable);
              for ($i = 0; $i <= $csvToTableLength;) {
              //$checkValue = $i + 2;
              for ($x = $i + 2; $x <= $csvToTableLength;) {
              if ($csvToTableSeperated[$i] == $csvToTableSeperated[$x]) {
              $tempTable[] = $csvToTableSeperated[$x];
              }else {
              $escapeVal = $csvToTableSeperated[$x];
              }
              $x++;
              $x++;
              }
              $i++;
              $i++;
              } */
            $error = false;
            $wpdb->query('START TRANSACTION');
            foreach ($sqls as $sql) {

                if (!$wpdb->query($sql)) {
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
        return true;
    }

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public static function record_count() {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM " . _LEXICON_WORD_DETAILS;
        return $wpdb->get_var($sql);
    }

    /** Text displayed when no customer data is available */
    public function no_items() {
        _e('No words avaliable.', 'sp');
    }

    /**
     * Render a column when no column specific method exist.
     *
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default($item, $column_name) {

        global $wpdb;
        $databaseName = $wpdb->dbname;

        $word_details_cols = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='$databaseName' AND TABLE_NAME='" . _LEXICON_WORD_DETAILS . "' OR TABLE_NAME='" . _LEXICON_WORD_CODE . "';");

        for ($i = 0; $i <= count($word_details_cols) - 1; $i++) {
            $column_nameA = $word_details_cols[$i]->COLUMN_NAME;

            while ($column_name == $column_nameA) {
                return $item[$column_name];
            }
        }
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    function column_code_id($item) {
        return sprintf(
                '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
        );
    }

    /**
     * Method for code column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_code($item) {
        $delete_nonce = wp_create_nonce('sp_delete_lexicon_word');
        $columnContainsWordCode = 'row-' . $item['id'] . '-contains-code';
        $title = '<strong id="' . $columnContainsWordCode . '">' . $item['code'] . '</strong>';
        $actions = [
            'inline hide-if-no-js' => sprintf('<a href="#row-%s-edit" class="editinline" id="%s" onclick="editWordRow(%s)">Quick Edit</a>', absint($item['id']), absint($item['id']), absint($item['id'])),
            'delete' => sprintf('<a href="?page=%s&action=%s&lexicon_word=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['id']), $delete_nonce)
        ];
        return $title . $this->row_actions($actions);
    }

    public function single_row($item) {

        echo '<tr id="row-' . absint($item['id']) . '">';
        $this->single_row_columns($item);
        echo '</tr>';
    }

    protected function single_row_columns($item) {
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

        foreach ($columns as $column_name => $column_display_name) {
            $classes = "$column_name column-$column_name";
            if ($primary === $column_name) {
                $classes .= ' has-row-actions column-primary';
            }

            if (in_array($column_name, $hidden)) {
                $classes .= ' hidden';
            }

            // Comments column uses HTML in the display name with screen reader text.
            // Instead of using esc_attr(), we strip tags to get closer to a user-friendly string.
            $data = 'data-colname="' . wp_strip_all_tags($column_display_name) . '"';

            $atts_row_id = 'row-' . $item['id'] . '-' . $column_name;

            $attributes = "id='$atts_row_id' class='$classes' $data";

            if ('code_id' === $column_name) {
                echo "<th $attributes>";
                echo $this->column_code_id($item);
                echo '</th>';
            } elseif (method_exists($this, '_column_' . $column_name)) {
                echo call_user_func(
                        array($this, '_column_' . $column_name), $item, $classes, $data, $primary
                );
            } elseif (method_exists($this, 'column_' . $column_name)) {
                echo "<td $attributes>";
                echo call_user_func(array($this, 'column_' . $column_name), $item);
                echo $this->handle_row_actions($item, $column_name, $primary);
                echo "</td>";
            } else {
                echo "<td $attributes>";
                echo $this->column_default($item, $column_name);
                echo $this->handle_row_actions($item, $column_name, $primary);
                echo "</td>";
            }
        }
    }

    /**
     *  Associative array of columns
     *
     * @return array
     */
    function get_columns() {

        global $wpdb;
        $databaseName = $wpdb->dbname;

        $word_details_cols = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='$databaseName' AND TABLE_NAME='" . _LEXICON_WORD_DETAILS . "';");

        if (count($word_details_cols) == 8) {
            $theArrayResult = [
                'code_id' => '<input type="checkbox" />',
                'code' => __('Code', 'sp'),
                'level' => __('Level', 'sp'),
                't_n' => __('T_N', 'sp'),
                'word_coexist' => __('Word Coexist', 'sp'),
                'c_l' => __('C_L', 'sp'),
                's_c' => __('S_C', 'sp'),
                'g_r' => __('G_R', 'sp'),
                'e_j' => __('E_J', 'sp'),
                'p' => __('P', 'sp'),
                'unit' => __('Unit', 'sp'),
                'theme' => __('Theme', 'sp'),
            ];
            //return $theArrayResult;
        } else {
            $endCount = count($word_details_cols);
            $theArrayResultTempA = ['code_id' => '<input type="checkbox" />',
                'code' => __('Code', 'sp'),
                'level' => __('Level', 'sp'),
                't_n' => __('T_N', 'sp'),
                'word_coexist' => __('Word Coexist', 'sp'),
                'c_l' => __('C_L', 'sp'),
                's_c' => __('S_C', 'sp'),
                'g_r' => __('G_R', 'sp'),
                'e_j' => __('E_J', 'sp'),
                'p' => __('P', 'sp'),
                'unit' => __('Unit', 'sp'),
                'theme' => __('Theme', 'sp'),];
            for ($i = 8; $i <= $endCount - 1; $i++) {
                $column_nameA = $word_details_cols[$i]->COLUMN_NAME;
                if ($i % 2 == 0) {
                    $fullLanguage = $this->shortLangToFull(str_replace("_word", "", "$column_nameA"));
                    $column_nameB = $fullLanguage . " Word";
                } else {
                    $fullLanguage = $this->shortLangToFull(str_replace("_phrase", "", "$column_nameA"));
                    $column_nameB = $fullLanguage . " Phrase";
                }
                //$column_nameA = $word_details_cols[$i]->COLUMN_NAME;
                //$column_nameB = $word_details_cols[$i]->COLUMN_NAME;
                //$theArrayResult = [];
                $theArrayResultTempB = ["$column_nameA" => __("$column_nameB", 'sp'),];
                $theArrayResultTempA = array_merge($theArrayResultTempA, $theArrayResultTempB);
                //$i = $i + 2;
                $theArrayResult = $theArrayResultTempA;
            }
            //echo print_r($theArrayResultTempA);
            //return $theArrayResultTempA;
        }
        return $theArrayResult;

        /* $columns = [
          'code_id' => '<input type="checkbox" />',
          'code' => __('Code', 'sp'),
          'level' => __('Level', 'sp'),
          't_n' => __('T_N', 'sp'),
          'word_coexist' => __('Word Coexist', 'sp'),
          'c_l' => __('C_L', 'sp'),
          's_c' => __('S_C', 'sp'),
          'g_r' => __('G_R', 'sp'),
          'e_j' => __('E_J', 'sp'),
          'p' => __('P', 'sp'),
          'unit' => __('Unit', 'sp'),
          'theme' => __('Theme', 'sp'),
          ]; */

        //$columnsPt2 = [];
        //$columnsPt2 = testingv1_get_word_details_cols();
        //$columns = array_merge($columns, $columnsPt2);
        echo print_r($columns);
        echo '<br/><br/>';
        //echo print_r($columnsPt2);
        return $columns;
    }

    public function shortLangToFull($variable) {
        switch ($variable) {
            case 'af': return 'Afrikaans';
            case 'sq': return 'Albanian';
            case 'am': return 'Amharic';
            case 'ar': return 'Arabic';
            case 'hy': return 'Armenian';
            case 'az': return 'Azerbaijani';
            case 'eu': return 'Basque';
            case 'be': return 'Belarusian';
            case 'bn': return 'Bengali';
            case 'bs': return 'Bosnian';
            case 'bg': return 'Bulgarian';
            case 'ca': return 'Catalan';
            case 'ce': return 'Cebuano';
            case 'ny': return 'Chichewa';
            case 'zh-CN': return 'Chinese';
            case 'co': return 'Corsican';
            case 'hr': return 'Croatian';
            case 'cs': return 'Czech';
            case 'da': return 'Danish';
            case 'nl': return 'Dutch';
            case 'en': return 'English';
            case 'eo': return 'Esperanto';
            case 'et': return 'Estonian';
            case 'tl': return 'Filipino';
            case 'fi': return 'Finnish';
            case 'fr': return 'French';
            case 'fy': return 'Frisian';
            case 'gl': return 'Galician';
            case 'ka': return 'Georgian';
            case 'de': return 'German';
            case 'el': return 'Greek';
            case 'gu': return 'Gujarati';
            case 'ht': return 'Haitian Creole';
            case 'ha': return 'Hausa';
            case 'haw': return 'Hawaiian';
            case 'iw': return 'Hebrew';
            case 'hi': return 'Hindi';
            case 'hmn': return 'Hmong';
            case 'hu': return 'Hungarian';
            case 'is': return 'Icelandic';
            case 'ig': return 'Igbo';
            case 'id': return 'Indonesian';
            case 'ga': return 'Irish';
            case 'it': return 'Italian';
            case 'ja': return 'Japanese';
            case 'jw': return 'Javanese';
            case 'kn': return 'Kannada';
            case 'kk': return 'Kazakh';
            case 'km': return 'Khmer';
            case 'ko': return 'Korean';
            case 'ku': return 'Kurdish (Kurmanji)';
            case 'ky': return 'Kyrgyz';
            case 'lo': return 'Lao';
            case 'la': return 'Latin';
            case 'lv': return 'Latvian';
            case 'lt': return 'Lithuanian';
            case 'lb': return 'Luxembourgish';
            case 'mk': return 'Macedonian';
            case 'mg': return 'Malagasy';
            case 'ms': return 'Malay';
            case 'ml': return 'Malayalam';
            case 'mt': return 'Maltese';
            case 'mi': return 'Maori';
            case 'mr': return 'Marathi';
            case 'mn': return 'Mongolian';
            case 'my': return 'Myanmar (Burmese)';
            case 'ne': return 'Nepali';
            case 'no': return 'Norwegian';
            case 'ps': return 'Pashto';
            case 'fa': return 'Persian';
            case 'pl': return 'Polish';
            case 'pt': return 'Portuguese';
            case 'pa': return 'Punjabi';
            case 'ro': return 'Romanian';
            case 'ru': return 'Russian';
            case 'sm': return 'Samoan';
            case 'gd': return 'Scots Gaelic';
            case 'sr': return 'Serbian';
            case 'st': return 'Sesotho';
            case 'sn': return 'Shona';
            case 'sd': return 'Sindhi';
            case 'si': return 'Sinhala';
            case 'sk': return 'Slovak';
            case 'sl': return 'Slovenian';
            case 'so': return 'Somali';
            case 'es': return 'Spanish';
            case 'su': return 'Sundanese';
            case 'sw': return 'Swahili';
            case 'sv': return 'Swedish';
            case 'tg': return 'Tajik';
            case 'ta': return 'Tamil';
            case 'te': return 'Telugu';
            case 'th': return 'Thai';
            case 'tr': return 'Turkish';
            case 'uk': return 'Ukrainian';
            case 'ur': return 'Urdu';
            case 'uz': return 'Uzbek';
            case 'vi': return 'Vietnamese';
            case 'cy': return 'Welsh';
            case 'xh': return 'Xhosa';
            case 'yi': return 'Yiddish';
            case 'yo': return 'Yoruba';
            case 'zu': return 'Zulu';
        }
    }

    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'code' => array('code', true),
            'en_word' => array('text', false)
        );
        return $sortable_columns;
    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = [
            'bulk-delete' => 'Delete'
        ];
        return $actions;
    }

    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items() {
        $this->_column_headers = $this->get_column_info();
        /** Process bulk action */
        $this->process_bulk_action();
        $per_page = $this->get_items_per_page('lexicon_words_per_page', 5);
        $current_page = $this->get_pagenum();
        $total_items = self::record_count();
        $this->set_pagination_args([
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page' => $per_page //WE have to determine how many items to show on a page
        ]);
        $this->items = self::get_lexicon_words($per_page, $current_page);
    }

    protected function display_tablenav($which) {
        if ('top' === $which) {
            wp_nonce_field('bulk-' . $this->_args['plural']);
        }
        ?>
        <div class="tablenav <?php echo esc_attr($which); ?>">

            <div id="custom_alignleft_bulkactions_lexicon">
                <input type="button" id="addWordId" onclick="addWord()" class="button-primary" value="Add Word">
                <input type="button" id="importLexiconLangCSV" onclick="import_lexicon_lang_CSV()" class="button-primary" value="Import">
                <input type="button" id="exportCsvId" onclick="exportCsv()" class="button-primary" value="Export">
            </div>
            <br class="clear" />
            <?php if ($this->has_items()): ?>
                <div class="alignleft actions bulkactions">
                    <?php $this->bulk_actions($which); ?>
                </div>
                <?php
            endif;
            $this->extra_tablenav($which);
            $this->pagination($which);
            ?>

            <br class="clear" />
        </div>
        <?php
    }

    public function process_bulk_action() {
        //Detect when a bulk action is being triggered...
        if ('delete' === $this->current_action()) {
            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr($_REQUEST['_wpnonce']);
            if (!wp_verify_nonce($nonce, 'sp_delete_lexicon_word')) {
                die('Fatal Error');
            } else {
                self::delete_lexicon_word(absint($_GET['lexicon_word']));
                // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
                // add_query_arg() return the current url
                //$origUrl = esc_attr($_REQUEST['page']);
                wp_redirect(esc_url_raw(add_query_arg(array('page' => 'lexicon_testing'), admin_url('admin.php'))));
                exit;
            }
        }
        // If the delete bulk action is triggered
        if (( isset($_POST['action']) && $_POST['action'] == 'bulk-delete' ) || ( isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete' )
        ) {
            $delete_ids = esc_sql($_POST['bulk-delete']);
            // loop over the array of record IDs and delete them
            foreach ($delete_ids as $id) {
                self::delete_lexicon_word($id);
            }
            // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
            // add_query_arg() return the current url
            wp_redirect(esc_url_raw(add_query_arg(array('page' => 'lexicon_testing'), admin_url('admin.php'))));
            exit;
        }
    }

}

class SP_Plugin {

    // class instance
    static $instance;
    // customer WP_List_Table object
    public $lexicon_words_obj;

    // class constructor
    public function __construct() {
        add_filter('set-screen-option', [__CLASS__, 'set_screen'], 10, 3);
        add_action('admin_menu', [$this, 'plugin_menu']);
        add_action('admin_enqueue_scripts', array(&$this, 'testingv1_add_javascript'));
        add_action('wp_print_scripts', array(&$this, 'testingv1_add_javascript'));
        add_action('admin_enqueue_scripts', array(&$this, 'testingv1_add_stylesheet'));
        add_action('wp_print_styles', array(&$this, 'testingv1_add_stylesheet'));
    }

    public static function set_screen($status, $option, $value) {
        return $value;
    }

    function testingv1_add_stylesheet() {
        $styleUrl = plugins_url('css/testingv1.css', __FILE__);
        $styleFile = WP_PLUGIN_DIR . '/testingv1/css/testingv1.css';

        if (file_exists($styleFile)) {
            wp_register_style('testingv1', $styleUrl, array(), '', 'screen');
            wp_enqueue_style('testingv1');
        }
    }

    function testingv1_add_javascript() {
        $scriptUrl = plugins_url('js/testingv1.js', __FILE__);
        $scriptFile = WP_PLUGIN_DIR . '/testingv1/js/testingv1.js';

        if (file_exists($scriptFile)) {
            wp_register_script('testingv1', plugins_url('js/testingv1.js', __FILE__), array('jquery'), '1.0', true);
            wp_enqueue_script('testingv1');
        }
    }

    function testingv1_activation() {

        global $wp_version;
        if (version_compare($wp_version, '3.5', '<')) {
            wp_die('This plugin requires WordPress version 3.5 or higher.');
        }
        if (get_option('testingv1_install') != 1) { //First Activation
            testingv1_install();
            load_plugin_textdomain('testingv1', false, basename(LEXICON_DIR) . '/lang/');
            testingv1_create_page_test();
        }
    }

    static function testingv1_deactivation() {
        global $wpdb;

//  If clean lexicon data is selected in admin panel, clear DB
        if (get_option('testingv1_clear_data_deactive') == 1) {
            define('TESTINGV1_PSEUDO_UNINSTALL', true);
            include_once(LEXICON_DIR . '/uninstall.php');
        }
    }

    function testingv1_install() {
        include_once(LEXICON_DIR . '/install.php');
    }

    public function plugin_menu() {
        $hook = add_menu_page(
                'Lexicon Testing', 'Lexicon Testing', 'manage_options', 'lexicon_testing', [$this, 'plugin_settings_page']
        );
        add_action("load-$hook", [$this, 'screen_option']);
    }

    /**
     * Plugin settings page
     */
    public function plugin_settings_page() {
        ?>
        <div class="wrap">
            <h2>Lexicon Database Management</h2>

            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-3">
                    <div id="post-body-content">
                        <div id="lexicon-table-content-main" class="meta-box-sortables ui-sortable">
                            <form method="post" enctype="multipart/form-data" id="lexicon-form-full">
                                <div id="lexicon-table-content">
                                    <?php
                                    $this->lexicon_words_obj->prepare_items();
                                    $this->lexicon_words_obj->display();
                                    ?>
                                </div>
                            </form>
                            <div id="lexicon-add-word">
                                <input type="button" onclick="backToLexicon()" class="button-secondary" value="Cancel"/>
                            </div>
                            <div id="lexicon-import-file">
                                <form method="post" enctype="multipart/form-data" action="">
                                    <?php include_once('importFile.php'); ?>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
        <?php
    }

    /**
     * Screen options
     */
    public function screen_option() {
        $option = 'per_page';
        $args = [
            'label' => 'Words',
            'default' => 5,
            'option' => 'lexicon_words_per_page'
        ];
        add_screen_option($option, $args);
        $this->lexicon_words_obj = new Lexicon_words_List();
    }

    /** Singleton instance */
    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

}

add_action('activate_testingv1/testingv1.php', array('SP_Plugin', 'testingv1_install'));
register_activation_hook(__FILE__, array('SP_Plugin', 'testingv1_activation'));
register_deactivation_hook(__FILE__, array('SP_Plugin', 'testingv1_deactivation'));

add_action('plugins_loaded', function () {
    SP_Plugin::get_instance();
});
