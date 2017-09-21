<?php
/**
 * Teachers Administration Panel
 */
if (current_user_can('mls_lexicon_management')) {
	if (!class_exists('mls_lexicon_List_Table')) {
		require_once(plugin_dir_path(__FILE__) . 'class.mls_lexicon_list_table.php');
	}
	class Teachers_Table extends Mls_Lexicon_List_Table {
		function __construct() {
			global $status, $page;
			$table_array = array();
			parent::__construct('teacher', 'teachers', false); // Singular records label, plural, Ajax support
		}
		function column_default($item, $column_name) {
			switch ($column_name) {
				case 'teacher':
				case 'c_iso':
					return $item[$column_name];
				default:
					return print_r($item, true); //Show the whole array for troubleshooting purposes
			}
		}
		function column_teacher($item) {
			$delete_user = wp_create_nonce('bulk-users');
			$actions = array(
				'edit' => sprintf('<a href="user-edit.php?user_id=%s">Edit teacher</a>', $item['t_id']),
				'delete' => sprintf('<a href="users.php?action=%s&user=%s&_wpnonce=%s">Delete teacher</a>', 'delete', $item['t_id'], $delete_user)
				
			);
			return sprintf('%1$s %2$s', $item['teacher'], $this->row_actions($actions));
		}
		function column_cb($item, $x = NULL) { // Null to supress E_STRICT warning
			return parent::column_cb($item, 't_id');
		}
		function get_columns($x = NULL) { // Checkbox provided by parent, Null to supress E_STRICT warning
			$columns = array(
				'teacher' => __('Teacher', 'mls_lexicon'),
				'c_iso' => __('Courses', 'mls_lexicon')
			);
			return parent::get_columns($columns);
		}
		function get_sortable_columns($x = NULL) { // Null to supress E_STRICT warning
			return parent::get_sortable_columns(array(
				'teacher', 'c_iso'
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
		function prepare_items($x = NULL, $y = NULL, $z = NULL, $q = NULL, $xz = NULL) {
			global $wpdb;
			global $wp_roles;
			$sql = "SELECT t.teacher_id as 'teacher', wp.display_name,  GROUP_CONCAT(DISTINCT CONCAT_WS('-', b.lang_1, b.lang_2, b.level) SEPARATOR '; ') AS 'courses' FROM ".$wpdb -> prefix.
			"mls_lexicon_course_author AS t LEFT JOIN ".$wpdb -> prefix.
			"mls_lexicon_course b ON t.course_id = b.id LEFT JOIN {$wpdb->prefix}users wp ON t.teacher_id = wp.ID GROUP BY t.teacher_id ";
			
			
			$res   = parent::prepare_items($sql, 1, true);
			$count = 1;
			$table_array = array();
			foreach ($res as $row) {
				foreach ($row as $field => $value) {
					if ($field == 'teacher')
						$id = $value;
					elseif ($field == 'courses') {
						$c_iso = $value;
					}
					elseif ($field == 'display_name') {
						$user_fullname = $value;
					}
				}
				$user_data     = get_userdata($id);
				$table_array[] = array(
					'ID' => $count,
					'teacher' => $user_fullname,
					'c_iso' => $c_iso,
					't_id' => $id
				);
				$count++;
			}
			parent::prepare_items(NULL, 2, false, $this, $table_array);
		}
	}
	$teachers_Table = new Teachers_Table();
	$teachers_Table->prepare_items();
?>

<div class="wrap" >
 <h2><?php echo __('Teachers Management', 'mls_lexicon'); ?> </h2>
  <?php
	if (isset($_GET['teacher']) && isset($_GET['action'])) {
		global $wpdb;
		if (isset($_GET['action2']) && $_GET['action'] == 'delete') { // Bulk Delete
			$delete_all_nonce = wp_create_nonce('bulk-users');
			$url = sprintf('users.php?s=&_wpnonce=%s&action=%s', $delete_all_nonce, 'delete');
			foreach($_GET['teacher'] as $teach) {
				$url.= '&users[]='.$teach;
			}
			print '<div id="message" class="notice"><p>'.__('Redirecting...', 'mls_lexicon').'</p></div>';
			?>
  			<script>
				window.location = '<?php echo $url; ?>';
            </script>
  		<?php
		} 
	}
}
  
  $teachers_Table->display($teachers_Table);

?>