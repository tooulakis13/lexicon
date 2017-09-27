<?php
/**
 * 	Lexicon Administrator Students Administration Panel
 * 	View all eligible students, enroll and withdraw from courses,
 *	edit and delete students.
 */
 if (current_user_can('mls_lexicon_management')) { /* Check for admin rights */
	if (!class_exists('mls_lexicon_List_Table')) { /* Load class */
		require_once(plugin_dir_path(__FILE__).'class.mls_lexicon_list_table.php');
	}
	/*
	 * Constructor.
	 * @access private
	 * @see WP_List_Table::__construct() for more information on default arguments.
	 * @see MLS_Lexicon_List_Table::__construct() for more details.
	 */
	class Students_Table extends Mls_Lexicon_List_Table {
		function __construct() {
			global $status, $page;
			$table_array = array();
			parent::__construct('stud', 'studs', false);
		}
		/*
		 *	Set default column format
		 *	@param array $item
		 *	@param string $column_name
		 */
		function column_default($item, $column_name) {
			switch ($column_name) {
				case 'student':
				case 'current_course':
				case 'completed_course':
				case 'user_level':
					return $item[$column_name];
				default:
					return print_r($item, true); /* Show the whole array for troubleshooting purposes */
			}
		}
		/* 
		 *	Insert actions into Student column
		 *	@param array $item
		 */
		function column_student($item) {
			$withdraw_nonce = wp_create_nonce('withdraw');
			$delete_user = wp_create_nonce('bulk-users');
			$actions = array(
				'edit' => sprintf('<a href="user-edit.php?user_id=%s">Edit</a>', $item['table_stud_id']),
				'enroll' => sprintf('<a href="?page=%s&action=%s&stud=%s">Enroll</a>', $_REQUEST['page'], 'enroll', $item['table_stud_id']),
				'withdraw' => sprintf('<a href="?page=%s&action=%s&stud=%s&_wpnonce=%s">Withdraw</a>', $_REQUEST['page'], 'withdraw', $item['table_stud_id'], $withdraw_nonce),
				'delete' => sprintf('<a href="users.php?action=%s&user=%s&_wpnonce=%s">Delete student</a>', 'delete', $item['table_stud_id'], $delete_user)
			);
			return sprintf('%1$s %2$s', $item['student'], $this -> row_actions($actions));
		}
		/* 
		 *	Set checkbox column
		 *	@param array $item
		 *	@param NULL $x Null to supress E_STRICT warning
		 */
		function column_cb($item, $x = NULL) { // 
			return parent::column_cb($item, 'table_stud_id');
		}
		/* 
		 *	Retrieve columns
		 *	@param NULL $x Null to supress E_STRICT warning
		 */
		function get_columns($x = NULL) {
			$columns = array(
				'student' => __('Student', 'mls_lexicon'),
				'current_course' => __('Current course(s)', 'mls_lexicon'),
				'completed_course' => __('Completed course(s)', 'mls_lexicon'),
				'user_level' => __('User type', 'mls_lexicon')
			);
			return parent::get_columns($columns);
		}
		/* 
		 *	Retrieve sortable columns
		 *	@param NULL $x Null to supress E_STRICT warning
		 */
		function get_sortable_columns($x = NULL) { // Null to supress E_STRICT warning
			return parent::get_sortable_columns(array(
				'student',
				'current_course',
				'completed_course',
				'user_level'
			));
		}
		/* 
		 *	Retrieve bulk actions
		 */
		function get_bulk_actions() {
			return array(
				'enroll' => 'Enroll',
				'withdraw' => 'Withdraw',
				'delete' => 'Delete'
			);
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
			/*
			 *	SQL Query to fetch all data including search data
			 *	@field student WP User ID
			 *	@field wp.dislay_name WP User display name
			 *	@field wp_meta_value WP User role
			 *	@field current_course All courses the student is enrolled in
			 *	@field completed_course All courses the student completed
			 */
			$sql = "SELECT c.student_id as 'student', wp.display_name, wpm.meta_value, GROUP_CONCAT(DISTINCT CONCAT_WS('-', a.lang_1, a.lang_2, a.level) SEPARATOR '; ') AS 'current_course', GROUP_CONCAT(DISTINCT d.lang_1, d.lang_2, d.level SEPARATOR '; ') AS 'completed_course' FROM ".$wpdb -> prefix.
			"mls_lexicon_course_student AS c LEFT JOIN ".$wpdb -> prefix.
			"mls_lexicon_course a ON a.id = c.course_id AND c.state = 0 LEFT JOIN ".$wpdb -> prefix.
			"mls_lexicon_course d ON d.id = c.course_id AND c.state = 1 LEFT JOIN
			{$wpdb->prefix}users wp ON c.student_id = wp.ID LEFT JOIN
			{$wpdb->prefix}usermeta wpm ON wpm.user_id = c.student_id AND wpm.meta_key='wp_capabilities'
			GROUP BY c.student_id";
			/* Send data to ::parent for phase 1 */
			$res = parent::prepare_items($sql, 1, true);
			$count = 1; /* Proccessed rows counts */
			$table_array = array();
			foreach($res as $row) { /* Assign data variables */
				foreach($row as $field => $value) {
					if ($field == 'student') {
						$id = $value;
					}
					elseif($field == 'current_course') {
						$curr_course = $value;
					}
					elseif($field == 'completed_course') {
						$comp_course = $value;
					}
					elseif($field == 'display_name') {
						$user_fullname = $value;
					}
				}
				$user_data = get_userdata($id);
				$user_role = translate_user_role($wp_roles -> role_names[$user_data -> roles[0]]);
				/* Prepare table data array */
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
			/* Get list of all eligible students */
			$all_lex_students = get_mls_lexicon_users(array('mls_lexicon_admin', 'mls_lexicon_editor', 'mls_lexicon_teacher'));
			$unlisted_students = array();
			/* Check for new, unenrolled students */
			if (empty($table_array) && empty($all_lex_students)) {
				/* There are no students */
				$table_array = NULL;
			}
			elseif(empty($all_lex_students)) {
				/* All existing students are already in table data */
			} else {
				/* There are new, unenrolled students */
				foreach($all_lex_students as $unlisted) {
					$is_listed = false;
					if (!empty($table_array)) {
						/* Compare arrays to find students missing from table data */
						foreach($table_array as $listed) {
							if ($unlisted -> ID == $listed['table_stud_id']) {
								/* Student already in table data, break */
								$is_listed = true;
								break;
							}
						}
					}
					if (!$is_listed) {
						/* Student is unlisted, instert into unlisted array */
						$unlisted_students[] = array(
							'ID' => $count,
							'table_stud_id' => $unlisted -> ID,
							'student' => $unlisted -> display_name,
							'current_course' => '',
							'completed_course' => '',
							'user_level' => translate_user_role($wp_roles -> role_names[$unlisted -> roles[0]])
						);
						$count++;
					}
				}
				/* Merge table data and unlisted array */
				$table_array = array_merge($table_array, $unlisted_students);
			}
			/* Send completed data to ::parent */
			parent::prepare_items(NULL, 2, false, $this, $table_array);
		}
	}
	
	/* Initialize Table */
	$students_Table = new Students_Table();
	/* Prepare Data */
	$students_Table -> prepare_items(); ?>

<div class = "wrap">
  <h2><?php echo __('Students Management', 'mls_lexicon'); ?> </h2>
  <?php
  /* Detect actions */
	if (isset($_GET['stud']) && isset($_GET['action'])) {
		global $wpdb;
		/* 
		 * Bulk Delete
		 * Generate WP nonce and send selected user(s) data to users.php
		 * @see /wp-admin/users.php for details
		 */
		if (isset($_GET['action2']) && $_GET['action'] == 'delete') { 
			$delete_all_nonce = wp_create_nonce('bulk-users');
			$url = sprintf('users.php?s=&_wpnonce=%s&action=%s', $delete_all_nonce, 'delete');
			foreach($_GET['stud'] as $stud) {
				$url.= '&users[]='.$stud;
			}
			print '<div id="message_red" class="notice"><p>'.__('Redirecting...', 'mls_lexicon')
				.
			'</p></div>'; ?>
  <script>
				window.location = '<?php echo $url; ?>'; </script>
  <?php
		} else { 
			/* Other Actions */
			if(isset($_GET['action2']) && $_GET['action'] == '-1') {
				$switcher = $_GET['action2'];
			} else {
				$switcher = $_GET['action'];
			}
			
			/* Common data containers */
			switch ($switcher) {
				case 'enroll':
					$sql_joiner = 'not ';
					$warning = __('Warning: Student will not be enrolled in a course he/she is already enrolled in.', 'mls_lexicon');
					$header = __('Student(s) enrollment:', 'mls_lexicon');
					$action = 'enroll';
					break;
				case 'withdraw':
					$sql_joiner = '';
					$warning = __('Warning: Course list contain all of the courses in which at least one of the selected students is enrolled in.', 'mls_lexicon');
					$header = __('Student(s) withdrawal:', 'mls_lexicon');
					$action = 'withdraw';
					break;
				default:
				/* Throw error, prevent user from continuing */
					trigger_error("Wrong action", E_USER_ERROR);
					die();
			}
			/*
			 * Prepare additional data for enrollment or withdrawal
			 */
			if (isset($_GET['action2'])) { /* Bulk actions */
				$res_courses = array();
				foreach($_GET['stud'] as $key => $s) { /* Student name and id */
						$user_id[] = $s;
						$user_data = get_userdata($s);
						$user_fullname[] = $user_data -> first_name.
						" ".$user_data -> last_name;
			/*
			 *	SQL query to fetch all courses from the student to be enrolled in or withdrawn from
			 *	@field c.id Course ID
			 *	@field iso Course ISO
			 *	@var string $sql_joiner determines enrollment or withdrawal data sets
			 *  @var integer $s WP User ID
			 */
						$courses_sql = "select  c.id, CONCAT_WS('-', c.lang_1, c.lang_2, c.level) as iso from wp_mls_lexicon_course as c where ".$sql_joiner.
						"exists (select s.student_id from wp_mls_lexicon_course_student as s where s.student_id = ".$s.
						" AND s.course_id = c.id)";
						$res = $wpdb -> get_results($courses_sql);
			 /*
			  *	Cross-check results.
			  * Will select all courses that either one of the students is not enrolled.
			  */
						foreach($res as $res_key) {
							if (!in_array($res_key, $res_courses)) {
								$res_courses[] = $res_key;
							}
						}
					}
			} else { /* Single-user actions */
				$user_id[] = $_GET['stud'];
				$user_data = get_userdata($user_id[0]);
				$user_fullname[] = $user_data -> first_name.
				" ".$user_data -> last_name;
				$res_courses = array();
			/*
			 *	SQL query to fetch all courses from the student to be enrolled in or withdrawn from
			 *	@field c.id Course ID
			 *	@field iso Course ISO
			 *	@var string $sql_joiner determines enrollment or withdrawal data sets
			 *  @var integer $s WP User ID
			 */
				$courses_sql = "select  c.id, CONCAT_WS('-', c.lang_1, c.lang_2, c.level) as iso from wp_mls_lexicon_course as c where ".$sql_joiner.
				"exists (select s.student_id from wp_mls_lexicon_course_student as s where s.course_id = c.id AND s.student_id = ".$user_id[0].
				" )";
				$res = $wpdb -> get_results($courses_sql);
				foreach($res as $res_key) {
					$res_courses[] = $res_key;
				}
			} ?>
  <div class="postbox-container" style="width: 100%;">
    <div id="poststuff">
      <div id="postdiv" class="postarea">
      <form id="stud_form" name="post" action="" method="post">
        <div class="notice notice-warning">
          <p> <i> <?php echo $warning; ?> </i></p>
        </div>
        <div class="postbox">
          <h3 class="hndle"> <span> <?php echo $header; ?> </span></h3>
          <!-- STUDENT SELECT BOX START -->
          <div id="sel_studs" class="inside" style="padding:8px;float:left;width: 32%; min-width: 200px;">
            <p>
              <?php __('Selected students:', 'mls_lexicon'); ?>
            </p>
            <p>
              <select id="stud_select" name="stud_select" form="stud_form" style="width: 30%; height: auto !important;"	size="<?php echo (count($_GET['stud']) < 10 ? count($_GET['stud']) : '10');?>"	required disabled multiple>
                <?php
			for ($i=0; $i < count($user_id); $i++) {
				echo '<option value="'.$user_id[$i].
				'" selected>'.$user_fullname[$i].
				'</option>';
			} ?>
              </select>
            </p>
          </div>
          <!-- STUDENT SELECT BOX END --> 
          <!-- COURSE SELECT BOX START -->
          <div id="cour_studs" class="inside" style="padding:8px; float: left; width: 32%; min-width: 200px;">
            <p>
              <?php __('Courses:', 'mls_lexicon'); ?>
            </p>
            <p>
              <select id="cour_select" name="cour_select" form="stud_form" style="width: 30%; height: auto !important;" size="<?php echo (count($res_courses) < 10 ? count($res_courses) : '10');?>" required multiple>
                <?php
			if (isset($res_courses) && count($res_courses) != 0) {
				for ($i = 0; $i < count($res_courses); $i++) {
					echo '<option value="'.$res_courses[$i] -> id.
					'" name="X">'.$res_courses[$i] -> iso.
					'</option>';
				}
			} else {
				echo sprintf('<option value="NULL" disabled>%s</option>', __('Nothing was found.', 'mls_lexicon'));
			} ?>
              </select>
            </p>
            <div class="button action" id="select_all" > <?php echo __('Select All', 'mls_lexicon') ?> </div>
          </div>
          <div id="sub_studs" class="inside" style="padding:8px; float: left; width: 32%; min-width: 200px;"> 
				<?php wp_nonce_field($action, 'action_nonce', false, true); ?>
			
          <input id="form_submit" type="submit" class="button button-hero button-primary action" value="<?php echo __('Submit', 'mls_lexicon')?>">
        </div>
        <div style="clear: both;"></div>
        </div>
      </form>
    </div>
  </div>
</div>
<script> // Ajax and select handler
			jQuery(document).ready(function(e) {
				jQuery("#select_all").click(function(e) { // Select All
					jQuery("#cour_select > option").each(function(index, element) {
						jQuery(element).attr('selected', 'selected');
					});
				});
				jQuery("#stud_form").submit(function(e) {
					e.preventDefault();
					jQuery("#form_submit").attr('disabled', 'disabled');
					jQuery(".spinner").show();
					// Variables
					var loc = 'admin_students';
					var act = '<?php echo $_GET['action']; ?>';
					var base_elements = [];
					var target_elements = [];
					var nonce = jQuery("#action_nonce").val();
					jQuery("#stud_select option:selected").each(function(index, element) {
						base_elements.push(jQuery(element).val());
					});
					jQuery("#cour_select option:selected").each(function(index, element) {
						target_elements.push(jQuery(element).val());
					});
					var data = {
						action: 'admin_submits',
						loc: loc,
						act: act,
						base_elem: base_elements,
						target_elem: target_elements,
						nonce: nonce
					};
					jQuery.ajax({
						type: 'POST',
						dataType: "json",
						url: ajaxurl,
						data: data,
						success: function(response) {
							if(response.type === 'success') {
								jQuery("#message>p>i").html(response.msg);
								jQuery("#message").addClass("notice-success");
							}
							else {
								if(response == -1) {
									var err_msg = ("Security error.");
								}
								else {
									var err_msg = response.msg;
								}
								jQuery("#message>p>i").html(err_msg);
								jQuery("#message").addClass("notice-error");
							}
							jQuery(".spinner").hide();
							jQuery("#message").slideDown(200).delay(1200).slideUp(200, function() {
								window.location = '?page=mls_lexicon_admin_students';
							});
						}
					});
				});
			});

</script>
<?php } } $students_Table->display($students_Table); } ?>
</div>
            <div style="position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);">
  <div id="message" class="notice inline" style="text-align:center; display: none;"><p style="font-size: 1.2em; line-height: 2.1em;"><i></i></p></div>
  <div class="spinner"></div>
  </div>
            
