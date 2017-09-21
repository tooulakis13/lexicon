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
			$enroll_nonce   = wp_create_nonce('enroll');
			$withdraw_nonce = wp_create_nonce('withdraw');
			$delete_user    = wp_create_nonce('bulk-users');
			$actions        = array(
				'edit' => sprintf('<a href="user-edit.php?user_id=%s">Edit</a>', $item['table_stud_id']),
				'enroll' => sprintf('<a href="?page=%s&action=%s&stud=%s&_wpnonce=%s">Enroll</a>', $_REQUEST['page'], 'enroll', $item['table_stud_id'], $enroll_nonce),
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
					if ($field == 'student')
						$id = $value;
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
			parent::prepare_items(NULL, 2, $this, $table_array);
		}
	}
	$students_Table = new Students_Table();
	$students_Table->prepare_items();
?>

<div class="wrap" >
  <h2>Students Management</h2>
    <?php
	$students_Table->display($students_Table);
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
			if($_GET['stud'] > 0) {
			$user_data     = get_userdata($_GET['stud']);
			$user_fullname = $user_data->first_name . " " . $user_data->last_name;
			}
			$users = get_lexicon_users();
			
			
			
			
			?>
	<div class="postbox-container" style="width: 100%;">
    <div id="poststuff">
      <div id="postdiv" class="postarea">
        <form id="teach_reass" name="post" action="" method="post">
          <div class="postbox">
          <h3 class="hndle"><span>
          	<?php switch ($action_type) {
					case 'enroll':
						echo _e('Course enrollment', 'lexicon');
						?>
                        </span></h3>
          <div class="inside" style="padding:8px; float: left; width: 49%; min-width: 400px;">
          <p>Select courses to enroll <?php echo $user_fullname; ?> in:</p>
          <p>
          <select id="teach_select" name="new_teach" form="teach_reass" style="width: 30%; height: auto !important; " size="<?php echo (count($users) < 10 ? count($users) : '10');?>" required>
          <?php 
		  foreach($opts as $opt)
		  		{ echo $opt; }
		  ?>
          </select>
              
              
            </p>
            <?php wp_nonce_field('reass_teacher'); ?>
 </div>
 
 
 <?php
						
						
						
						break;
					case 'withdraw':
					case 'withdraw_all':
						echo _e('Course withdrawal', 'lexicon');
						break;
			}
						 ?>
            
          
        <div class="inside" style="padding:8px; float: left; width: 49%; min-width: 400px;">	
          <p>Select courses to enroll <?php echo $user_fullname; ?> in:</p>
          <p>
          <select id="teach_select" name="new_teach" form="teach_reass" style="width: 30%; height: auto !important; " size="<?php echo (count($opts) < 10 ? count($opts) : '10');?>" required>
          <?php 
		  foreach($opts as $opt)
		  		{ echo $opt; }
		  ?>
          </select>
              
              
            </p>
            <?php wp_nonce_field('reass_teacher'); ?>
           
            
           
          </div><div style="clear: both;"></div>
      </div>
      </form>
    </div>
  </div>
</div>
			
			
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