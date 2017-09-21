<?php

if (!function_exists('write_log')) {
    function write_log ( $log )  {
        
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
       
    }
}

///////////////////////////////////////////////////////////////////////////////////
//  CSV to MySQL database.
///////////////////////////////////////////////////////////////////////////////////

		/*
		 *	Find absolute path to all files in a given path
		 *  @string $var path
		 *	@return array object
		 */
function mls_get_files_paths($var) {
	$files_paths = array();
	if(is_dir($var)) {  // Check if given path is a directory
	$dir=opendir($var); // If so, open it
	chdir($var);
	while($archive = readdir($dir)) { // Check directory contents, ignore dotted links
		if($archive != '.' && $archive != '..'){
			$path = $var.DIRECTORY_SEPARATOR.$archive;
			$path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
			if(is_dir($archive)) { // If found child folder, call recursively with new path, merge results with current
				$files_paths = array_merge($files_paths, mls_get_files_paths($path));
			}
			if(is_file($archive)) { // If a file is found, add it to array
				$files_paths[] = $path;
			}
		}
	}
	chdir("../"); // Go back to starting point
	closedir($dir);	// Close dir
	} elseif(is_file($var)) {
		$files_paths[] = $var; // Given path is a file
	} else { // Given path is neither file or a folder, error
		return false;
	}
	return $files_paths;
	}
	
	
	
function mls_lexicon_load_files($var) { //array of file paths

if(is_array($var)) { //Check if it is a tmp file
			
			$paths[] = $var;
		} else {
	$paths = mls_get_files_paths($var);
		}
	$csv_paths= array(); 
	$zip_paths= array();
	$img_paths= array(); 
	if(!empty($paths)) { 
	foreach($paths as $path) {
		if($path !== '') {
			if(is_array($path)) {
		$finfo = pathinfo($path['name']);
		$path = $path['tmp_name'];
		} else {
		$finfo = pathinfo($path);
		}
		$ftype = strtolower($finfo['extension']);
		$fname = strtolower($finfo['filename']);
		switch($ftype) {
			case 'csv':
				$csv_paths[] = $path;
				break;
			case 'zip':
				$zip_paths[] = $path;
				break;
			case 'svg':
			case 'jpg':
			case 'gif':
			case 'png':
				$img_paths[] = array($path, $fname);
				break;
			default:
				break;	
		}
		}
		
	}
	}
	
	if(!empty($csv_paths)) { 
	usort($csv_paths, 'mls_lexicon_compare_paths');
	if(mls_lexicon_load_csv($csv_paths)) {} 
	
	}
	if(!empty($zip_paths)) { 
	usort($zip_paths, 'mls_lexicon_compare_paths');
	if(mls_lexicon_unzip($zip_paths)){} 
	}
	if(!empty($img_paths)) {
		 if(mls_lexicon_process_img($img_paths)){} }
	
	return true;
}
function mls_lexicon_compare_paths($p1, $p2) {
	$res = 0;
	$pp1 = strtolower($p1);
	$pp2 = strtolower($p2);
	$p1dirs = explode(DIRECTORY_SEPARATOR, $pp1);
	$p2dirs = explode(DIRECTORY_SEPARATOR, $pp2);
	// First check if they are in same directory - the higher in hierarchy, the higher the value
	if(sizeof($p1dirs) == sizeof($p2dirs)) {
		// They are in same folder, order aphabetically
		$res = strcasecmp($pp1 ,$pp2);
	} else {
		
		$res = (sizeof($p1dirs) > sizeof($p2dirs)) ? +1 : -1;	
	}
	
	return $res;
}
function mls_lexicon_maketemp() {
	$rand_fn = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8);
	$ini_val = ini_get('upload_tmp_dir');
$temp_path = $ini_val ? $ini_val.DIRECTORY_SEPARATOR.$rand_fn : sys_get_temp_dir().DIRECTORY_SEPARATOR.$rand_fn;
//$temp_path = $ini_val ? $ini_val.DIRECTORY_SEPARATOR.$rand_fn : $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."tmp".DIRECTORY_SEPARATOR.$rand_fn;  //Requires manual permissions, but could work on some servers
	if(!is_dir($temp_path)) { 
	mkdir($temp_path, 0755, true);
	 }
	return $temp_path;	
}
function mls_lexicon_unzip($paths) {
	if(!empty($paths)) {
	foreach($paths as $path) {
	$temp_path = mls_lexicon_maketemp();
	$zip = new ZipArchive;
		$entries = array();
	    if ( $zip->open( $path) )
	    {
			for ( $i=0; $i < $zip->numFiles; $i++ )
	        {
	            $entry = $zip->getNameIndex($i);
	            if ( substr( $entry, -1 ) != '/' ) {  // skip directories     
	            if ( strpos($entry,'/._') === false ) { // skip mac files
				$zip->extractTo($temp_path, $entry);
				
				}
				}
	            
	        }
			$zip->close();
			if(mls_lexicon_load_files($temp_path)){
				//mls_lexicon_delete_dir($temp_path);
			}
	    } 
	}
}
return true;
}
function mls_lexicon_process_img($vars) {
	global $wpdb;
	
	foreach($vars as $var) {
		$path = $var[0];
		$name = $var[1];
		$finfo = new finfo(FILEINFO_MIME); 
		$svg_type = substr($name, 0, 1);
	$code = substr($name, 1);
	$imgtype = explode(";", $finfo->file($path));
	$imgtype = $imgtype[0];
	

	
	/*$image = substr($image, 2);
	$image = hex2bin($image);*/

	
					switch(strtolower($svg_type)) {
						case 'w':
							if($wpdb->get_var("SELECT * from `{$wpdb->prefix}mls_lexicon_codes` WHERE `code`='{$code}'")) {
							$sql = $wpdb->prepare("INSERT INTO `{$wpdb->prefix}mls_lexicon_codes_img` (`code`, `image`, `mimetype`) values (%s, LOAD_FILE(%s), %s) ON DUPLICATE KEY UPDATE `image`=LOAD_FILE(%s), `mimetype`=%s", $code, $path, $imgtype, $path, $imgtype);
							$wpdb->query($sql);
						}	
						// Check if file loaded; 
						if($wpdb->get_results("SELECT code FROM `{$wpdb->prefix}mls_lexicon_codes_img` WHERE `code`='{$code}' AND `image` IS NULL")){
							// LOAD_FILE failed, try something else
							$image = file_get_contents($path);
							$image = '0x'.bin2hex($image);
							$sql = $wpdb->prepare("UPDATE  `{$wpdb->prefix}mls_lexicon_codes_img` SET `image`={$image} WHERE `code`='%s'", $code);
							$wpdb->query($sql);
						}
							break;
						case 'c':
							$cat = $wpdb->get_var("SELECT id, CONCAT(notion_type, class, subclass, mgroup, subgroup) AS code from `{$wpdb->prefix}mls_lexicon_categories` WHERE CONCAT(notion_type, class, subclass, mgroup, subgroup)={$code}");
							if($cat) {
							$sql = $wpdb->prepare("INSERT INTO `{$wpdb->prefix}mls_lexicon_categories_img` (`id`, `image`, `mimetype`) values (%d, LOAD_FILE(%s), %s) ON DUPLICATE KEY UPDATE `image`=LOAD_FILE(%s), `mimetype`=%s", $cat[0], $path, $imgtype, $path, $imgtype);
							$wpdb->query($sql);
						}	
						// Check if file loaded; 
						if($wpdb->get_results("SELECT id from `{$wpdb->prefix}mls_lexicon_categories_img` WHERE `id`={$cat[0]} AND `image` IS NULL")){
							// LOAD_FILE failed, try something else
							$image = file_get_contents($path);
							$image = '0x'.bin2hex($image);
							$sql = $wpdb->prepare("UPDATE  `{$wpdb->prefix}mls_lexicon_categories_img` SET `image`={$image} WHERE `id`=%d", $cat[0]);
							$wpdb->query($sql);
						}
							break;
						default:
							break;
					}
	}
	return true;
}

function mls_lexicon_load_csv($paths) {
	global $wpdb;
	$wpdb->query("SET FOREIGN_KEY_CHECKS=0;");
	foreach($paths as $path) {
	$sqls = array();
	$data = file($path);
				$data[0] = trim($data[0]);
				$type = explode(';', $data[0]);
				$data = array_slice($data, 1);
				$inserted = false;
				foreach($data as $line) {
					$line = trim($line, "\t\n\r\0\x0B");
					$ed = explode(';', $line);
						switch(strtolower($type[0])) {
						case 'codes':
							$sqls[] = $wpdb->prepare("INSERT INTO {$wpdb->prefix}mls_lexicon_codes (code, notion_type, class, subclass, mgroup, subgroup, unit, theme)
										values (%s, %d, %d, %d, %d, %d, %d, %d) ON DUPLICATE KEY UPDATE
										notion_type=VALUES(notion_type), class=VALUES(class), subclass=VALUES(subclass), mgroup=VALUES(mgroup),
										subgroup=VALUES(subgroup),unit=VALUES(unit),theme=VALUES(theme)
										;", $ed);
							break;
						case 'cat_codes':
							$sqls[] = $wpdb->prepare("INSERT INTO {$wpdb->prefix}mls_lexicon_categories (notion_type, class, subclass, mgroup, subgroup)
										values (%d, %d, %d, %d, %d) ON DUPLICATE KEY UPDATE
										notion_type=VALUES(notion_type), class=VALUES(class), subclass=VALUES(subclass), mgroup=VALUES(mgroup),
										subgroup=VALUES(subgroup)
										;", $ed);
						break;
						case 'category':
							$sqls[] = $wpdb->prepare("INSERT INTO {$wpdb->prefix}mls_lexicon_categories_trans (cat_id, name, lang)
										values ((SELECT id from {$wpdb->prefix}mls_lexicon_categories WHERE 
										notion_type = %d AND class = %d AND subclass = %d AND mgroup = %d AND subgroup = %d), %s, %s) ON DUPLICATE KEY UPDATE
										lang=VALUES(lang), name=VALUES(name)
										;", array_merge($ed, array($type[1])));
							break;
						case 'module':
							if($wpdb->get_var("SELECT id FROM {$wpdb->prefix}mls_lexicon_lang_mod WHERE lang='{$type[1]}' AND level='{$type[2]}'")) {
								$inserted = true;
							}
							if(!$inserted) {								
							$sqls[] = $wpdb->prepare("INSERT INTO {$wpdb->prefix}mls_lexicon_lang_mod (lang, level) values (%s, %s)
							ON DUPLICATE KEY UPDATE lang=VALUES(lang), level=VALUES(level);", $type[1], $type[2]);
							$inserted = true;
							}
							if(strlen($ed[1]) == 3) {
								$ed[1] = 1;
							} else {
								$ed[1] = 0;
							}
							$sqls[] = $wpdb->prepare("INSERT INTO {$wpdb->prefix}mls_lexicon_words (lang_mod_id, code, must_learn, text, phrase)
										values ((SELECT id from {$wpdb->prefix}mls_lexicon_lang_mod WHERE lang='{$type[1]}' AND level='{$type[2]}'), %s, %d, %s, %s)
										ON DUPLICATE KEY UPDATE
										lang_mod_id=VALUES(lang_mod_id), code=VALUES(code), must_learn=VALUES(must_learn), text=VALUES(text), phrase=VALUES(phrase)
										;", $ed);
							break;
						case 'course':
						if(!$inserted) {
							$course_id = set_courses($type[1],$type[2],$type[3],$type[4]);
							$admins =  get_super_admins();
							$user = get_user_by("login", $admins[0]); //admin
							set_course_teacher($user->ID, $course_id);
							$inserted = true;
						}
							$sqls[] = $wpdb->prepare("INSERT INTO {$wpdb->prefix}mls_lexicon_course_codes (course_id, code, category_code)
										values ({$course_id}, %s, %d)
										ON DUPLICATE KEY UPDATE
										course_id=VALUES(course_id), code=VALUES(code), category_code=VALUES(category_code)
										;", $ed);
							break;
						default:
							break;
						}
				}
				$wpdb->query("START TRANSACTION");
				$error = false;
				foreach($sqls as $sql) {
				if(!$wpdb->query($sql)){ 
				$error = true;
				break;
				}
			}
				if(!$error) {
			$wpdb->query("COMMIT");
		} else {
			$wpdb->query("ROLLBACK");
		}
	}
	$wpdb->query("SET FOREIGN_KEY_CHECKS=1;");
	return true;
}


function mls_lexicon_clean_temp(){
	$plug_dir = str_replace("\\","/",mls_lexicon_DIR);
	if(is_dir($plug_dir)) {
		$dir=opendir($plug_dir);
		chdir($plug_dir);
			while($archive = readdir($dir)) {
				if($archive != '.' && $archive != '..'){
						if(is_dir($archive)) {
							if (strpos($archive,'temp_') !== false) {
   								 mls_lexicon_delete_dir(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $plug_dir.DIRECTORY_SEPARATOR.$archive));
							}
						}
				}
			}
		chdir("../");
		closedir($dir);
	}
}


function mls_lexicon_delete_dir($dir) {
$it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
$files = new RecursiveIteratorIterator($it,
             RecursiveIteratorIterator::CHILD_FIRST);
foreach($files as $file) {
    if ($file->isDir()){
        rmdir($file->getRealPath());
    } else {
        unlink($file->getRealPath());
    }
}
rmdir($dir);	
}

function get_course_teacher($course_id){
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'mls_lexicon_course_author';	
	$query =  "SELECT teacher_id FROM ".$tablaprefix. " WHERE course_id ='".$course_id."'";
	$registro = $wpdb->get_results($query);	
	return $registro;
	
}


function get_mls_lexicon_users($exclude = NULL) { 

    $users = array();
    $roles = array('mls_lexicon_admin', 'mls_lexicon_editor', 'mls_lexicon_teacher', 'mls_lexicon_student');
	if($exclude) {
		$roles = array_diff($roles, $exclude);
	}

    foreach ($roles as $role) {
        $users_query = new WP_User_Query( array( 
            'fields' => 'all_with_meta', 
            'role' => $role, 
            'orderby' => 'display_name'
            ) );
        $results = $users_query->get_results();
        if ($results) $users = array_merge($users, $results);
	}

    return $users;
}
function mls_lexicon_create_module ($lang, $level) {
	global $wpdb;
	$sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}mls_lexicon_lang_mod (lang, level) values (%s, %s)", $lang, $level);
	$wpdb->query("START TRANSACTION");
	if($wpdb->query($sql)) {
		$wpdb->query("COMMIT");	
		$res = $wpdb->get_results($wpdb->prepare("SELECT id from {$wpdb->prefix}mls_lexicon_lang_mod where lang=%s AND level=%s", $lang, $level));
		return $res;
	} else {
		$wpdb->query("ROLLBACK");
	 return -1;	
	}
	
}
function get_teacher_courses($teacher_id){
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'mls_lexicon_course_author';
	
	$query =  "SELECT course_id FROM ".$tablaprefix. " WHERE teacher_id ='".$teacher_id."'";
	$registros = $wpdb->get_results($query);
	
	return $registros;
	
}

function set_course_teacher($teacher_id , $course_id){
	global $wpdb;
			
	$wpdb->replace( 
		$wpdb->prefix.'mls_lexicon_course_author' ,
		array(
			'teacher_id' => $teacher_id,
			'course_id' => $course_id
			
			),
		array(
			 '%d',
			 '%d'
			 )
	);
	return $wpdb->insert_id;
}
function delete_course($cids) { // can be int, can be array of ints
	$res = -1;
	global $wpdb;
	$sqls = array();
	if(is_array($cids)) {
		foreach ($cids as $cid) {
			$sqls[] = $wpdb->prepare("DELETE FROM ".$wpdb->prefix."mls_lexicon_course WHERE id=%d", $cid);
		}
	} else {
		$sqls[] = $wpdb->prepare("DELETE FROM ".$wpdb->prefix."mls_lexicon_course WHERE id=%d", $cids);
	}
	
	$error = false;
			$wpdb->query('START TRANSACTION');
			foreach($sqls as $sql) {
				
				if(!$wpdb->query($sql)){
				$error = -1;
				break;
				};
			}
			if(!$error) {
					$wpdb->query('COMMIT');
					$res = 1;
				} else {
					 $wpdb->query('ROLLBACK');
					
				}

	return $res;
	
}

function delete_course_word($cids, $w_code) { // can be int, can be array of ints + word code
	$res = -1;
	global $wpdb;
	$sqls = array();
	if(is_array($cids)) {
		foreach ($cids as $cid) {
			if(is_array($w_code)) {
				foreach($w_code as $w_c) {
			$sqls[] = $wpdb->prepare("DELETE FROM ".$wpdb->prefix."mls_lexicon_course_codes WHERE course_id=%d AND code=%s", $cid, $w_c);
				}
			} else {
				$sqls[] = $wpdb->prepare("DELETE FROM ".$wpdb->prefix."mls_lexicon_course_codes WHERE course_id=%d AND code=%s", $cid, $w_code);
			}
		}
	} else {
		if(is_array($w_code)) {
				foreach($w_code as $w_c) {
			$sqls[] = $wpdb->prepare("DELETE FROM ".$wpdb->prefix."mls_lexicon_course_codes WHERE course_id=%d AND code=%s", $cids, $w_c);
				}
			} else {
				$sqls[] = $wpdb->prepare("DELETE FROM ".$wpdb->prefix."mls_lexicon_course_codes WHERE course_id=%d AND code=%s", $cids, $w_code);
			}
	}
	
	$error = false;
			$wpdb->query('START TRANSACTION');
			foreach($sqls as $sql) {
				
				if(!$wpdb->query($sql)){
				$error = -1;
				break;
				};
			}
			if(!$error) {
					$wpdb->query('COMMIT');
					$res = 1;
				} else {
					 $wpdb->query('ROLLBACK');
					
				}

	return $res;
	
}
function get_words($code){
	global $wpdb;
	$tableprefix = $wpdb->prefix.'mls_lexicon_words';
	
	$query =  "SELECT * FROM ".$tableprefix. " WHERE codigo ='".$code."'";
	$registro = $wpdb->get_results($query);
	
	return $registro;
	
}

function get_lang_words_by_level($lang,$level){
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'mls_lexicon_words';
	
	$query =  "SELECT code FROM ".$tablaprefix. " WHERE lang ='".$idioma."' AND level='".$nivel."' ORDER BY code DESC";
	$registro = $wpdb->get_results($query);
	
	return $registro;
	
}
function get_encoded_img($id, $type) {
	global $wpdb;
	 	switch($type) {
		case 'cat':
			$img = $wpdb->get_row($wpdb->prepare("SELECT image, mimetype from {$wpdb->prefix}mls_lexicon_categories_img WHERE id=%d", $id));
		break;
		case 'word':
			$img = $wpdb->get_row($wpdb->prepare("SELECT image, mimetype from {$wpdb->prefix}mls_lexicon_codes_img WHERE code=%d", $id));
		break;	
		}
		if(is_null($img)){
		$result = plugins_url().DIRECTORY_SEPARATOR."mls_lexicon".DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR."default.png";
		} else {
		$result = "data:".$img->mimetype.";base64,".base64_encode( $img->image );
		}
	 	return $result;
		
}
function set_word($code,$lang,$level,$text,$context,$audio,$teacher_id){
	global $wpdb;
			
	$wpdb->insert( 
		$wpdb->prefix.'mls_lexicon_words' ,
		array(
			'code' => $code,
			'lang' => $lang,
			'level' => $level,
			'text' => $text,
			'context' => $context,
			'audio' => $audio,
			'teacher_id' => $teacher_id,
					
			),
		array(
			 '%d',
			 '%s',
			 '%s',
			 '%s',
			 '%s',
			 '%s',
			 '%d'
			 )
	);
	return $wpdb->insert_id;
}
/**************************************************************************************************/
function get_flashcard($cardid){
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'mls_lexicon_flashcard';
	
	$query =  "SELECT * FROM ".$tablaprefix. " WHERE ID ='".$cardid."'";
	$registro = $wpdb->get_results($query);
	
	return $registro;
	
}

function set_flashcard($pregunta_id , $respuesta_id, $img){
	global $wpdb;
			
	$wpdb->insert( 
		$wpdb->prefix.'mls_lexicon_flashcard' ,
		array(
			'id_pregunta' => $pregunta_id,
			'id_respuesta' => $respuesta_id,
			'imagen' => $img			
			),
		array(
			 '%s',
			 '%s',
			 '%s'
			 )
	);
	return $wpdb->insert_id;
}

function get_cursos_card($cursoid){
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'_mls_lexicon_curso_card';
	
	$query =  "SELECT flashcard_id FROM ".$tablaprefix. " WHERE curso_id = '".$cursoid."'";
	$registros = $wpdb->get_results($query);
	
	return $registros;
	
}

function set_cursos_card($cursoid , $cardid){
	global $wpdb;
			
	$wpdb->insert( 
		$wpdb->prefix.'_mls_lexicon_curso_card' ,
		array(
			'curso_id' => $cursoid,
			'flashcard_id' => $cardid
			),
		array(
			 '%d',
			 '%d'
			 )
	);
	return $wpdb->insert_id;
}
/********************************************************************************/
function get_course_student_card($Idestu,$idCurso){
	
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'mls_lexicon_course_student_card';
	
	//$query =  "SELECT flashcard_id,nivelcubo FROM '".$tablaprefix." WHERE estudiante_id ='".$Idestu."' AND curso_id ='".$idCurso."'";
	$query =  "SELECT code, nivelcubo FROM ".$tablaprefix." WHERE student_id ='".$Idestu."' AND course_id ='".$idCurso."'";
	
	$registros = $wpdb->get_results($query);
	
	return $registros;
}
	

function set_courses($lang1,$lang2,$level,$des){
	global $wpdb;
	$wpdb->query("INSERT INTO {$wpdb->prefix}mls_lexicon_course (lang_1, lang_2, level, description) values ('{$lang1}', '{$lang2}', '{$level}', '{$des}') ON DUPLICATE KEY UPDATE `lang_1`='{$lang1}', `lang_2`='{$lang2}', `level`='{$level}', `description`='{$des}'");	
	return $wpdb->get_var("SELECT id FROM {$wpdb->prefix}mls_lexicon_course WHERE `lang_1`='{$lang1}' AND `lang_2`='{$lang2}' AND `level`='{$level}'");	
}



 
function get_cursosDestino($cursoBase){
	$registros = "";
	if($cursoBase != ""){		
		global $wpdb;
		$tablaprefix = $wpdb->prefix.'_mls_lexicon_curso';
		
		$query =  "SELECT idioma_2 FROM ".$tablaprefix. " WHERE idioma_1 = '".$cursoBase."'";
		$registros = $wpdb->get_results($query);		
	}
	
}

function get_course($lang_1, $lang_2, $level){
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'mls_lexicon_course';
	
	$query =  "SELECT id FROM ".$tablaprefix. " WHERE lang_1 ='".$lang_1."' AND lang_2 ='".$lang_2."' AND level ='".$level."'";
	$registro = $wpdb->get_results($query);
	
	return $registro;
	
}

function get_courseById($courseID){
	
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'mls_lexicon_course';
	
	$query =  "SELECT * FROM ".$tablaprefix." WHERE id ='".$courseID."'";
	
	$registro = $wpdb->get_row($query);
	
	return $registro;	
}

function get_cursoByCode($code){
	
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'_mls_lexicon_curso';
	
	$query =  "SELECT * FROM ".$tablaprefix;
	
	$registro = $wpdb->get_results($query);
	
	foreach ($registro as $reg){
	
		if(substr($reg->idioma_1,0,3) == substr($code,1,3) && substr($reg->idioma_2,0,3) == substr($code,5,3) && $reg->nivel == substr($code,9,2)){		
			return $reg;
		}
			
	}
	
}



function set_course_user($user , $course_id){
		global $wpdb;
		$sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}mls_lexicon_course_student (student_id, course_id) values (%s, %s)", $user, $course_id);	
		$wpdb->query("START TRANSACTION");
	 	$res=$wpdb->query($sql);
		 if($res)
      	{
		  $sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}mls_lexicon_course_student_card (student_id, course_id, code, prog_level)
          SELECT %s, %s, code, 1
          FROM   {$wpdb->prefix}mls_lexicon_course_codes
          WHERE  course_id = %s", $user, $course_id, $course_id);
          $res = $wpdb->query($sql);
	  	}
		if($res) {
			$wpdb->query("COMMIT");	
		} else {
			$wpdb->query("ROLLBACK");
		}
		return $res;
}

function del_course_user($user, $course) {
	write_log("Attempting to delete users course");
	global $wpdb;
	$sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}mls_lexicon_course_student WHERE (student_id=%s AND course_id=%s)", $user, $course);
	write_log($sql);
	$wpdb->query("START TRANSACTION");
	$res=$wpdb->query($sql);
	write_log($res);
	if($res) {
			$wpdb->query("COMMIT");	
		} else {
			$wpdb->query("ROLLBACK");
		}
		return $res;
	
	
}

function get_official_courses(){
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'mls_lexicon_course';
	$query =  "SELECT * FROM ".$tablaprefix;
	$result = $wpdb->get_results($query);
	
	return $result;
}

function get_course_user($user){
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'mls_lexicon_course_student';
	
	$query =  "SELECT course_id FROM ".$tablaprefix. " WHERE student_id ='".$user."'";
	$registros = $wpdb->get_results($query);
	
	return $registros;
}

function check_course($user, $course) {
	global $wpdb;
	$table = $wpdb->prefix.'mls_lexicon_course_student';
	$sql = $wpdb->prepare("SELECT * FROM {$table} WHERE student_id = %s AND course_id = %s", $user, $course);
	$res = $wpdb->get_results($sql);
	
	return $res;
}

function crearValueCurso($idio1,$idio2,$lvl){
	$idiom1=substr($idio1,0,3);
	$idiom2=substr($idio2,0,3);
	
	$cursFinal = "[".$idiom1."-".$idiom2."-".$lvl."]";
	return $cursFinal;
}

function asociarPalabras($aux,$auxIdCurso){
	global $wpdb;
	
	$ShortBase = strtoupper(substr($aux,1,3));
	$ShortDestino = strtoupper(substr($aux,5,3));
	$ShortLevel = strtoupper(substr($aux,9,2));	
	
	$aux_1 = get_palabrabyIDIOMALVL($ShortBase,$ShortLevel);
	
	$aux_2 = get_palabrabyIDIOMALVL($ShortDestino,$ShortLevel);
	
	
	for($i = 0; $i <= sizeof($aux_1); $i++){
			
			set_flashcard($aux_1[$i]->codigo,$aux_2[$i]->codigo," ");
			set_cursos_card($auxIdCurso, $wpdb->insert_id);
					
	}
	
}

function lex_profe_crearCurso()
{

}

function crearShortcode($Ib,$Id,$Il){
	
	$CodesAux = array();
	$CodesAux[0] = "[level-".strtolower(substr($Ib,0,3))."-".strtolower(substr($Id,0,3))."-".strtolower(substr($Il,0,2))."]";
	$CodesAux[1] = "[/level-".strtolower(substr($Ib,0,3))."-".strtolower(substr($Id,0,3))."-".strtolower(substr($Il,0,2))."]";
	
	return $CodesAux;
	
	
	
}
	
function admin_post_crearCurso(){	
	
	check_admin_referer( 'crearCurso_Verificar' );
		
	$Ibase = $_POST['idiomaBase'];
	$Idest = $_POST['idiomaDestino'];
	$Ilvl  = $_POST['nivel'];
	$descrip = utf8_decode($_POST['descripcion']);
	
	if( $Ibase == null || $Idest == null || $Ilvl == null ){
		wp_redirect(  admin_url( 'admin.php?page=lex_profe_crearCurso&m=3'));
	}else{
		
		if(get_cursos($Ibase, $Idest,$Ilvl) != null || get_page_by_title($CursFinal) != null){
			
			wp_redirect(  admin_url( 'admin.php?page=lex_profe_crearCurso&m=2'));		
			
		}else{			
			
			$IdCursoFinal = set_cursos( $Ibase , $Idest, $Ilvl , $descrip);// DEVUELVE EL ID DEL CURSO RECIÉN CREADO
			
			set_cursos_profe(get_current_user_id(),$IdCursoFinal);// ASOCIA EL CURSO CON EL PROFESOR QUE LO HA CREADO
			
			$CursFinal = crearValueCurso($Ibase,$Idest,$Ilvl);//LLAMA A LA FUNCIÖN QUE CREA EL SHORTCODE
		
			$ShortCode = crearShortcode($Ibase,$Idest,$Ilvl);//CREA EL SHORTCODE PARA INTRODUCIR A LA PAGINA CREADA
			
		 	$Pageslug = '_mls_lexicon_'.$CursFinal;					
						
		 	add_posts_page($CursFinal,$CursFinal,'read',$Pageslug,'mls_lexicon_check_user');
												
				/*******************/
				//Creacion de la pagina!
				$post = array ();
				$post['post_title'] = $CursFinal;
				$post['post_type'] = 'page';
				$post['post_content'] = $ShortCode[0].' [curso] '.$ShortCode[1];
				$post['post_status'] = 'publish';
				$post['post_author'] = get_current_user_id();
				$post['comment_status'] = 'closed';
				$post['ping_satuts'] = 'closed';
				$post['page_template'] = 'front-page.php';
				/*******************/	
			
				wp_insert_post($post);	
				asociarPalabras($CursFinal,$IdCursoFinal);
				wp_redirect(  admin_url( 'admin.php?page=lex_profe_crearCurso&m=1')); 							
		 }
	}
	
}

/*

  Funciones

*/

//header( 'Location: http://www.ejemplo.travel/en' ); 
function getUserLanguage()
{ 
  $lang = substr($_SERVER["HTTP_ACCEPT_LANGUAGE"],0,2);
  return $lang;
} 

function mls_lexicon_get_course_base()
{
  global $wpdb;
  $tabla = $wpdb->prefix.'mls_lexicon_course';  
  $query =  "SELECT DISTINCT lang_1 FROM ".$tabla."";
  return $wpdb->get_results($query);
}

function mls_lexicon_image_path($code)
{
  return plugins_url() . "/mls_lexicon/images/A1/" . $code . ".jpg";
}

function mls_lexicon_audio_path($codigo, $idiom)
{
  return plugins_url() . "/mls_lexicon/audios/" . $idiom . $codigo . ".mp3";
}
function mls_lexicon_get_module_id ( $lang, $level) {
	global $wpdb;
	return $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}mls_lexicon_lang_mod WHERE lang=%s AND level=%d", $lang, $level));
}
function mls_lexicon_next_flashcard($code, $lang_1, $lang_2)
{
  global $wpdb;
   $sql_1 = "SELECT w.text,w.phrase FROM {$wpdb->prefix}mls_lexicon_words w LEFT JOIN {$wpdb->prefix}mls_lexicon_lang_mod m ON (m.lang = '{$lang_1}' AND m.id = w.lang_mod_id) WHERE w.code = '{$code}' AND m.id = w.lang_mod_id";
  $res_1 = $wpdb->get_row($sql_1);
  $sql_2 = "SELECT w.text,w.phrase FROM {$wpdb->prefix}mls_lexicon_words w LEFT JOIN {$wpdb->prefix}mls_lexicon_lang_mod m ON (m.lang = '{$lang_2}' AND m.id = w.Lang_mod_id) WHERE w.code = '{$code}' AND m.id = w.lang_mod_id";
  $res_2 = $wpdb->get_row($sql_2);
  $order_flashcards = get_option('mls_lexicon_order_flashcards');
  $estado_palabra = array();
  if($order_flashcards == "basic")
  {
    
      $estado_palabra[] = $res_1;
      $estado_palabra[] = $res_2;
    
    
  }
  elseif($order_flashcards == "fast")
  {
    
      $estado_palabra[] = $res_2;
      $estado_palabra[] = $res_1;
    
    
  }
  elseif($order_flashcards == "random")
  {
    $aleatorio = rand(0,1);
    $estado_palabra[] = $res[$aleatorio];
    if($aleatorio == 0)
      $estado_palabra[] = $res_2;
    else
      $estado_palabra[] = $res_1;
  }

  return $estado_palabra;
}


//////////////////////////////////////////////////////////////////////
function user_can_save( $post_id, $plugin_file, $nonce )
{
  $is_autosave = wp_is_post_autosave( $post_id );
  $is_revision = wp_is_post_revision( $post_id );
  $is_valid_nonce = ( isset( $_POST[ $nonce ] ) && wp_verify_nonce( $_POST[ $nonce ], $plugin_file ) );
  // Return true if the user is able to save; otherwise, false.
  return ! ( $is_autosave || $is_revision ) && $is_valid_nonce;
}
function has_files_to_upload( $id )
{
  return (!empty($_FILES)) && isset($_FILES[ $id ]);
}
function file_upload_max_size() {
  static $max_size = -1;

  if ($max_size < 0) {
    // Start with post_max_size.

$max_upload = (int)(ini_get('upload_max_filesize'))*1024*1024;
$max_post = (int)(ini_get('post_max_size'))*1024*1024;
$memory_limit = (int)(ini_get('memory_limit'))*1024*1024;
    // If upload_max_size is less, then reduce. Except if upload_max_size is
    // zero, which indicates no limit.

    
  }
  return array($max_upload, $max_post, $memory_limit) ;
}

function parse_size($size) {
  $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
  $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
  if ($unit) {
    // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
    return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
  }
  else {
    return round($size);
  }
}

function iso639_1_to_english_string($code)
{
  $languageCodes = array(
 "aa" => "Afar",
 "ab" => "Abkhazian",
 "ae" => "Avestan",
 "af" => "Afrikaans",
 "ak" => "Akan",
 "am" => "Amharic",
 "an" => "Aragonese",
 "ar" => "Arabic",
 "as" => "Assamese",
 "av" => "Avaric",
 "ay" => "Aymara",
 "az" => "Azerbaijani",
 "ba" => "Bashkir",
 "be" => "Belarusian",
 "bg" => "Bulgarian",
 "bh" => "Bihari",
 "bi" => "Bislama",
 "bm" => "Bambara",
 "bn" => "Bengali",
 "bo" => "Tibetan",
 "br" => "Breton",
 "bs" => "Bosnian",
 "ca" => "Catalan",
 "ce" => "Chechen",
 "ch" => "Chamorro",
 "co" => "Corsican",
 "cr" => "Cree",
 "cs" => "Czech",
 "cu" => "Church Slavic",
 "cv" => "Chuvash",
 "cy" => "Welsh",
 "da" => "Danish",
 "de" => "German",
 "dv" => "Divehi",
 "dz" => "Dzongkha",
 "ee" => "Ewe",
 "el" => "Greek",
 "en" => "English",
 "eo" => "Esperanto",
 "es" => "Spanish",
 "et" => "Estonian",
 "eu" => "Basque",
 "fa" => "Persian",
 "ff" => "Fulah",
 "fi" => "Finnish",
 "fj" => "Fijian",
 "fo" => "Faroese",
 "fr" => "French",
 "fy" => "Western Frisian",
 "ga" => "Irish",
 "gd" => "Scottish Gaelic",
 "gl" => "Galician",
 "gn" => "Guarani",
 "gu" => "Gujarati",
 "gv" => "Manx",
 "ha" => "Hausa",
 "he" => "Hebrew",
 "hi" => "Hindi",
 "ho" => "Hiri Motu",
 "hr" => "Croatian",
 "ht" => "Haitian",
 "hu" => "Hungarian",
 "hy" => "Armenian",
 "hz" => "Herero",
 "ia" => "Interlingua (International Auxiliary Language Association)",
 "id" => "Indonesian",
 "ie" => "Interlingue",
 "ig" => "Igbo",
 "ii" => "Sichuan Yi",
 "ik" => "Inupiaq",
 "io" => "Ido",
 "is" => "Icelandic",
 "it" => "Italian",
 "iu" => "Inuktitut",
 "ja" => "Japanese",
 "jv" => "Javanese",
 "ka" => "Georgian",
 "kg" => "Kongo",
 "ki" => "Kikuyu",
 "kj" => "Kwanyama",
 "kk" => "Kazakh",
 "kl" => "Kalaallisut",
 "km" => "Khmer",
 "kn" => "Kannada",
 "ko" => "Korean",
 "kr" => "Kanuri",
 "ks" => "Kashmiri",
 "ku" => "Kurdish",
 "kv" => "Komi",
 "kw" => "Cornish",
 "ky" => "Kirghiz",
 "la" => "Latin",
 "lb" => "Luxembourgish",
 "lg" => "Ganda",
 "li" => "Limburgish",
 "ln" => "Lingala",
 "lo" => "Lao",
 "lt" => "Lithuanian",
 "lu" => "Luba-Katanga",
 "lv" => "Latvian",
 "mg" => "Malagasy",
 "mh" => "Marshallese",
 "mi" => "Maori",
 "mk" => "Macedonian",
 "ml" => "Malayalam",
 "mn" => "Mongolian",
 "mr" => "Marathi",
 "ms" => "Malay",
 "mt" => "Maltese",
 "my" => "Burmese",
 "na" => "Nauru",
 "nb" => "Norwegian Bokmal",
 "nd" => "North Ndebele",
 "ne" => "Nepali",
 "ng" => "Ndonga",
 "nl" => "Dutch",
 "nn" => "Norwegian Nynorsk",
 "no" => "Norwegian",
 "nr" => "South Ndebele",
 "nv" => "Navajo",
 "ny" => "Chichewa",
 "oc" => "Occitan",
 "oj" => "Ojibwa",
 "om" => "Oromo",
 "or" => "Oriya",
 "os" => "Ossetian",
 "pa" => "Panjabi",
 "pi" => "Pali",
 "pl" => "Polish",
 "ps" => "Pashto",
 "pt" => "Portuguese",
 "qu" => "Quechua",
 "rm" => "Raeto-Romance",
 "rn" => "Kirundi",
 "ro" => "Romanian",
 "ru" => "Russian",
 "rw" => "Kinyarwanda",
 "sa" => "Sanskrit",
 "sc" => "Sardinian",
 "sd" => "Sindhi",
 "se" => "Northern Sami",
 "sg" => "Sango",
 "si" => "Sinhala",
 "sk" => "Slovak",
 "sl" => "Slovenian",
 "sm" => "Samoan",
 "sn" => "Shona",
 "so" => "Somali",
 "sq" => "Albanian",
 "sr" => "Serbian",
 "ss" => "Swati",
 "st" => "Southern Sotho",
 "su" => "Sundanese",
 "sv" => "Swedish",
 "sw" => "Swahili",
 "ta" => "Tamil",
 "te" => "Telugu",
 "tg" => "Tajik",
 "th" => "Thai",
 "ti" => "Tigrinya",
 "tk" => "Turkmen",
 "tl" => "Tagalog",
 "tn" => "Tswana",
 "to" => "Tonga",
 "tr" => "Turkish",
 "ts" => "Tsonga",
 "tt" => "Tatar",
 "tw" => "Twi",
 "ty" => "Tahitian",
 "ug" => "Uighur",
 "uk" => "Ukrainian",
 "ur" => "Urdu",
 "uz" => "Uzbek",
 "ve" => "Venda",
 "vi" => "Vietnamese",
 "vo" => "Volapuk",
 "wa" => "Walloon",
 "wo" => "Wolof",
 "xh" => "Xhosa",
 "yi" => "Yiddish",
 "yo" => "Yoruba",
 "za" => "Zhuang",
 "zh" => "Chinese",
 "zu" => "Zulu"
);
  return $languageCodes[$code];
}

?>