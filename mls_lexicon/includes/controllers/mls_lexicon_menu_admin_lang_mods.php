<?php
/**
 * Courses Administration Panel
 */
if (current_user_can('mls_lexicon_management')) {
	if (!class_exists('mls_lexicon_List_Table')) {
		require_once(plugin_dir_path(__FILE__) . 'class.mls_lexicon_list_table.php');
	}
	class Lang_Mods_Table extends Mls_Lexicon_List_Table {
		function __construct() {
			global $status, $page;
			$table_array = array();
			parent::__construct('mod', 'mods', false); // Singular records label, plural, Ajax support
		}
		function column_default($item, $column_name) {
			switch ($column_name) {
				case 'lang':
				case 'level':
					return $item[$column_name];
				default:
					return print_r($item, true); //Show the whole array for troubleshooting purposes
			}
		}
		function column_lang($item) {
			$delete_nonce  = wp_create_nonce('delete');
			$actions = array(
				'edit' => sprintf('<a href="?page=%s&action=%s&mod_id=%s">Edit Module</a>', $_REQUEST['page'], 'edit', $item['ID']),
				'delete' => sprintf('<a href="?page=%s&action=%s&mod_id=%s&_wpnonce=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['ID'] , $delete_nonce)
			);
			return sprintf('%1$s %2$s', $item['lang'], $this->row_actions($actions));
		}
		function column_cb($item, $x = NULL) { // Null to supress E_STRICT warning
			return parent::column_cb($item, 'ID');
		}
		function get_columns($x = NULL) { // Checkbox provided by parent, Null to supress E_STRICT warning
			$columns = array(
				'lang' => __('Language', 'mls_lexicon'),
				'level' => __('Level', 'mls_lexicon')
			);
			return parent::get_columns($columns);
		}
		function get_sortable_columns($x = NULL) { // Null to supress E_STRICT warning
			return parent::get_sortable_columns(array(
				'lang', 'level'
			));
		}
		function get_bulk_actions() {
			return array(
				
			);
		}
		function usort_reorder($a, $b) {
			return parent::usort_reorder($a, $b);
		}
		function prepare_items($x = NULL, $y = NULL, $z = NULL, $q = NULL, $xz = NULL) {
			global $wpdb;
			$sql   = "select id, lang, level from ".$wpdb->prefix."mls_lexicon_lang_mod";
			$res   = parent::prepare_items($sql, 1, true);
			$table_array = array();
			foreach ($res as $row) {
				foreach ($row as $field => $value) {
					if ($field == 'lang'){
						$lang = $value;
				}
					elseif ($field == 'level') {
						$level = $value;
					}
					elseif ($field == 'id') {
						$id = $value;
					}
				}
					$table_array[] = array(
					'ID' => $id,
					'lang' => $lang,
					'level' => $level
				);
			}
			parent::prepare_items(NULL, 2, false,  $this, $table_array);
		}
	}
	$lang_mod_Table = new Lang_Mods_Table();
	$lang_mod_Table->prepare_items();
?>

<div class="wrap" >
  <h2>Language Modules Management</h2>
    <?php
	$lang_mod_Table->display($lang_mod_Table, array('add_mod', 'Add Module'));
	if (isset($_GET['action']) && isset($_GET['mod_id'])) {
		$action_type = '';
		switch ($_GET['action']) {
			case 'edit':
				break;
			case 'delete':
				check_admin_referer('delete');
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