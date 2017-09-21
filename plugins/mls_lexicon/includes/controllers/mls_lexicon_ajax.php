<?php

/*

  Funciones Ajax
  
*/
require_once(mls_lexicon_DIR.'/includes/controllers/mls_lexicon_functions.php');
function mls_lexicon_my_action_callback()
{
  global $wpdb;
  $tablaprefix = "{$wpdb->prefix}mls_lexicon_course";
  $value = $_POST['func'];
  $arr = array();

  switch($value)
  {
    case 1:
  	  $cTarget = $_POST['cTarget'];
      $res = $wpdb->get_results("SELECT lang_2 
        FROM ".$tablaprefix." WHERE lang_1 = '".$cTarget."'");
    
      foreach ($res as $row)
        $arr[] = "<option value=".$row->lang_2.">".$row->lang_2."</option>";
    break;
    
    case 2:
      $cBase = $_POST['cBase'];
      $cTarget = $_POST['cTarget']; 

      $res = $wpdb->get_results("SELECT level FROM ".$tablaprefix." WHERE lang_1 = '".$cTarget."'AND lang_2 = '".$cBase."'");

      foreach ($res as $row)
        $arr[] = "<option value=".$row->level.">".$row->level."</option>";
    break;

    case 3:
      $cBase = $_POST['cBase'];
      $cTarget = $_POST['cTarget'];
      $cLevel = $_POST['cLevel'];
      $curso = $cBase. ' - '.$cTarget.' - '.$cLevel;
      //$res = $wpdb->get_results("SELECT id FROM wp_m_subscriptions WHERE sub_name ='".$curso."'");
    
      //$curso_id = get_curso($cursBase,$cursDest,$curslvl)[0]->ID;
      $course_id = get_course($cTarget,$cBase,$cLevel);
      $course_id = $course_id[0]->id;
      $user_id = wp_get_current_user()->ID;
      $result = set_course_user($user_id, $course_id);

?>
<?php
      if(!$result)
        $arr[] = 0;
      if($result)
      {
        $arr[] = 1;
      }
    break;
  }
	if(ob_get_contents()) {
  ob_clean();
	}
 echo json_encode($arr);
  die;
}
//TODO Check or max_post_size
function mls_lexicon_admin_data() {
	$error = false; // If true means there was en error
	$error_msg = 'Unidentified Error';
		$result = [];
	$result['type'] = '';
	$result['msg'] = '';
	$result['zip'] = '';
	if(!isset($_POST['act'])) {
		$error = true;
		$error_msg = __('No action specified', 'mls_lexicon');
		
	} else {
	global $wpdb;
	if($_POST['act'] === 'admin_data_import') {	
		$files = array();
		$succ_msg = __('Data import completed.', 'mls_lexicon');
	
	if(check_ajax_referer( 'admin_data', 'nonce', false)) { // Nonce verified
	if (class_exists('finfo')) {
		if(isset($_FILES)) { // Files recieved
			$uploaddir = mls_lexicon_maketemp();
			if($uploaddir) {  // Temp dir created
			foreach($_FILES as $file)
   				 {
					 	$filesize = $file['size'];
						$maxsizes = file_upload_max_size();
						$maxes = array(false, false, false);
						if($maxsizes[0] < $filesize) { $maxes[0] = true; }
						if($maxsizes[1] < $filesize) { $maxes[1] = true; }
						if($maxsizes[2] < $filesize) { $maxes[2] = true; }
						if($maxes[0] || $filesize == 0 || $maxes[1] || $maxes[2]) {
						$error = true;
						$error_msg = $file['name'].': '.__('File is too big to upload. Change the following in php.ini:', 'mls_lexicon');
						if($maxes[0]) {
							$error_msg .="<br>"."upload_max_filesize";
						}
						if($maxes[1]) {
							$error_msg .="<br>"."post_max_size";
						}
						if($maxes[2]) {
							$error_msg .="<br>"."memory_limit";
						}

						break;
						}
        			if(move_uploaded_file($file['tmp_name'], $uploaddir.DIRECTORY_SEPARATOR.basename($file['name'])))
       				{
            			$files[] = $uploaddir.DIRECTORY_SEPARATOR.$file['name'];
						

        			} else if(!$error)	{
           				 $error = true;
						 $error_msg = __('Could not upload files', 'mls_lexicon');
        			}
				 }
				 //Files in temp dir
				 if(!$error) { // No error uploading, start import
				 		
				 		if(mls_lexicon_load_files($uploaddir)) { // Files imported
							
							
						} else {
							$error=true;
							$error_msg = __('Error importing files.', 'mls_lexicon');
						}
				 }
			} else {
				$error=true;
				$error_msg = __('Could not create temp folder. Check server write permissions.', 'mls_lexicon');
			}
			mls_lexicon_delete_dir($uploaddir);  //clear temp;
		} else {
			$error = true;
		$error_msg = __('Files were not sent.', 'mls_lexicon');
		}
	} else { // fileinfo not enabled
	$error = true;
		$error_msg = __('Fileinfo module not enabled. Check your PHP settings', 'mls_lexicon');
	}
	} else { // Couln't verify nonce
		$error = true;
		$error_msg = __('Security error. Try re-logging.', 'mls_lexicon');
	}
	
	} else if ($_POST['act'] === 'admin_data_export') {
			if(check_ajax_referer( 'admin_data', 'nonce', false)) {
				$succ_msg = __('Data export completed.', 'mls_lexicon');
				$category_codes = array(); // Codes and images
				$category_translations = array();
				$word_codes = array(); // Codes and images K->V
				$modules = array();
				$courses = array();
				$student_data = array();
				$zip = new ZipArchive();
				$temp_dir = mls_lexicon_maketemp();
				$file = $temp_dir.DIRECTORY_SEPARATOR.'MLS_Lexicon_Export_'.date('Y-m-d_H-i-s').'.zip';
				if ($zip -> open($file, ZIPARCHIVE::CREATE) === TRUE) {
					
						
						
							if(isset($_POST['ex_cat_code'])) { // export codes
								$file_cont = 'CAT_CODES;';
								$cat_codes = $wpdb->get_results("SELECT * from {$wpdb->prefix}mls_lexicon_categories");
								
								foreach($cat_codes as $cat_code) {
								$file_cont .= "\n".$cat_code->notion_type.';'.$cat_code->class.';'.$cat_code->subclass.';'.$cat_code->mgroup.';'.$cat_code->subgroup;
								}
								
								$zip->addFromString('Categories/cat_codes.csv', $file_cont);
							}
							if (isset($_POST['ex_cat_img'])) {
								
									
										
											$ex_cat_imgs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mls_lexicon_categories_img");
											foreach($ex_cat_imgs as $ex_c_img) {
												$ex_cat_code = $wpdb->get_var("SELECT CONCAT(notion_type, class, subclass, mgroup, subgroup) from {$wpdb->prefix}mls_lexicon_categories WHERE id={$ex_c_img->id}");
												switch($ex_c_img->mimetype) {
													case 'image/svg+xml':
														$filetype = 'svg';
														break;
													case 'image/jpeg':
														$filetype = 'jpg';
														break;
													case 'image/gif':
														$filetype = 'gif';
														break;
													case 'image/png':
														$filetype = 'png';
														break;
													default:
														$error = true;
														$error_msg = __('Could not determine filetype while exporting images', 'mls_lexicon');
														break;
												}
												if(!$error) {
												$ex_cat_image = $ex_c_img->image;
														if(substr($ex_cat_image, 0, 2) == '0x') {
															$ex_cat_image = hex2bin(substr($ex_c_img->image, 2));
														}
												$zip->addFromString('Categories/Images/C'.$ex_cat_code.'.'.$filetype, $ex_cat_image);
												
												}
												
											}
								
							}
						
								
									if(isset($_POST['ex_cat_trans_lang'])) {
										foreach($_POST['ex_cat_trans_lang'] as $cat_tr_lang) {
										$cat_trans = $wpdb->get_results("SELECT tr.lang, tr.name, c.notion_type, c.class, c.subclass, c.mgroup, c.subgroup FROM {$wpdb->prefix}mls_lexicon_categories_trans tr LEFT JOIN {$wpdb->prefix}mls_lexicon_categories c ON tr.cat_id = c.id WHERE tr.lang='{$cat_tr_lang}'");
										$file_cont = 'CATEGORY;'.$cat_tr_lang;
											foreach($cat_trans as $cat_tran) {
												$file_cont .= "\n".$cat_tran->notion_type.';'.$cat_tran->class.';'.$cat_tran->subclass.';'.$cat_tran->mgroup.';'.$cat_tran->subgroup.';'.$cat_tran->name;
											}
										$zip->addFromString('Categories/Translations/cat_trans_'.$cat_tr_lang.'.csv', $file_cont);
										
										}
									}
								
							
					
							
								
									if(isset($_POST['ex_codes_word'])) {
									$ex_word_codes = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mls_lexicon_codes");
									$file_cont = 'CODES;';
									foreach($ex_word_codes as $ex_word_code) {
									  $file_cont .= "\n".$ex_word_code->code.';'.$ex_word_code->notion_type.';'.$ex_word_code->class.';'.$ex_word_code->subclass.';'.$ex_word_code->mgroup.';'.$ex_word_code->subgroup.';'.$ex_word_code->unit.';'.$ex_word_code->theme;
										}
									$zip->addFromString('Codes/word_codes.csv', $file_cont);
									}
									if (isset($_POST['ex_codes_img'])) {
										
											$ex_code_imgs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mls_lexicon_codes_img");
											foreach($ex_code_imgs as $ex_c_img) {
												switch($ex_c_img->mimetype) {
													case 'image/svg+xml':
														$filetype = 'svg';
														break;
													case 'image/jpeg':
														$filetype = 'jpg';
														break;
													case 'image/gif':
														$filetype = 'gif';
														break;
													case 'image/png':
														$filetype = 'png';
														break;
													default:
														$error = true;
														$error_msg = __('Could not determine filetype while exporting images', 'mls_lexicon');
														break;
												}
												if(!$error) {
												$ex_cat_image = $ex_c_img->image;
														if(substr($ex_cat_image, 0, 2) == '0x') {
															$ex_cat_image = hex2bin(substr($ex_c_img->image, 2));
														}
												$zip->addFromString('Codes/Images/W'.$ex_c_img->code.'.'.$filetype, $ex_cat_image);
												
												}
												
											}
										}
								
							
									if(isset($_POST['ex_mods'])) {
										foreach($_POST['ex_mods'] as $ex_mod) {
											$mod_data = $wpdb->get_results("SELECT lang, level FROM {$wpdb->prefix}mls_lexicon_lang_mod WHERE id={$ex_mod}");
											$file_cont = 'MODULE;'.$mod_data[0]->lang.';'.$mod_data[0]->level;
											$mod_codes = $wpdb->get_results("SELECT * from {$wpdb->prefix}mls_lexicon_words WHERE lang_mod_id={$ex_mod}");
											foreach($mod_codes as $mod_code) {
											$file_cont .= "\n".$mod_code->code.';'.$mod_code->must_learn.';'.$mod_code->text.';'.$mod_code->phrase;
											}
											$zip->addFromString('Modules/mod_'.$mod_data[0]->lang.'_'.$mod_data[0]->level.'.csv', $file_cont);
										}
									}
								
							
							
						
									if(isset($_POST['ex_courses'])) {
										foreach($_POST['ex_courses'] as $ex_course) {
										$ex_course_data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mls_lexicon_course WHERE id={$ex_course}");
										$file_cont = "COURSE;".$ex_course_data[0]->lang_1.';'.$ex_course_data[0]->lang_2.';'.$ex_course_data[0]->level.';'.$ex_course_data[0]->description;
										$ex_course_codes = $wpdb->get_results("SELECT code, category_code FROM {$wpdb->prefix}mls_lexicon_course_codes WHERE course_id={$ex_course_data[0]->id}");
										foreach($ex_course_codes as $ex_course_code) {
											$file_cont .= "\n".$ex_course_code->code.';'.$ex_course_code->category_code;
										
										}
										$zip->addFromString('Courses/course_'.$ex_course_data[0]->lang_1.'_'.$ex_course_data[0]->lang_2.'_'.$ex_course_data[0]->level.'.csv', $file_cont);
										
										}
									
									}
								
							
							
							if(isset($_POST['ex_studs'])) {
								
									$ex_stud_courses = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mls_lexicon_course_student");
									$file_cont = "STUDENT_COURSE;";
									foreach($ex_stud_courses as $ex_stud_course) {
										$file_cont .= "\n".$ex_stud_course->student_id.';'.$ex_stud_course->course_id.';'.$ex_stud_course->state;
									}
									$zip->addFromString('Student Data/stud_courses.csv', $file_cont);
									$ex_stud_cards = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mls_lexicon_course_student_card");
									$file_cont = "STUDENT_CARD;";
									foreach($ex_stud_cards as $ex_stud_card) {
										$file_cont .= "\n".$ex_stud_card->student_id.';'.$ex_stud_card->course_id.';'.$ex_stud_card->code.';'.$ex_stud_card->prog_level;
									}
									$zip->addFromString('Student Data/stud_cards.csv', $file_cont);
								
							
							}
							$file_cont = '';
			    		
			
					
				} else { //zip error 
					$error = true;
        				switch($zip){
						case ZipArchive::ER_EXISTS: 
							$error_msg = __('Zip Error', 'mls_lexicon').' - '.__('File already exists.', 'mls_lexicon');
         		      		break;
						case ZipArchive::ER_INCONS: 
	          			   	$error_msg = __('Zip Error', 'mls_lexicon').' - '.__('Archive inconsistent.', 'mls_lexicon');
							break;        
						case ZipArchive::ER_MEMORY: 
			                $error_msg = __('Zip Error', 'mls_lexicon').' - '.__('Malloc failure.', 'mls_lexicon');
            			    break;
			            case ZipArchive::ER_NOENT: 
            		    $error_msg = __('Zip Error', 'mls_lexicon').' - '.__('No such file.', 'mls_lexicon');
                		break;
			           	case ZipArchive::ER_NOZIP: 
            	    	$error_msg = __('Zip Error', 'mls_lexicon').' - '.__('Not a zip archive.', 'mls_lexicon');
                		break;
            			case ZipArchive::ER_OPEN: 
               			$error_msg = __('Zip Error', 'mls_lexicon').' - '.__('Cannot open file.', 'mls_lexicon');
                		break;
            			case ZipArchive::ER_READ: 
                		$error_msg = __('Zip Error', 'mls_lexicon').' - '.__('Read error.', 'mls_lexicon');
                		break;
            			case ZipArchive::ER_SEEK: 
                		$error_msg = __('Zip Error', 'mls_lexicon').' - '.__('Seek error.', 'mls_lexicon');
                		break;
            			default: 
                		$error_msg = __('Zip Error', 'mls_lexicon').' - '.__('Unknow Error.', 'mls_lexicon');
                		break;
        			}
				}
				$zip->close();
				$result['zip'] = $file;
				
			} else { // Couln't verify nonce
		$error = true;
		$error_msg = __('Security error. Try re-logging.', 'mls_lexicon');
	}
	}
	
	
	if(!$error) {  // Successfull
					$result['type'] = 'success';
					$result['msg'] =  $succ_msg;
													} else {
									$result['type'] = 'fail';
									$result['msg'] = $error_msg;
								}
	
	
	$result = json_encode($result);
	echo $result;
	die();
	}
}

function mls_lexicon_admin_submits() {
	write_log($_POST);
	global $wpdb;

	$sql = array();
	$test_sql = array();
	$result = [];
	$result['type'] = '';
	$result['msg'] = '';
	switch($_POST['loc']){ // Check location
	
		case 'admin_students':
					$base_elem = $_POST['base_elem'];
					$target_elem = $_POST['target_elem'];
				switch($_POST['act']) { //Check action
					case 'enroll':
						check_ajax_referer( 'enroll', 'nonce'); // Will die on fail
						$table = $wpdb->prefix.'mls_lexicon_course_student';
						$error = false; // If true means there was en error
						$error_msg = '';
								foreach ($base_elem as $be => $key) {
									foreach($target_elem as $te => $t_key) {
										$check_c = check_course($key, $t_key);
										if(!empty($check_c)) {
										} else {
											if(!set_course_user($key, $t_key)) {
												$error = true;
												$error_msg = $wpdb->print_error();
											}
										}
									}
								}
								
								if(!$error) {
									$result['type'] = 'success';
									$result['msg'] = 'Enrollment successful.';
								} else {
								
									$result['type'] = 'fail';
									$result['msg'] = $error_msg;
								}
						break;
					case 'withdraw':
						check_ajax_referer( 'withdraw', 'nonce');
						$table = $wpdb->prefix.'mls_lexicon_course_student';
						$error = false; // If true means there was en error
						$error_msg = '';
							foreach ($base_elem as $be => $key) {
								foreach($target_elem as $te => $t_key) {
									$check_c = check_course($key, $t_key);
										if(empty($check_c)) {
									} else {
										if(!del_course_user($key, $t_key)) {
											$error = true;
											$error_msg = $wpdb->print_error();
										}
									}
								}
							}

							if(!$error) {
								$result['type'] = 'success';
								$result['msg'] = 'Withdrawal successful.';
							} else {
								$result['type'] = 'fail';
								$result['msg'] = $error_msg;
								}
						break;
					default:
						$result['type'] = 'error';
						$result['msg'] = 'Incorrect action!';
						break;
				}
			break;
		case 'admin_courses':  // Admin Courses Ajax
			// vars
			$cids = $_POST['cids'];
			$w_code = $_POST['w_code'];
			switch($_POST['act']) {
				case 'delete':					
				case 'delete_all':
				check_ajax_referer( 'delete', 'nonce'); // Will die on fail
					if(!delete_course($cids)) { //failed to delte courses
						$result['type'] = 'error';
						$result['msg'] = 'Incorrect action!';
						break;
					} else {
						$result['type'] = 'success';
						$result['msg'] = 'Course(s) Deleted.';
					}
					
				
				break;
				case 'edit_w_delete':
				check_ajax_referer( 'edit_w_delete', 'nonce');
					if(!delete_course_word($cids, $w_code)) { //failed to delete course word
						$result['type'] = 'error';
						$result['msg'] = 'Incorrect action!';
						break;
					} else {
						$result['type'] = 'success';
						$result['msg'] = 'Word Deleted.';
					}
				break;
				default:
				$result['type'] = 'error';
				$result['msg'] = 'Incorrect action!';
				break;
			}
		break;
		default:
			$result['type'] = 'error';
			$result['msg'] = 'Incorrect Location!';
			break;
		
	}
	$result = json_encode($result);
	echo $result;
	die();
}