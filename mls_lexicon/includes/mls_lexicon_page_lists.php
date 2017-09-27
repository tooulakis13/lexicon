<?php
/**
 * 	Lexicon Administrator Students Administration Panel
 * 	View all eligible students, enroll and withdraw from courses,
 *	edit and delete students.
 */
 if (current_user_can('mls_lexicon_course_enroll')) { /* Check for admin rights */
	if (!class_exists('mls_lexicon_List_Table')) { /* Load class */
		require_once(plugin_dir_path(__FILE__).'controllers'.DIRECTORY_SEPARATOR.'class.mls_lexicon_list_table.php');
	}
	// Set of list variables
		if($sc_data['list'] == 'courses') {
			$list_type = 'courses';	
			$support_table = 'Enrolled?';
		} else { // default settings
			$list_type = 'courses';	
			$support_table = 'Enrolled?';
		}

		if($sc_data['data_selector'] != 'all') {
			$list_data_selector = $sc_data['data_selector'];
		} else { //default
			$list_data_selector = 'all';
		}

	$GLOBALS["list_type"] = $list_type;
	$GLOBALS["list_data_selector"] = $list_data_selector;
	$GLOBALS["support_table"] = $support_table;
	/*
	 * Constructor.
	 * @access private
	 * @see WP_List_Table::__construct() for more information on default arguments.
	 * @see MLS_Lexicon_List_Table::__construct() for more details.
	 */
	class Lists_Table extends Mls_Lexicon_List_Table {
		var $list_type;
		var $list_data_selector;
		var $support_table;
		
		function __construct() {
			global $status, $page;
			 $this->list_type =  $GLOBALS["list_type"];
			  $this->list_data_selector =  $GLOBALS["list_data_selector"];
			   $this->support_table =  $GLOBALS["support_table"];
				
						parent::__construct('list', 'lists', false);
		}
		/*
		 *	Set default column format
		 *	@param array $item
		 *	@param string $column_name
		 */
		function column_default($item, $column_name) {
			switch ($column_name) {
				case 'data':
					if($this->list_type == 'courses') {
						$actions = array(
				'enroll' => sprintf('<a href="?SignUpCourse&course_id=%d">Enroll</a>',  $item['data_id'])
						);
						return sprintf('%1$s %2$s', $item[$column_name], $this -> row_actions($actions));
					} else {
						return $item[$column_name];
					}
					
				case 'support':
					return $item[$column_name];
				default:
					return print_r($item, true); /* Show the whole array for troubleshooting purposes */
			}
		}
		
		function column_cb($item, $x = NULL) { // 
			//return parent::column_cb($item, 'table_stud_id');
		}
	
	
		/* 
		 *	Retrieve columns
		 *	@param NULL $x Null to supress E_STRICT warning
		 */
		function get_columns($x = NULL) {
			$columns = array(
				'data' => __(ucfirst($this->list_type), 'mls_lexicon'),
				'support' => __($this->support_table, 'mls_lexicon'),
			);
			return parent::get_columns($columns);
		}
		/* 
		 *	Retrieve sortable columns
		 *	@param NULL $x Null to supress E_STRICT warning
		 */
		function get_sortable_columns($x = NULL) { // Null to supress E_STRICT warning
			return parent::get_sortable_columns(array(
				'data',
				'support'
			));
		}
	
		/*
		 *	Comparison function for usort()
		 *	parameters passed to ::parent
		 *	@see function.usort in PHP Manual
		 */
		function usort_reorder($a, $b) {
			return parent::usort_reorder($a, $b);
		}
		/*
		 * Prepare table data for display, then send it to ::parent
		 * @param NULL $x Null to supress E_STRICT warning
		 * @param NULL $y Null to supress E_STRICT warning
		 * @param NULL $z Null to supress E_STRICT warning
		 * @param NULL $q Null to supress E_STRICT warning
		 */
		function prepare_items($x = NULL, $y = NULL, $z = NULL, $q = NULL, $xz = NULL) {
			global $wpdb;
			global $wp_roles;
			/* Determine used data */
			if($this->list_type == 'courses') {
				
				$sql = "SELECT c.id as 'data_id', concat_ws('-', c.lang_1, c.lang_2, c.level) as 'data', IF( (SELECT s.student_id from ".$wpdb -> prefix.
			"mls_lexicon_course_student as s where s.student_id = ".get_current_user_id()." and s.course_id = c.id) > 0 ,'Yes', 'No') as 'support' FROM ".$wpdb -> prefix.
			"mls_lexicon_course as c";

			}
			
			/* Send data to ::parent for phase 1 */
			$res = parent::prepare_items($sql, 1, true);
			$count = 1; /* Proccessed rows counts */
			$table_array = array();
			foreach($res as $row) { /* Assign data variables */
				foreach($row as $field => $value) {
					if ($field == 'data_id') {
						$id = $value;
					}
					elseif($field == 'data') {
						$data = $value;
					}
					elseif($field == 'support') {
						$support = $value;
					}
				}
				/* Prepare table data array */
				$table_array[] = array(
					'ID' => $count,
					'data_id' => $id,
					'data' => $data,
					'support' => $support
				);
				$count++;
			}
			/* Filter results */
			if($this->list_data_selector != 'all' && !empty($table_array)) {
				$vars = explode(',',$this->list_data_selector);
				
				$new_arr = array();
				foreach($table_array as $ta) {
					$found = false;
					foreach($vars as $var) {
						if(strpos($ta['data'], $var) !== false) {
						$found = true;
						break;
						}
					}
					if($found) {
					$new_arr[] = $ta;
					}
				}

				$table_array = $new_arr;
			}
			/* Send completed data to ::parent */
			parent::prepare_items(NULL, 2, false, $this, $table_array);
		}
	}
	
	/* Initialize Table */
	$lists_Table = new Lists_Table();
	/* Prepare Data */
	$lists_Table -> prepare_items(); ?>

<div class = "wrap">
  <h2><?php echo __(ucfirst($list_type), 'mls_lexicon'); ?> </h2>
  <?php
  $lists_Table->display($lists_Table); } ?>
</div>