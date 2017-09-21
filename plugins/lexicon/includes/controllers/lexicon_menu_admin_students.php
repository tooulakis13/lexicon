<?php
/**
 * Students Administration Panel
 */
if (current_user_can('lexicon_management')) {
	if (!class_exists('Lexicon_List_Table')) {
		require_once(plugin_dir_path(__FILE__) . 'class.lexicon_list_table.php');
	}
	class Students_Table extends Lexicon_List_Table {
		function __construct() {
			global $status, $page;
			$table_array = array();
			parent::__construct('stud', 'studs', false); // Singular records label, plural, Ajax support
		}
		function column_default($item, $column_name) {
			switch ($column_name) {
				case 'student':
				case 'current_course':
				case 'completed_course':
				case 'user_level':
					return $item[$column_name];
				default:
					return print_r($item, true); //Show the whole array for troubleshooting purposes
			}
		}
		function column_student($item) {
			$withdraw_nonce = wp_create_nonce('withdraw');
			$delete_user    = wp_create_nonce('bulk-users');
			$actions        = array(
				'edit' => sprintf('<a href="user-edit.php?user_id=%s">Edit</a>', $item['table_stud_id']),
				'enroll' => sprintf('<a href="?page=%s&action=%s&stud=%s">Enroll</a>', $_REQUEST['page'], 'enroll', $item['table_stud_id']),
				'withdraw' => sprintf('<a href="?page=%s&action=%s&stud=%s&_wpnonce=%s">Withdraw</a>', $_REQUEST['page'], 'withdraw', $item['table_stud_id'], $withdraw_nonce),
				'delete' => sprintf('<a href="users.php?action=%s&user=%s&_wpnonce=%s">Delete student</a>', 'delete', $item['table_stud_id'], $delete_user)
			);
			return sprintf('%1$s %2$s', $item['student'], $this->row_actions($actions));
		}
		function column_cb($item, $x = NULL) { // Null to supress E_STRICT warning
			return parent::column_cb($item, 'table_stud_id');
		}
		function get_columns($x = NULL) { // Checkbox provided by parent, Null to supress E_STRICT warning
			$columns = array(
				'student' => __('Student', 'lexicon'),
				'current_course' => __('Current course(s)', 'lexicon'),
				'completed_course' => __('Completed course(s)', 'lexicon'),
				'user_level' => __('User type', 'lexicon')
			);
			return parent::get_columns($columns);
		}
		function get_sortable_columns($x = NULL) { // Null to supress E_STRICT warning
			return parent::get_sortable_columns(array(
				'student',
				'current_course',
				'completed_course',
				'user_level'
			));
		}
		function get_bulk_actions() {
			return array(
				'enroll' => 'Enroll',
				'withdraw_all' => 'Withdraw from all'
			);
		}
		function usort_reorder($a, $b) {
			return parent::usort_reorder($a, $b);
		}
		function prepare_items($x = NULL, $y = NULL, $z = NULL, $q = NULL) {
			global $wpdb;
			global $wp_roles;
			$sql   = "SELECT c.student_id as 'student', GROUP_CONCAT(DISTINCT CONCAT_WS('-', a.lang_1, a.lang_2, a.level) SEPARATOR '; ') AS 'current_course', GROUP_CONCAT(DISTINCT d.lang_1, d.lang_2, d.level SEPARATOR '; ') AS 'completed_course' FROM " . $wpdb->prefix . "lexicon_course_student AS c LEFT JOIN " . $wpdb->prefix . "lexicon_course a ON a.id = c.course_id AND c.state = 0 LEFT JOIN " . $wpdb->prefix . "lexicon_course d ON d.id = c.course_id AND c.state = 1 GROUP BY c.student_id";
			$res   = parent::prepare_items($sql, 1);
			$count = 1;
			$table_array = array();
			foreach ($res as $row) {
				foreach ($row as $field => $value) {
					if ($field == 'student'){
						$id = $value;
					}
					elseif ($field == 'current_course') {
						$curr_course = $value;
					} elseif ($field == 'completed_course') {
						$comp_course = $value;
					}
				}
				$user_data     = get_userdata($id);
				$user_fullname = $user_data->first_name . " " . $user_data->last_name;
				$user_role     = translate_user_role($wp_roles->role_names[$user_data->roles[0]]);
				$table_array[] = array(
					'ID' => $count,
					'table_stud_id' => $id,
					'student' => $user_fullname,
					'current_course' => $curr_course,
					'completed_course' => $comp_course,
					'user_level' => $user_role
				);
				$count++;
			}
			$all_lex_students = get_lexicon_users(array('lexicon_admin', 'lexicon_editor', 'lexicon_teacher'));
			$unlisted_students[] = array();
			
				foreach($all_lex_students as $unlisted) {
					$is_listed = false;
						foreach($table_array as $listed) {
							if($listed['table_stud_id']	== $unlisted->ID) {
								$is_listed = true;
								break;
							}
					if(!$is_listed) {
						$unlisted_students[] = array(
					'ID' => $count,
					'table_stud_id' => $unlisted->ID,
					'student' => $unlisted->display_name,
					'current_course' => '',
					'completed_course' => '',
					'user_level' => translate_user_role($wp_roles->role_names[$unlisted->roles[0]])
				);
				$count++;
					}
							
						}
				}
								
			
			$table_array = $table_array + $unlisted_students;
			parent::prepare_items(NULL, 2, $this, $table_array);
		}
	}
	$students_Table = new Students_Table();
	$students_Table->prepare_items();
?>

<div class="wrap" >
  <h2>Students Management</h2>
  
    <?php
	if(isset($_GET['stud']) && isset($_GET['action'])) {
		print_r($_GET["_wp_http_referer"]); echo "<br> Student : ";
		print_r($_GET['stud']); echo "<br> Action : ";
		print_r($_GET['action']); echo "<br> Action 2 : ";
		print_r($_GET['action2']); echo "<br>";
	}
	
	
	
	
	
	
	$students_Table->display($students_Table);

}
?>
</div>