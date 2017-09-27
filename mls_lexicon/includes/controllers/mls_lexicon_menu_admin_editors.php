<?php
/**
 * Editors Administration Panel
 */
if (current_user_can('mls_lexicon_management')) {
	if (!class_exists('mls_lexicon_List_Table')) {
		require_once(plugin_dir_path(__FILE__) . 'class.mls_lexicon_list_table.php');
	}
	class Teachers_Table extends Mls_Lexicon_List_Table {
		function __construct() {
			global $status, $page;
			$table_array = array();
			parent::__construct('editor', 'editors', false); // Singular records label, plural, Ajax support
		}
		function column_default($item, $column_name) {
			switch ($column_name) {
				case 'editor':
						return $item[$column_name];
				default:
					return print_r($item, true); //Show the whole array for troubleshooting purposes
			}
		}
		function column_editor($item) {
			$delete_user = wp_create_nonce('bulk-users');
			$actions = array(
				'edit' => sprintf('<a href="user-edit.php?user_id=%s">Edit editor</a>', $item['e_id']),
				'delete' => sprintf('<a href="users.php?action=%s&user=%s&_wpnonce=%s">Delete editor</a>', 'delete', $item['e_id'], $delete_user)
				
			);
			return sprintf('%1$s %2$s', $item['editor'], $this->row_actions($actions));
		}
		function column_cb($item, $x = NULL) { // Null to supress E_STRICT warning
			return parent::column_cb($item, 'e_id');
		}
		function get_columns($x = NULL) { // Checkbox provided by parent, Null to supress E_STRICT warning
			$columns = array(
				'editor' => __('Teacher', 'mls_lexicon')
			);
			return parent::get_columns($columns);
		}
		function get_sortable_columns($x = NULL) { // Null to supress E_STRICT warning
			return parent::get_sortable_columns(array(
				'editor'
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
		function prepare_items($x = NULL, $y = NULL, $d = NULL, $z = NULL, $q = NULL) {
			global $wpdb;
			global $wp_roles;
			$usrs = get_users(array('role' => 'mls_lexicon_editor'));
			$pre_res = array();
			foreach($usrs as $wpu) {
				$obj = new stdClass();
				$obj->editor = $wpu->ID;
				$obj->display_name = $wpu->data->display_name;
				$pre_res[] = $obj;
			}
			$res   = parent::prepare_items($pre_res, 1, false);
			$count = 1;
			$table_array = array();
			foreach ($res as $row) {
				foreach ($row as $field => $value) {
					if ($field == 'editor')
						$id = $value;
					elseif ($field == 'display_name') {
						$user_fullname = $value;
					}
				}
				$user_data     = get_userdata($id);
				$table_array[] = array(
					'ID' => $count,
					'editor' => $user_fullname,
					'e_id' => $id
				);
				$count++;
			}
			parent::prepare_items(NULL, 2, NULL, $this, $table_array);
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