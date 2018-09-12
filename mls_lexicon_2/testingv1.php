<?php
/*
  Plugin Name: testingv1
  Description: Testing
  Version: 1.0
 */

// Location variables
define('LEXICON_DIR', dirname(__FILE__));
define('LEXICON_DIR_RELATIVO', dirname(plugin_basename(__FILE__)));
define('LEXICON_URL', plugin_dir_url(__FILE__));

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
        $sql = "SELECT * FROM {$wpdb->prefix}lexicon_words";
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
    public static function import_lexicon_lang_CSV() {
        //define('LEXICON_DIR', dirname(__FILE__));
        //define('LEXICON_DIR_RELATIVO', dirname(plugin_basename(__FILE__)));
        //define('LEXICON_URL', plugin_dir_url(__FILE__));
//Para cada fichero en el directorio /idioms
        //$dir = str_replace("\\", "/", LEXICON_DIR) . '/lexicon_languages';
        //lexicon_load($dir, 'lang');
//Para cada fichero en el directorio /courses
//$dir = str_replace("\\", "/", LEXICON_DIR) . '/lexicon_courses';
//$this->lexicon_load($dir, 'course');
    }

    public function lexicon_load($dir, $type) {
        echo '<script type="text/javascript">alert("In lexicon load function")</script>';
        $directory = opendir($dir);
        while ($archive = readdir($directory)) {
            if ($archive != '.' && $archive != '..') {
                switch ($type) {
                    case 'lang':
                        $x = $this->lexicon_load_lang($dir, $archive);
                        break;
                    case 'course':
                        $x = $this->lexicon_load_course($dir, $archive);
                        break;
                    default:
                }
            }
        }
        closedir($directory);
        echo "Done";
    }

    public function lexicon_load_lang($dir, $lang_name) {
        echo '<script type="text/javascript">alert("In lexicon load lang function")</script>';
        global $wpdb;
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
        return true;
    }

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
        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}lexicon_words";
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
        switch ($column_name) {
            case 'code':
            case 'text':
            case 'phrase':
            case 'context':
            case 'column_6':
            case 'column_7':
            case 'column_8':
            case 'column_9':
            case 'column_10':
            case 'column_11':
            case 'column_12':
            case 'column_13':
            case 'column_14':
            case 'column_15':
            case 'column_16':
            case 'level':
            case 'lang':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    function column_cb($item) {
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
        /* ----------------------------------------------------------------------------- */
        /*
          echo '<tr id="row-' . absint($item['id']) . '-edit" style="display: none;">';
          echo '<td colspan="3">'
          . '<b>Word Code: ' . $item['code'] . '</b><br><br>'
          . 'Text: ' . '<input type="text" value="' . $item['text'] . '"/> <br><br>'
          . 'Phrase: ' . '<input type="text" value="' . $item['phrase'] . '"/> <br><br>'
          . 'Level: ' . '<input type="text" value="' . $item['level'] . '"/> <br><br>'
          . '<input class="button-secondary" type="button" id="' . absint($item['id']) . '" value="Cancel" onclick="cancelMods(this.id)" />'
          . '   <input class="button-primary" type="button" id="' . absint($item['id']) . '" value="Update" onclick="applyMods(this.id)" />'
          . '</td>';
          echo '<td colspan="2">'
          . 'Word Code: ' . $item['code'] . '<br><br>'
          . 'Text: ' . '<input type="text" value="' . $item['text'] . '"/> <br><br>'
          . '</td>';
          echo '<td colspan="2">'
          . 'Word Code: ' . $item['code'] . '<br><br>'
          . 'Text: ' . '<input type="text" value="' . $item['text'] . '"/> <br><br>'
          . '</td>';
          echo '</tr>'; */
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

            if ('cb' === $column_name) {
                echo '<th scope="row" class="check-column">';
                echo $this->column_cb($item);
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
        $columns = [
            'cb' => '<input type="checkbox" />',
            'code' => __('Code', 'sp'),
            'text' => __('Text', 'sp'),
            'phrase' => __('Phrase', 'sp'),
            'context' => __('Context', 'sp'),
            'level' => __('Level', 'sp'),
            'column_6' => __('Column 6', 'sp'),
            'column_7' => __('Column 7', 'sp'),
            'column_8' => __('Column 8', 'sp'),
            'column_9' => __('Column 9', 'sp'),
            'column_10' => __('Column 10', 'sp'),
            'column_11' => __('Column 11', 'sp'),
            'column_12' => __('Column 12', 'sp'),
            'column_13' => __('Column 13', 'sp'),
            'column_14' => __('Column 14', 'sp'),
            'column_15' => __('Column 15', 'sp'),
            'column_16' => __('Column 16', 'sp'),
            'lang' => __('Lang', 'sp')
        ];
        return $columns;
    }

    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'code' => array('code', true),
            'text' => array('text', false)
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
                <!-- <a href="#" class="button-primary">Import Language File</a> -->
                <?php
                $importLangCSV_nonce = wp_create_nonce('sp_import_lexicon_lang_CSV');
                ?>
                <a class="button-primary" href="?page=<?php echo esc_attr($_REQUEST['page']) ?>&action=importLangCSV&_wpnonce=<?php echo $importLangCSV_nonce ?>">Import Language File</a>

                        <!-- <input type="button" id="loadCsvId" onclick="loadCsv()" class="button-primary" value="Import Language File"> -->
                <input type="button" id="exportCsvId" onclick="exportCsv()" class="button-primary" value="Export CSV">
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
        if ('importLangCSV' === $this->current_action()) {
            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr($_REQUEST['_wpnonce']);
            if (!wp_verify_nonce($nonce, 'sp_import_lexicon_lang_CSV')) {
                die('Fatal Error');
            } else {
                $dir = str_replace("\\", "/", LEXICON_DIR) . '/lexicon_languages';
                self::lexicon_load($dir, 'lang');
                //self::import_lexicon_lang_CSV();
                // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
                // add_query_arg() return the current url
                //$origUrl = esc_attr($_REQUEST['page']);
                //wp_redirect(esc_url_raw(add_query_arg(array('page' => 'lexicon_testing'), admin_url('admin.php'))));
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
    }

    public static function set_screen($status, $option, $value) {
        return $value;
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

        /*         * ***************************************
         * *		Database installation
         * *************************************** */

        global $wpdb;
        $lexicon_db_version = "1.1";
        //$GLOBALS['_LEXICON_COURSE'] = $wpdb->prefix . 'lexicon_course';
        //$GLOBALS['_LEXICON_COURSE_STUDENT'] = $wpdb->prefix . 'lexicon_course_student';
        //$GLOBALS['_LEXICON_COURSE_SUTDENT_CARD'] = $wpdb->prefix . 'lexicon_course_student_card';
        //$GLOBALS['_LEXICON_COURSE_AUTHOR'] = $wpdb->prefix . 'lexicon_course_author';
        //$GLOBALS['_LEXICON_COURSE_CODES'] = $wpdb->prefix . 'lexicon_course_codes';
        //$GLOBALS['_LEXICON_WORDS'] = $wpdb->prefix . 'lexicon_words';
        //$GLOBALS['_LEXICON_WORD_CODE'] = $wpdb->prefix . 'lexicon_word_code';
        //$GLOBALS['_LEXICON_WORD_DETAILS'] = $wpdb->prefix . 'lexicon_word_details';
        define('_LEXICON_COURSE', $wpdb->prefix . 'lexicon_course');
        define('_LEXICON_COURSE_STUDENT', $wpdb->prefix . 'lexicon_course_student');
        define('_LEXICON_COURSE_SUTDENT_CARD', $wpdb->prefix . 'lexicon_course_student_card');
        define('_LEXICON_COURSE_AUTHOR', $wpdb->prefix . 'lexicon_course_author');
        define('_LEXICON_COURSE_CODES', $wpdb->prefix . 'lexicon_course_codes');
        define('_LEXICON_WORDS', $wpdb->prefix . 'lexicon_words');
        define('_LEXICON_WORD_CODE', $wpdb->prefix . 'lexicon_word_code');
        define('_LEXICON_WORD_DETAILS', $wpdb->prefix . 'lexicon_word_details');
        // Check for existing DB
        if (get_option("lexicon_db_version") == "") {
            //No db found

            /*
             * 	DB Tables SQL
             */
            $sqls = Array();
            // lexicon_course
            $sqls[] = "CREATE TABLE `" . _LEXICON_COURSE . "`(
					`id` int unsigned NOT NULL auto_increment PRIMARY KEY,
					`lang_1` varchar(30) NOT NULL DEFAULT '',
					`lang_2` varchar(30) NOT NULL DEFAULT '',		
					`level` varchar(10) NOT NULL DEFAULT '',
					`description` varchar(255)							
					) DEFAULT CHARSET=utf8; ";
            // lexicon_course_student
            $sqls[] = "CREATE TABLE `" . _LEXICON_COURSE_STUDENT . "`(
					`student_id` bigint(20) NOT NULL DEFAULT 0,
					`course_id` int unsigned NOT NULL DEFAULT 0,
					`state` int unsigned NOT NULL DEFAULT 0,
                                        PRIMARY KEY (student_id, course_id),
					CONSTRAINT `COURSE_STUDENT_FK01` FOREIGN KEY (course_id) REFERENCES " . _LEXICON_COURSE . " (id)
                                        ON DELETE CASCADE
                                        ON UPDATE CASCADE	
					) DEFAULT CHARSET=utf8; ";
            // lexicon_course_student_card
            $sqls[] = "CREATE TABLE `" . _LEXICON_COURSE_SUTDENT_CARD . "`(
					`student_id` bigint(20) NOT NULL DEFAULT 0,
					`course_id` int unsigned NOT NULL DEFAULT 0,
					`code` varchar(16) NOT NULL DEFAULT '',
					`prog_level` int unsigned NOT NULL DEFAULT 0,
					CONSTRAINT `COURSE_STUDENT_CARD_FK01` FOREIGN KEY (student_id) REFERENCES " . _LEXICON_COURSE_STUDENT . " (student_id)
                                        ON DELETE CASCADE
                                        ON UPDATE CASCADE,
					CONSTRAINT `COURSE_STUDENT_CARD_FK02` FOREIGN KEY (course_id) REFERENCES " . _LEXICON_COURSE . " (id)
                                        ON DELETE CASCADE
                                        ON UPDATE CASCADE			
					) DEFAULT CHARSET=utf8; ";
            // lexicon_course_author
            $sqls[] = "CREATE TABLE `" . _LEXICON_COURSE_AUTHOR . "`(
					`teacher_id` bigint(20) NOT NULL DEFAULT 0,
					`course_id` int unsigned NOT NULL DEFAULT 0,									
					PRIMARY KEY (teacher_id, course_id)					
					) DEFAULT CHARSET=utf8; ";
            // lexicon_course_codes
            $sqls[] = "CREATE TABLE `" . _LEXICON_COURSE_CODES . "`(
					`course_id` int unsigned NOT NULL DEFAULT 0,
					`code` varchar(16) NOT NULL DEFAULT '',
                                        `context` varchar(120),
					PRIMARY KEY (course_id, code)			
					) DEFAULT CHARSET=utf8; ";
            // lexicon_words
            $sqls[] = "CREATE TABLE `" . _LEXICON_WORDS . "`(
                                `id` int unsigned NOT NULL auto_increment PRIMARY KEY,
                                `code` varchar(16) NOT NULL DEFAULT '',
                                `text` varchar(30) NOT NULL DEFAULT '',
                                `phrase` varchar(120),
          			`context` varchar(120),
  			        `level` varchar(10) NOT NULL DEFAULT '',
  			        `column_6` varchar(10) NOT NULL DEFAULT '',
  			        `column_7` varchar(10) NOT NULL DEFAULT '',
  			        `column_8` varchar(10) NOT NULL DEFAULT '',
  			        `column_9` varchar(10) NOT NULL DEFAULT '',
   			      	`column_10` varchar(10) NOT NULL DEFAULT '',
                                `column_11` varchar(10) NOT NULL DEFAULT '',
  			        `column_12` varchar(10) NOT NULL DEFAULT '',
                                `column_13` varchar(10) NOT NULL DEFAULT '',
                                `column_14` varchar(10) NOT NULL DEFAULT '',
                                `column_15` varchar(10) NOT NULL DEFAULT '',
                                `column_16` varchar(10) NOT NULL DEFAULT '',
                                `lang` varchar(30) NOT NULL DEFAULT ''
					) DEFAULT CHARSET=utf8; ";
            // lexicon_word_code
            $sqls[] = "CREATE TABLE `" . _LEXICON_WORD_CODE . "`(
                                `id` int unsigned NOT NULL PRIMARY KEY,
                                `code` varchar(16) NOT NULL DEFAULT '',
  			        `level` varchar(10) NOT NULL DEFAULT '',
                                `t_n` varchar(16) NOT NULL DEFAULT '',
                                `word_coexist` varchar(80) NOT NULL DEFAULT ''
					) DEFAULT CHARSET=utf8; ";
            // lexicon_word_details
            $sqls[] = "CREATE TABLE `" . _LEXICON_WORD_DETAILS . "`(
                                `code_id` int unsigned NOT NULL,
                                `word` varchar(30) NOT NULL DEFAULT '',
                                `phrase` varchar(120),
  			        `c_l` varchar(10),
  			        `s_c` varchar(10),
  			        `g_r` varchar(10),
  			        `e_j` varchar(10),
   			      	`p` varchar(10),
                                `unit` varchar(10),
  			        `theme` varchar(10),
                                `context` varchar(10),
                                CONSTRAINT `WORD_DETAILS_FK03` FOREIGN KEY (code_id) REFERENCES " . _LEXICON_WORD_CODE . " (id)
                                ON DELETE CASCADE
                                ON UPDATE CASCADE
					) DEFAULT CHARSET=utf8; ";
            $error = false;
            $wpdb->query('START TRANSACTION');
            foreach ($sqls as $sql) {

                if (!$wpdb->query($sql)) {
                    $error = $wpdb->print_error();
                    break;
                };
            }

            if (!$error) {
                $wpdb->query('COMMIT');
                add_option("lexicon_db_version", $lexicon_db_version);
            } else {
                $wpdb->query('ROLLBACK');
                echo $error;
            }
        } else {
            // DB version outdated

            $sqls = Array();
            $sqls[] = "SET foreign_key_checks = 0";
            //lexicon_course
            $sqls[] = "ALTER TABLE `" . _LEXICON_COURSE . "` 
					MODIFY `id` int unsigned NOT NULL auto_increment,
					MODIFY `lang_1` varchar(30) NOT NULL DEFAULT '',
					MODIFY `lang_2` varchar(30) NOT NULL DEFAULT '',		
					MODIFY `level` varchar(10) NOT NULL DEFAULT '',
					MODIFY `description` varchar(255),
					DROP PRIMARY KEY,
					ADD PRIMARY KEY (id)";
            //lexicon_course_student
            $sqls[] = "ALTER TABLE `" . _LEXICON_COURSE_STUDENT . "`
					MODIFY `student_id` bigint(20) NOT NULL DEFAULT 0,
					MODIFY `course_id` int unsigned NOT NULL DEFAULT 0,
					MODIFY `state` int unsigned NOT NULL DEFAULT 0,
					DROP PRIMARY KEY,
                                        ADD PRIMARY KEY (student_id, course_id),
					DROP FOREIGN KEY `COURSE_STUDENT_FK01`,
					CONSTRAINT `COURSE_STUDENT_FK01` FOREIGN KEY (course_id) REFERENCES " . _LEXICON_COURSE . " (id)
                                        ON DELETE CASCADE
                                        ON UPDATE CASCADE";
            // lexicon_course_student_card
            $sqls[] = "ALTER TABLE `" . _LEXICON_COURSE_SUTDENT_CARD . "`
					MODIFY `student_id` bigint(20) NOT NULL DEFAULT 0,
					MODIFY `course_id` int unsigned NOT NULL DEFAULT 0,
					MODIFY `code` varchar(16) NOT NULL DEFAULT '',
					MODIFY `prog_level` int unsigned NOT NULL DEFAULT 0,
					DROP FOREIGN KEY `COURSE_STUDENT_CARD FK02`,
					DROP FOREIGN KEY `COURSE_STUDENT_CARD FK02`,
					CONSTRAINT `COURSE_STUDENT_CARD_FK01` FOREIGN KEY (student_id) REFERENCES " . _LEXICON_COURSE_STUDENT . " (student_id)
                                        ON DELETE CASCADE
                                        ON UPDATE CASCADE,
					CONSTRAINT `COURSE_STUDENT_CARD_FK02` FOREIGN KEY (course_id) REFERENCES " . _LEXICON_COURSE . " (id)
                                        ON DELETE CASCADE
                                        ON UPDATE CASCADE";
            // lexicon_course_author
            $sqls[] = "ALTER TABLE `" . _LEXICON_COURSE_AUTHOR . "`
					MODIFY `teacher_id` bigint(20) NOT NULL DEFAULT 0,
					MODIFY `course_id` int unsigned NOT NULL DEFAULT 0,	
					DROP PRIMARY KEY,								
					ADD PRIMARY KEY (teacher_id, course_id)";
            // lexicon_course_codes
            $sqls[] = "ALTER TABLE `" . _LEXICON_COURSE_CODES . "`
					MODIFY `course_id` int unsigned NOT NULL DEFAULT 0,
					MODIFY `code` varchar(16) NOT NULL DEFAULT '',
                                        MODIFY `context` varchar(120),
		  			DROP PRIMARY KEY,
					ADD PRIMARY KEY (course_id, code)";
            // lexicon_words	
            $sqls[] = "ALTER TABLE `" . _LEXICON_WORDS . "`
					MODIFY `id` int unsigned NOT NULL auto_increment,
					MODIFY `code` varchar(16) NOT NULL DEFAULT '',
					MODIFY `text` varchar(30) NOT NULL DEFAULT '',
					MODIFY `phrase` varchar(120),
                                        MODIFY `context` varchar(120),
					MODIFY `level` varchar(10) NOT NULL DEFAULT '',
                                        MODIFY `column_6` varchar(10) NOT NULL DEFAULT '',
                                        MODIFY `column_7` varchar(10) NOT NULL DEFAULT '',
                                        MODIFY `column_8` varchar(10) NOT NULL DEFAULT '',
                                        MODIFY `column_9` varchar(10) NOT NULL DEFAULT '',
                                        MODIFY `column_10` varchar(10) NOT NULL DEFAULT '',
                                        MODIFY `column_11` varchar(10) NOT NULL DEFAULT '',
                                        MODIFY `column_12` varchar(10) NOT NULL DEFAULT '',
                                        MODIFY `column_13` varchar(10) NOT NULL DEFAULT '',
                                        MODIFY `column_14` varchar(10) NOT NULL DEFAULT '',
                                        MODIFY `column_15` varchar(10) NOT NULL DEFAULT '',
                                        MODIFY `column_16` varchar(10) NOT NULL DEFAULT '',
                                        MODIFY `lang` varchar(30) NOT NULL DEFAULT '',
					DROP PRIMARY KEY,
				  	ADD PRIMARY KEY (id)";
            // lexicon_word_code
            $sqls[] = "ALTER TABLE `" . _LEXICON_WORD_CODE . "`
                                MODIFY `id` int unsigned NOT NULL,
                                MODIFY `code` varchar(16) NOT NULL DEFAULT '',
  			        MODIFY `level` varchar(10) NOT NULL DEFAULT '',
                                MODIFY `t_n` varchar(16) NOT NULL DEFAULT '',
                                MODIFY `word_coexist` varchar(80) NOT NULL DEFAULT ''
                                DROP PRIMARY KEY,
				ADD PRIMARY KEY (id)";
            // lexicon_word_details
            $sqls[] = "ALTER TABLE `" . _LEXICON_WORD_DETAILS . "`
                                MODIFY `code_id` int unsigned NOT NULL,
                                MODIFY `word` varchar(30) NOT NULL DEFAULT '',
                                MODIFY `phrase` varchar(120),
  			        MODIFY `c_l` varchar(10),
  			        MODIFY `s_c` varchar(10),
  			        MODIFY `g_r` varchar(10),
  			        MODIFY `e_j` varchar(10),
   			      	MODIFY `p` varchar(10),
                                MODIFY `unit` varchar(10),
  			        MODIFY `theme` varchar(10),
                                DROP FOREIGN KEY `WORD_DETAILS FK03`,
                                CONSTRAINT `WORD_DETAILS_FK03` FOREIGN KEY (code_id) REFERENCES " . _LEXICON_WORD_CODE . " (id)
                                ON DELETE CASCADE
                                ON UPDATE CASCADE";
            $sqls[] = "SET foreign_key_checks = 1";

            $error = false;
            $wpdb->query('START TRANSACTION');
            foreach ($sqls as $sql) {

                if (!$wpdb->query($sql)) {
                    $error = $wpdb->print_error();
                    break;
                };
            }

            if ($error) {
                $wpdb->query('COMMIT');
                update_option("lexicon_db_version", $lexicon_db_version);
            } else {
                $wpdb->query('ROLLBACK');
                echo $error;
            }
        }

        if (get_page_by_title('LEXICON') == null) {
            $post = array();
            $post['post_title'] = 'LEXICON';
            $post['post_type'] = 'page';
            $post['post_content'] = '[lexicon]';
            $post['post_status'] = 'publish';
            $post['post_author'] = 1;
            $post['comment_status'] = 'closed';
            $post['ping_satuts'] = 'closed';
            $post['page_template'] = 'front-page.php';
            wp_insert_post($post);
        }
        /*
         * 	Lexicon Options
         */

        if (get_option("testingv1_install") == "") {
            add_option("testingv1_install", '1');
        } else {
            update_option("testingv1_install", '1');
        }
        if (get_option("testingv1_clear_data_deactive") == "") {
            add_option("testingv1_clear_data_deactive", '1');
        } else {
            update_option("testingv1_clear_data_deactive", '1');
        }
        if (get_option("testingv1_cleanup_db") == "") {
            add_option("testingv1_cleanup_db", '1');
        } else {
            update_option("testingv1_cleanup_db", '1');
        }
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
                        <div id="lexicon-table-content" class="meta-box-sortables ui-sortable">
                            <form method="post">
                                <?php
                                $this->lexicon_words_obj->prepare_items();
                                $this->lexicon_words_obj->display();
                                ?>
                            </form>
                        </div>
                        <div id="lexicon-add-word">
                            <input type="button" onclick="backToLexicon()" class="button-secondary" value="Cancel"/>
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
