<?php
/**
 * Teachers Administration Panel
 */
if (current_user_can('lexicon_management')) {
	if (!class_exists('Lexicon_List_Table')) {
		require_once(plugin_dir_path(__FILE__) . 'class.lexicon_list_table.php');
	}
	class Teachers_Table extends Lexicon_List_Table {
		function __construct() {
			global $status, $page;
			$table_array = array();
			parent::__construct('teacher', 'teachers', false); // Singular records label, plural, Ajax support
		}
		function column_default($item, $column_name) {
			switch ($column_name) {
				case 'teacher':
				case 'c_iso':
				case 'desc':
					return $item[$column_name];
				default:
					return print_r($item, true); //Show the whole array for troubleshooting purposes
			}
		}
		function column_teacher($item) {
			$actions = array(
				'edit' => sprintf('<a href="user-edit.php?user_id=%s">Edit</a>', $item['t_id']),
				'reass' => sprintf('<a href="?page=%s&action=%s&teacher_id=%s&course_id=%s">Re-assign course</a>', $_REQUEST['page'], 'reass_course', $item['t_id'], $item['c_id'])
			);
			return sprintf('%1$s %2$s', $item['teacher'], $this->row_actions($actions));
		}
		function column_cb($item, $x = NULL) { // Null to supress E_STRICT warning
			return parent::column_cb($item, 't_id');
		}
		function get_columns($x = NULL) { // Checkbox provided by parent, Null to supress E_STRICT warning
			$columns = array(
				'teacher' => __('Teacher', 'lexicon'),
				'c_iso' => __('Course ISO', 'lexicon'),
				'desc' => __('Description', 'lexicon')
			);
			return parent::get_columns($columns);
		}
		function get_sortable_columns($x = NULL) { // Null to supress E_STRICT warning
			return parent::get_sortable_columns(array(
				'teacher', 'c_iso', 'desc'
			));
		}
		function get_bulk_actions() {
			return array(
				
			);
		}
		function usort_reorder($a, $b) {
			return parent::usort_reorder($a, $b);
		}
		function prepare_items($x = NULL, $y = NULL, $z = NULL, $q = NULL) {
			global $wpdb;
			global $wp_roles;
			$sql   = "select a.teacher_id , b.id as 'c_id', CONCAT_WS('-', b.lang_1, b.lang_2, b.level) AS 'c_iso', b.description AS 'desc' FROM ".$wpdb->prefix."lexicon_course_author a LEFT JOIN ".$wpdb->prefix."lexicon_course b ON a.course_id = b.id ";
			$res   = parent::prepare_items($sql, 1);
			$count = 1;
			$table_array = array();
			foreach ($res as $row) {
				foreach ($row as $field => $value) {
					if ($field == 'teacher_id')
						$id = $value;
					elseif ($field == 'c_iso') {
						$c_iso = $value;
					} elseif ($field == 'desc') {
						$desc = $value;
					}elseif ($field == 'c_id') {
						$c_id = $value;
					}
				}
				$user_data     = get_userdata($id);
				$user_fullname = $user_data->first_name . " " . $user_data->last_name;
				$table_array[] = array(
					'ID' => $count,
					'teacher' => $user_fullname,
					'c_iso' => $c_iso,
					'desc' => $desc,
					'c_id' => $c_id,
					't_id' => $id
				);
				$count++;
			}
			parent::prepare_items(NULL, 2, $this, $table_array);
		}
	}
	$teachers_Table = new Teachers_Table();
	$teachers_Table->prepare_items();
?>

<div class="wrap" >
  <h2>Teachers Management</h2>
  
    <?php
	$teachers_Table->display($teachers_Table);

	if (isset($_GET['action']) && isset($_GET['teacher_id']) && isset($_GET['course_id'])) {
		if(isset($_POST['new_teach'])) {
			check_admin_referer('reass_teacher');
			global $wpdb;
			$sql = sprintf("UPDATE %slexicon_course_author SET teacher_id=%s WHERE teacher_id=%s AND course_id=%s", $wpdb->prefix, $_POST['new_teach'], $_GET['teacher_id'], $_GET['course_id']);
			$wpdb->query("START TRANSACTION");
			if(!$wpdb->query($sql)){
				$wpdb->query('ROLLBACK');
			} else {
			$wpdb->query("COMMIT");
			?> 
			<script>
			window.location = '?page=lexicon_admin_teachers';
			</script>
			<?php
			
			}
		}
		$user_arr = get_users();
		$opts = array();
		foreach($user_arr as $user){
			if(($user->has_cap('lexicon_create_course') or $user->has_cap('lexicon_create_custom_course') or $user->has_cap('lexicon_management')) && $user->ID != $_GET['teacher_id']) 
				$opts[] = sprintf('<option value="%s">%s</option>', $user->ID, $user->first_name.' '.$user->last_name);
			
		}
	?>
  <div class="postbox-container">
    <div id="poststuff">
      <div id="postdiv" class="postarea">
        <form id="teach_reass" name="post" action="" method="post">
          <div class="postbox">
          <h3 class="hndle"><span>
            <?php _e('Course re-assignment', 'lexicon') ?>
            </span></h3>
          <div class="inside" style="padding:8px">
          <p>Select user to assign the course to:</p>
          <p>
          <select id="teach_select" name="new_teach" form="teach_reass" style="width: 30%; height: auto !important; float: left;" size="<?php echo (count($opts) < 10 ? count($opts) : '10');?>" required>
          <?php 
		  foreach($opts as $opt)
		  		{ echo $opt; }
		  ?>
          </select>
              
              
            </p>
            <?php wp_nonce_field('reass_teacher'); ?>
            <p>
              <input type='submit' name='reass_admin' value='Assing to selected user' style="float: left; margin-left: 20px"/>
              
            </p>
            <div style="clear: both;"></div>
           
          </div>
        
      </div>
      </form>
    </div>
  </div>
</div>
<?	
		
		
		
?>
</div>
<?php
		
	} else {
?>
</div>
<script>	
	jQuery(document).ready(function(e) {
        	jQuery("#studs-filter").show(400);
    });

	</script>
<?php
	}
}
?>
