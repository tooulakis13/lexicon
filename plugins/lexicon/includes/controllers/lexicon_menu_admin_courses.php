<?php
/**
 * Courses Administration Panel
 */
if (current_user_can('lexicon_management')) {
	if (!class_exists('Lexicon_List_Table')) {
		require_once(plugin_dir_path(__FILE__) . 'class.lexicon_list_table.php');
	}
	class Course_Table extends Lexicon_List_Table {
		function __construct() {
			global $status, $page;
			$table_array = array();
			parent::__construct('course', 'courses', false); // Singular records label, plural, Ajax support
		}
		function column_default($item, $column_name) {
			switch ($column_name) {
				case 'c_iso':
				case 'desc':
				case 'base_lang':
				case 'tar_lang':
				case 'level':
				case 'author':
					return $item[$column_name];
				default:
					return print_r($item, true); //Show the whole array for troubleshooting purposes
			}
		}
		function column_c_iso($item) {
			$delete_nonce = wp_create_nonce('delete');
			$actions        = array(
				'edit' => sprintf('<a href="?page=%s&action=%s&course_id=%s">Edit</a>',$_REQUEST['page'], 'edit', $item['c_id']),
				'delete' => sprintf('<a href="?page=%s&action=%s&course_id=%s&_wpnonce=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['c_id'], $delete_nonce)
			);
			return sprintf('%1$s %2$s', $item['c_iso'], $this->row_actions($actions));
		}
		function column_cb($item, $x = NULL) { // Null to supress E_STRICT warning
			return parent::column_cb($item, 'c_id');
		}
		function get_columns($x = NULL) { // Checkbox provided by parent, Null to supress E_STRICT warning
			$columns = array(
				'c_iso' => __('Course ISO', 'lexicon'),
				'desc' => __('Description', 'lexicon'),
				'base_lang' => __('Base Language', 'lexicon'),
				'tar_lang' => __('Target Language', 'lexicon'),
				'level' => __('Level', 'lexicon'),
				'author' => __('Author', 'lexicon')
			);
			return parent::get_columns($columns);
		}
		function get_sortable_columns($x = NULL) { // Null to supress E_STRICT warning
			return parent::get_sortable_columns(array(
				'c_iso', 'desc', 'base_lang', 'tar_lang', 'level', 'author'
			));
		}
		function get_bulk_actions() {
			return array(
				'delete' => 'Delete'
			);
		}
		function usort_reorder($a, $b) {
			return parent::usort_reorder($a, $b);
		}
		function prepare_items($x = NULL, $y = NULL, $z = NULL, $q = NULL) {
			global $wpdb;
			global $wp_roles;
			$sql   = "SELECT a.id as 'Course ID', CONCAT_WS('-', a.lang_1, a.lang_2, a.level) AS 'Course ISO', a.description AS 'Description', a.lang_1 AS 'Base Language', a.lang_2 AS 'Target Language', a.level AS 'Level', b.teacher_id AS 'Author' FROM ".$wpdb->prefix."lexicon_course a LEFT JOIN ".$wpdb->prefix."lexicon_course_author b ON a.id = b.course_id";
			$res   = parent::prepare_items($sql, 1);
			$count = 1;
			$table_array = array();
			foreach ($res as $row) {
				foreach ($row as $field => $value) {
					if ($field == 'Course ID')
						$id = $value;
					elseif ($field == 'Course ISO') {
						$cour_iso = $value;
					} elseif ($field == 'Description') {
						$desc = $value;
					}elseif ($field == 'Base Language') {
						$base_lang = $value;
					}elseif ($field == 'Target Language') {
						$tar_lang = $value;
					}elseif ($field == 'Level') {
						$level = $value;
					}elseif ($field == 'Author') {
						$auth_id = $value;
					}
				}
				$user_data     = get_userdata($auth_id);
				$user_fullname = $user_data->first_name . " " . $user_data->last_name;
				$table_array[] = array(
					'ID' => $count,
					'c_id' => $id,
					'c_iso' => $cour_iso,
					'desc' => $desc,
					'base_lang' => $base_lang,
					'tar_lang' => $tar_lang,
					'level' => $level,
					'author' => $user_fullname
				);
				$count++;
			}
			parent::prepare_items(NULL, 2, $this, $table_array);
		}
	}
	$course_Table = new Course_Table();
	$course_Table->prepare_items();
?>

<div class="wrap" >
  <h2>Courses Management</h2>
<?php
	$course_Table->display($course_Table);
?>
 

<?php
	if (isset($_GET['action']) && isset($_GET['stud'])) {
		$action_type = '';
		switch ($_GET['action']) {
			case 'enroll':
				check_admin_referer('enroll');
				$action_type = 'enroll';
				break;
			case 'withdraw':
				check_admin_referer('withdraw');
				$action_type = 'withdraw';
				break;
			case -1: // Bulk action
				if (isset($_GET['action2'])) {
					check_admin_referer('bulk-' . $students_Table->_args['plural']);
					switch ($_GET['action2']) {
						case 'enroll':
							$action_type = 'enroll';
							break;
						case 'withdraw_all':
							$action_type = 'withdraw_all';
							break;
						default:
							die();
							break;
					}
				}
				break;
			default:
				die();
				break;
		}
		// Action execution
		if (isset($action_type)) {
?></div>
<?php
		}
	} else {
?>
</div><script>	
	jQuery(document).ready(function(e) {
        	jQuery("#studs-filter").show(400);
    });

	</script>
<?php
	}
}
?>