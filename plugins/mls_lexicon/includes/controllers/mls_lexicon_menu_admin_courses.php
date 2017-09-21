<?php
/**
 * Courses Administration Panel
 */
if (current_user_can('mls_lexicon_management')) {
	if (!class_exists('mls_lexicon_List_Table')) {
		require_once(plugin_dir_path(__FILE__) . 'class.mls_lexicon_list_table.php');
	}
	class Course_Table extends Mls_Lexicon_List_Table {
		function __construct() {
			global $status, $page;
			$table_array = array();
			parent::__construct('course_id', 'course_id', false); // Singular records label, plural, Ajax support
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
				'c_iso' => __('Course ISO', 'mls_lexicon'),
				'desc' => __('Description', 'mls_lexicon'),
				'base_lang' => __('Base Language', 'mls_lexicon'),
				'tar_lang' => __('Target Language', 'mls_lexicon'),
				'level' => __('Level', 'mls_lexicon'),
				'author' => __('Author', 'mls_lexicon')
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
		function prepare_items($x = NULL, $y = NULL, $z = NULL, $q = NULL, $u = NULL) {
			global $wpdb;
			global $wp_roles;
			$sql   = "SELECT a.id as 'Course ID', CONCAT_WS('-', a.lang_1, a.lang_2, a.level) AS 'Course ISO', a.description AS 'Description', a.lang_1 AS 'Base Language', a.lang_2 AS 'Target Language', a.level AS 'Level', b.teacher_id AS 'Author' FROM ".$wpdb->prefix."mls_lexicon_course a LEFT JOIN ".$wpdb->prefix."mls_lexicon_course_author b ON a.id = b.course_id";
			$res   = parent::prepare_items($sql, 1, true);
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
			parent::prepare_items(NULL, 2, NULL, $this, $table_array);
		}
	}
	$course_Table = new Course_Table();
	$course_Table->prepare_items();
?>

<div class="wrap" >
  <h2>Courses Management</h2>
<?php
	
?>
 

<?php
	if (isset($_GET['action'])) {
		$action_type = '';
		switch ($_GET['action']) {
			case 'edit':
				$action_type = 'edit';
				if(isset($_GET['edit_action'])) {
					switch($_GET['edit_action']) {
					case 'w_delete':
					$action_type = 'edit_w_delete';	
					break;
					case 'add_new_word':
					$action_type = 'edit_add_new_word';	
					break;
					default:
					break;	
					}
					
				}
				break;
			case 'delete':
				check_admin_referer('delete');
				$action_type = 'delete';
				break;
			case 'add_course':
				$action_type = 'add_course';
				break;
			case -1: // Bulk action
				if (isset($_GET['action2'])) {
					
					switch ($_GET['action2']) {
						case 'delete':
							$action_type = 'delete_all';
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
			switch($action_type) {
				case 'delete':
				case 'delete_all':
				case 'edit_w_delete':
				//mix vars
				if ($action_type == 'delete' or $action_type == 'delete_all') {
					$conf_action = 'delete';
				} else {
					$conf_action = 'edit_w_delete';
				}
				?>
                	<div id="course-confirm-delete" style="max-width: 460px; width: auto; margin: 0 auto; padding: 10px;">
                    	<div id="postdiv" class="postarea">
                        <div class="postbox" style="margin: 0 auto; padding: 10px; text-align:center;">
                        <p><?php echo __('Are You sure you want to delete selected item(s)?', 'mls_lexicon'); ?><p>
               
                        <p>
                        <form id="courses_form" name="post" action="" method="post">
                        <?php wp_nonce_field($conf_action, 'action_nonce', false, true); ?>
                <input name="conf_delete" id="button_conf_delete" type="submit" class="button button-hero button-primary" value="<?php echo __('Yes, Delete', 'mls_lexicon'); ?>">
                <p><a href="<?php echo "?page=".$_REQUEST['page'];
				if(isset($_GET['edit_action'])) {
				echo "&action=edit&course_id=".$_GET['course_id'];	
				}
				
				?>           
                " class="add-new-h2"><?php echo __('No, Go Back.', 'mls_lexicon'); ?></a></p>
                </form>
              			</p>
                        </div>
                        </div>
                    </div>
                
                
                <?php
				
				
				break;
				case 'edit': //Prepare word list
				case 'add_course':
				$cid = $_GET['course_id'];
				$course_data = get_courseById($cid);
				echo "<h3><i>Editing ".$course_data->lang_1."-".$course_data->lang_2."-".$course_data->level."</i></h3>";
		class Course_Words_Table extends Mls_Lexicon_List_Table {
		function __construct() {
			global $status, $page;
			$table_array = array();
			
			parent::__construct('word_code', 'word_code', false); // Singular records label, plural, Ajax support
		}
		function column_default($item, $column_name) {
			switch ($column_name) {
				case 'w_code':
				case 'w_cat':
				case 'base_lang':
				case 'tar_lang':
					return $item[$column_name];

				default:
					return print_r($item, true); //Show the whole array for troubleshooting purposes
			}
		}
		function column_w_code($item) {
			$delete_nonce = wp_create_nonce('delete_word');
			$actions        = array(
			'delete' => sprintf('<a href="?page=%s&action=%s&course_id=%d&edit_action=%s&w_code=%s&_wpnonce=%s">Delete</a>', $_REQUEST['page'], 'edit', $_GET['course_id'], 'w_delete', $item['w_code'], $delete_nonce)
				
			);
			return sprintf('%1$s %2$s', $item['w_code'], $this->row_actions($actions));
		}
		function column_cb($item, $x = NULL) { // Null to supress E_STRICT warning
			return parent::column_cb($item, 'c_id');
		}
		function get_columns($x = NULL) { // Checkbox provided by parent, Null to supress E_STRICT warning
			$columns = array(
				'w_code' => __('Word Code', 'mls_lexicon'),
				'w_cat' => __('Word Category', 'mls_lexicon'),
				'base_lang' => __('Base Language', 'mls_lexicon'),
				'tar_lang' => __('Target Language', 'mls_lexicon')
			);
			return parent::get_columns($columns);
		}
		function get_sortable_columns($x = NULL) { // Null to supress E_STRICT warning
			return parent::get_sortable_columns(array(
				'w_code', 'w_cat', 'base_lang', 'tar_lang'
			));
		}
		
		function usort_reorder($a, $b) {
			return parent::usort_reorder($a, $b);
		}
		function prepare_items($x = NULL, $y = NULL, $z = NULL, $q = NULL, $u = NULL) {
			global $wpdb;
			$cid = $_GET['course_id'];
			$course_data = get_courseById($cid);
			$sql = $wpdb->prepare("SELECT c.code as 'w_code', cat_tr.name as 'w_cat', w_b.text as 'base_tr', w_t.text as 'tar_tr' FROM ".$wpdb->prefix."mls_lexicon_course_codes AS c LEFT 

JOIN ".$wpdb->prefix."mls_lexicon_categories_trans AS cat_tr ON cat_id = (SELECT id FROM ".$wpdb->prefix."mls_lexicon_categories 

WHERE CONCAT(notion_type, class, subclass, mgroup, subgroup) = c.category_code) AND lang='%s'
LEFT JOIN ".$wpdb->prefix."mls_lexicon_words AS w_b ON w_b.code = c.code AND w_b.lang_mod_id = (SELECT id FROM 

".$wpdb->prefix."mls_lexicon_lang_mod WHERE lang='%s' and level='A1')
LEFT JOIN ".$wpdb->prefix."mls_lexicon_words AS w_t ON w_t.code = c.code AND w_t.lang_mod_id = (SELECT id FROM 

".$wpdb->prefix."mls_lexicon_lang_mod WHERE lang='%s' and level='A1')
WHERE c.course_id = %d;", $course_data->lang_1, $course_data->lang_1, $course_data->lang_2, $cid);
			$res   = parent::prepare_items($sql, 1, true);
			$count = 1;
			$table_array = array();
			foreach ($res as $row) {
				foreach ($row as $field => $value) {
					if ($field == 'w_code')
						$w_code = $value;
					elseif ($field == 'w_cat') {
						$w_cat = $value;
					} elseif ($field == 'base_tr') {
						$base_tr = $value;
					}elseif ($field == 'tar_tr') {
						$tar_tr = $value;
					}
				}
				$table_array[] = array(
					'ID' => $count,
					'w_code' => $w_code,
					'w_cat' => $w_cat,
					'base_lang' => $base_tr,
					'tar_lang' => $tar_tr,
					'c_id' => $w_code
				);
				$count++;
			}
			parent::prepare_items(NULL, 2, NULL, $this, $table_array);
		}
	}
	$Course_Words_Table = new Course_Words_Table();
	$Course_Words_Table->prepare_items();
	$Course_Words_Table->display($Course_Words_Table, array( array('', 'Go Back'), array('edit&course_id='.$cid.'&edit_action=add_new_word', 'Add Word')));
				
				?> 
				
				<?php
				break;
				case 'edit_add_new_word':  // Word Adding table
				//data
				$c_id = $_GET['course_id'];
				$c_data = get_courseById($c_id);
				?>
                <div class="notice notice-warning">
          <p> <i> <?php echo __('Warning', 'mls_lexicon').': '.__("Only words that exist in both of course's language modules will be displayed.", 'mls_lexicon'); ?> </i></p>
        </div>
        <h2><i><?php echo __('Course Edition', 'mls_lexicon').' : '.__('Words addition', 'mls_lexicon'); ?></i></h2>
					<div id="edit_word_add" style="width: auto; margin: 0 auto; padding: 10px;">
                    	<div id="postdiv" class="postarea">
                        <div class="postbox" style="margin: 0 auto; padding: 10px; text-align: left;">
                        <p><?php echo __('First, select base language module.', 'mls_lexicon'); ?></p>
               			
                        <p>
                        <form id="w_add_mod_select" name="post" action="" method="post">
                      <select id="w_add_mod_select_select">
                      <option value="0" disabled <?php if(!isset($_GET['edit_sel_mod'])) echo 'selected'; ?>><?php echo __('Select module', 'mls_lexicon'); ?></option>
                      <option value="<?php echo $c_data->lang_1; ?>"><?php echo $c_data->lang_1; ?>
                      </option>
                      <option value="<?php echo $c_data->lang_2; ?>"><?php echo $c_data->lang_2; ?>
                      </option>
                      </select>
            
                </form>
              			</p>
                        </div>
                        </div>
                    </div>
				<?php
				// word add selection table
				class Course_Words_Add_Table extends Mls_Lexicon_List_Table {
		function __construct() {
			global $status, $page;
			$table_array = array();
			
			parent::__construct('word_code_add', 'word_code_add', false); // Singular records label, plural, Ajax support
		}
		function column_default($item, $column_name) {
			switch ($column_name) {
				case 'w_code':
				case 'w_cat':
				case 'w_text':
				case 'w_phrase':
					return $item[$column_name];

				default:
					return print_r($item, true); //Show the whole array for troubleshooting purposes
			}
		}
		function column_w_code($item) {
			$actions        = array(
			'add' => sprintf('<a class="w_add_word_click" href="%s">Add this word</a>', $item['w_code'])
				
			);
			return sprintf('%1$s %2$s', $item['w_code'], $this->row_actions($actions));
		}
		function column_cb($item, $x = NULL) { // Null to supress E_STRICT warning
			return parent::column_cb($item, 'c_id');
		}
		function get_columns($x = NULL) { // Checkbox provided by parent, Null to supress E_STRICT warning
			$columns = array(
				'w_code' => __('Word Code', 'mls_lexicon'),
				'w_cat' => __('Word Category', 'mls_lexicon'),
				'w_text' => __('Word Text', 'mls_lexicon'),
				'w_phrase' => __('Word Phrase', 'mls_lexicon')
			);
			return parent::get_columns($columns);
		}
		function get_sortable_columns($x = NULL) { // Null to supress E_STRICT warning
			return parent::get_sortable_columns(array(
				'w_code', 'w_cat', 'w_text', 'w_phrase'
			));
		}
		
		function usort_reorder($a, $b) {
			return parent::usort_reorder($a, $b);
		}
		function prepare_items($x = NULL, $y = NULL, $z = NULL, $q = NULL, $u = NULL) {
			global $wpdb;
			$cid = $_GET['course_id'];
			$course_data = get_courseById($cid);
			$sel_mod = $_GET['edit_sel_mod'];
			if($course_data->lang_1 == $sel_mod) {
				$other_mod = $course_data->lang_2;
			} else {
				$other_mod = $course_data->lang_1;
			}
			$sql = $wpdb->prepare("select w.code, w.text, w.phrase, ct.name from ".$wpdb->prefix."mls_lexicon_words as w
LEFT JOIN ".$wpdb->prefix."mls_lexicon_categories_trans as ct ON lang='%s' AND cat_id = (SELECT cat.id from
".$wpdb->prefix."mls_lexicon_categories as cat WHERE CONCAT(cat.notion_type, cat.class, cat.subclass, cat.mgroup, cat.subgroup) = (SELECT CONCAT(notion_type, class, subclass, mgroup, subgroup) from ".$wpdb->prefix."mls_lexicon_codes WHERE code = w.code))
WHERE w.lang_mod_id = (select mod1.id from ".$wpdb->prefix."mls_lexicon_lang_mod as mod1 where lang='%s' and 
level='%s') AND EXISTS(select w2.code from ".$wpdb->prefix."mls_lexicon_words as w2 WHERE w2.code = w.code AND 
w2.lang_mod_id = (select mod2.id from ".$wpdb->prefix."mls_lexicon_lang_mod as mod2 where lang='%s' and level='%s')) AND NOT EXISTS (select inx.code from ".$wpdb->prefix."mls_lexicon_course_codes as inx WHERE inx.course_id = %d AND inx.code = w.code)", 
$sel_mod, $sel_mod, $course_data->level, $other_mod,$course_data->level, $cid);
			$res   = parent::prepare_items($sql, 1, true);
			$count = 1;
			$table_array = array();
			foreach ($res as $row) {
				foreach ($row as $field => $value) {
					if ($field == 'code')
						$w_code = $value;
					elseif ($field == 'name') {
						$w_cat = $value;
					} elseif ($field == 'text') {
						$w_text = $value;
					}elseif ($field == 'phrase') {
						$w_phrase = $value;
					}
				}
				$table_array[] = array(
					'ID' => $count,
					'w_code' => $w_code,
					'w_cat' => $w_cat,
					'w_text' => $w_text,
					'w_phrase' => $w_phrase,
					'c_id' => $w_code
				);
				$count++;
			}
			parent::prepare_items(NULL, 2, NULL, $this, $table_array);
		}
	}
	if(isset($_GET['edit_sel_mod'])) {
		if (strlen($_GET['edit_sel_mod']) == 3) {
	$Course_Words_Add_Table = new Course_Words_Add_Table();
	$Course_Words_Add_Table->prepare_items();
	$Course_Words_Add_Table->display($Course_Words_Add_Table, array('', 'Go Back'));
		}
	}
				
				break;
				default:
				break;
			}
			?>
            </div>
            	<div style="position: fixed;  top: 50%;  left: 50%;  transform: translate(-50%, -50%);">
  				<div id="message" class="notice inline" style="text-align:center; display: none;"><p style="font-size: 1.2em; line-height: 2.1em;"><i></i></p></div>
  				<div class="spinner"></div>
  			</div>
            <script> // Ajax handler
			jQuery(document).ready(function(e) {
				// add word button
				jQuery(".w_add_word_click").click(function(e) {
                    e.preventDefault();
					alert(e.attr('href'));
					
                });
				
				
				// word add select form
				jQuery("#w_add_mod_select_select").change(function(e) {
                    jQuery("#w_add_mod_select").trigger("submit");
                });
				
				jQuery("#w_add_mod_select").submit(function(e) {
                    e.preventDefault();
					var url = window.location.href;
					url += '&edit_sel_mod='+jQuery("#w_add_mod_select_select").val();
					window.location = url;
                });
				// main form submits
				jQuery("#courses_form").submit(function(e) {
					e.preventDefault();
					jQuery("#form_submit").attr('disabled', 'disabled');
					jQuery(".spinner").show();
					// Variables
					var loc = 'admin_courses';
					var act = '<?php echo $action_type; ?>';
					var nonce = jQuery("#action_nonce").val();
					var course_ids = [];
					var word_code = [];
					<?php
					if(is_array($_GET['course_id'])) {
					foreach($_GET['course_id'] as $cid){
						echo 'course_ids.push('.$cid.');';
					}
					} else {
						echo 'course_ids.push('.$_GET['course_id'].');';
					}
					if(isset($_GET['w_code'])) {
						echo 'word_code.push("'.$_GET['w_code'].'");';
					}
					?>
					var data = {
						action: 'admin_submits',
						loc: loc,
						act: act,
						cids: course_ids,
						w_code: word_code,
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
								window.location = '?page=mls_lexicon_admin_courses';
							});
						}
					});
				});
			});

</script>
            


<?php
		


		}
	} else {
		$course_Table->display($course_Table, array('add_course', 'Add Course'));
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