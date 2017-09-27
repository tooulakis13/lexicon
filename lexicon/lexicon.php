<?php

/*

  Plugin Name: Lexicon
  Plugin URI: http://www.patmir.com/
  Description: Plugin to learn vocabulary in different languages.
  Author: MY LANGUAGE SKILLS S.L.U
  Author URI: http://www.linkedin.com/pub/toni-devis-lopez/40/249/693
  Version: 1.0	
  License: GPLv2 or later

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*/

// Location variables
define('LEXICON_DIR', dirname( __FILE__ ) );
define('LEXICON_DIR_RELATIVO', dirname( plugin_basename( __FILE__ )));
define('LEXICON_URL', plugin_dir_url( __FILE__ ));

//Links to necessary files
require_once(LEXICON_DIR.'/includes/lexicon_functions.php');
require_once(LEXICON_DIR.'/includes/lexicon_ajax.php');

class Lexicon {
	function Lexicon() {
		if(isset($this)) {
			//  Actions
			add_action('admin_menu', array(&$this, 'lexicon_menu'));
			add_action('activate_lexicon/lexicon.php', array(&$this, 'lexicon_install'));
			add_action('admin_post_createCourse', 'admin_post_createCourse' );
			add_action('admin_enqueue_scripts', array(&$this, 'lexicon_add_stylesheet'));
			add_action('admin_enqueue_scripts', array(&$this, 'lexicon_add_javascript'));
			add_action('admin_enqueue_scripts', array(&$this, 'lexicon_add_canvas'));
			add_action('wp_print_styles', array(&$this, 'lexicon_add_stylesheet'));
			add_action('wp_print_scripts', array(&$this, 'lexicon_add_javascript'));
			add_action('wp_print_scripts', array(&$this, 'lexicon_add_canvas'));
			add_action('wp_ajax_my_action_one', 'lexicon_my_action_callback');   //this line is for logged in users
			add_action('wp_ajax_nopriv_my_action_one', 'lexicon_my_action_callback'); // this is for not logged in users
			add_action( 'delete_user', array(&$this, 'lexicon_delete_user') );
			register_activation_hook(__FILE__,array(&$this, 'lexicon_activation'));
			register_deactivation_hook(__FILE__,array(&$this, 'lexicon_deactivation'));
			add_shortcode('lexicon',array(&$this, 'lexicon_init_shortcode')); //ShortCode necessary to ininiate the lexion page
		}
	}


///////////////////////////////////////////////////////////////////////////////////
//  Installation
///////////////////////////////////////////////////////////////////////////////////

function lexicon_install()
{
	
/*****************************************
**		Database installation
*****************************************/

		global $wpdb;
		$lexicon_db_version = "1.1";
		define('_LEXICON_COURSE', $wpdb->prefix.'lexicon_course');
		define('_LEXICON_COURSE_STUDENT', $wpdb->prefix.'lexicon_course_student');
		define('_LEXICON_COURSE_SUTDENT_CARD', $wpdb->prefix.'lexicon_course_student_card');
		define('_LEXICON_COURSE_AUTHOR', $wpdb->prefix.'lexicon_course_author');
		define('_LEXICON_COURSE_CODES', $wpdb->prefix.'lexicon_course_codes');
		define('_LEXICON_WORDS', $wpdb->prefix.'lexicon_words');
		// Check for existing DB
		if (get_option("lexicon_db_version")=="") { 
			//No db found
			
			/*
			*	DB Tables SQL
			*/
			$sqls = Array();
			// lexicon_course
			$sqls[] = "CREATE TABLE `"._LEXICON_COURSE."`(
					`id` int unsigned NOT NULL auto_increment PRIMARY KEY,
					`lang_1` varchar(30) NOT NULL DEFAULT '',
					`lang_2` varchar(30) NOT NULL DEFAULT '',		
					`level` varchar(10) NOT NULL DEFAULT '',
					`description` varchar(255)							
					) DEFAULT CHARSET=utf8; ";
			// lexicon_course_student
			$sqls[] = "CREATE TABLE `"._LEXICON_COURSE_STUDENT."`(
					`student_id` bigint(20) NOT NULL DEFAULT 0,
					`course_id` int unsigned NOT NULL DEFAULT 0,
					`state` int unsigned NOT NULL DEFAULT 0,
          			PRIMARY KEY (student_id, course_id),
					CONSTRAINT `COURSE_STUDENT_FK01` FOREIGN KEY (course_id) REFERENCES "._LEXICON_COURSE." (id)
       				ON DELETE CASCADE
				    ON UPDATE CASCADE	
					) DEFAULT CHARSET=utf8; ";
			// lexicon_course_student_card
			$sqls[] = "CREATE TABLE `"._LEXICON_COURSE_SUTDENT_CARD."`(
					`student_id` bigint(20) NOT NULL DEFAULT 0,
					`course_id` int unsigned NOT NULL DEFAULT 0,
					`code` varchar(16) NOT NULL DEFAULT '',
					`prog_level` int unsigned NOT NULL DEFAULT 0,
					CONSTRAINT `COURSE_STUDENT_CARD_FK01` FOREIGN KEY (student_id) REFERENCES "._LEXICON_COURSE_STUDENT." (student_id)
       				ON DELETE CASCADE
				    ON UPDATE CASCADE,
					CONSTRAINT `COURSE_STUDENT_CARD_FK02` FOREIGN KEY (course_id) REFERENCES "._LEXICON_COURSE." (id)
       				ON DELETE CASCADE
				    ON UPDATE CASCADE			
					) DEFAULT CHARSET=utf8; ";
			// lexicon_course_author
			$sqls[] = "CREATE TABLE `"._LEXICON_COURSE_AUTHOR."`(
					`teacher_id` bigint(20) NOT NULL DEFAULT 0,
					`course_id` int unsigned NOT NULL DEFAULT 0,									
					PRIMARY KEY (teacher_id, course_id)					
					) DEFAULT CHARSET=utf8; ";
			// lexicon_course_codes
			$sqls[] = "CREATE TABLE `"._LEXICON_COURSE_CODES."`(
					`course_id` int unsigned NOT NULL DEFAULT 0,
					`code` varchar(16) NOT NULL DEFAULT '',
          			`context` varchar(120),
					PRIMARY KEY (course_id, code)			
					) DEFAULT CHARSET=utf8; ";
			// lexicon_words
			$sqls[] = "CREATE TABLE `"._LEXICON_WORDS."`(
					`id` int unsigned NOT NULL auto_increment PRIMARY KEY,
					`code` varchar(16) NOT NULL DEFAULT '',
					`text` varchar(30) NOT NULL DEFAULT '',
					`phrase` varchar(120),
          			`context` varchar(120),
  			        `level` varchar(10) NOT NULL DEFAULT '',
  			        `column_6` varchar(10) NOT NULL DEFAULT '',
  			        `column_7` varchar(10) NOT NULL DEFAULT '',
  			        `column_8` varchar(10) NOT NULL DEFAULT '',
  			        `column_9` varchar(10) NOT NULL DEFAULT '',
   			      	`column_10` varchar(10) NOT NULL DEFAULT '',
    			    `column_11` varchar(10) NOT NULL DEFAULT '',
  			        `column_12` varchar(10) NOT NULL DEFAULT '',
    			    `column_13` varchar(10) NOT NULL DEFAULT '',
     			    `column_14` varchar(10) NOT NULL DEFAULT '',
     			    `column_15` varchar(10) NOT NULL DEFAULT '',
    			    `column_16` varchar(10) NOT NULL DEFAULT '',
    			    `lang` varchar(30) NOT NULL DEFAULT ''
					) DEFAULT CHARSET=utf8; ";
			$error = false;
			$wpdb->query('START TRANSACTION');
			foreach($sqls as $sql) {
				
				if(!$wpdb->query($sql)){
				$error = $wpdb->print_error();
				break;
				};
			}
			
				if(!$error) {
					$wpdb->query('COMMIT');
				add_option("lexicon_db_version", $lexicon_db_version);
				} else {
					 $wpdb->query('ROLLBACK');
					echo $error;
				}
			} else {
				// DB version outdated
				
			$sqls = Array();
			$sqls[] = "SET foreign_key_checks = 0";
			//lexicon_course
			$sqls[] = "ALTER TABLE `"._LEXICON_COURSE."` 
					MODIFY `id` int unsigned NOT NULL auto_increment,
					MODIFY `lang_1` varchar(30) NOT NULL DEFAULT '',
					MODIFY `lang_2` varchar(30) NOT NULL DEFAULT '',		
					MODIFY `level` varchar(10) NOT NULL DEFAULT '',
					MODIFY `description` varchar(255),
					DROP PRIMARY KEY,
					ADD PRIMARY KEY (id)";
			//lexicon_course_student
			$sqls[] = "ALTER TABLE `"._LEXICON_COURSE_STUDENT."`
					MODIFY `student_id` bigint(20) NOT NULL DEFAULT 0,
					MODIFY `course_id` int unsigned NOT NULL DEFAULT 0,
					MODIFY `state` int unsigned NOT NULL DEFAULT 0,
					DROP PRIMARY KEY,
          			ADD PRIMARY KEY (student_id, course_id),
					DROP FOREIGN KEY `COURSE_STUDENT_FK01`,
					CONSTRAINT `COURSE_STUDENT_FK01` FOREIGN KEY (course_id) REFERENCES "._LEXICON_COURSE." (id)
       				ON DELETE CASCADE
				    ON UPDATE CASCADE";
			// lexicon_course_student_card
			$sqls[] = "ALTER TABLE `"._LEXICON_COURSE_SUTDENT_CARD."`
					MODIFY `student_id` bigint(20) NOT NULL DEFAULT 0,
					MODIFY `course_id` int unsigned NOT NULL DEFAULT 0,
					MODIFY `code` varchar(16) NOT NULL DEFAULT '',
					MODIFY `prog_level` int unsigned NOT NULL DEFAULT 0,
					DROP FOREIGN KEY `COURSE_STUDENT_CARD FK02`,
					DROP FOREIGN KEY `COURSE_STUDENT_CARD FK02`,
					CONSTRAINT `COURSE_STUDENT_CARD_FK01` FOREIGN KEY (student_id) REFERENCES "._LEXICON_COURSE_STUDENT." (student_id)
       				ON DELETE CASCADE
				    ON UPDATE CASCADE,
					CONSTRAINT `COURSE_STUDENT_CARD_FK02` FOREIGN KEY (course_id) REFERENCES "._LEXICON_COURSE." (id)
       				ON DELETE CASCADE
				    ON UPDATE CASCADE";
			// lexicon_course_author
			$sqls[] = "ALTER TABLE `"._LEXICON_COURSE_AUTHOR."`
					MODIFY `teacher_id` bigint(20) NOT NULL DEFAULT 0,
					MODIFY `course_id` int unsigned NOT NULL DEFAULT 0,	
					DROP PRIMARY KEY,								
					ADD PRIMARY KEY (teacher_id, course_id)";
			// lexicon_course_codes
			$sqls[] = "ALTER TABLE `"._LEXICON_COURSE_CODES."`
					MODIFY `course_id` int unsigned NOT NULL DEFAULT 0,
					MODIFY `code` varchar(16) NOT NULL DEFAULT '',
          			MODIFY `context` varchar(120),
		  			DROP PRIMARY KEY,
					ADD PRIMARY KEY (course_id, code)";
			// lexicon_words	
			$sqls[] = "ALTER TABLE `"._LEXICON_WORDS."`
					MODIFY `id` int unsigned NOT NULL auto_increment,
					MODIFY `code` varchar(16) NOT NULL DEFAULT '',
					MODIFY `text` varchar(30) NOT NULL DEFAULT '',
					MODIFY `phrase` varchar(120),
	          		MODIFY `context` varchar(120),
					MODIFY `level` varchar(10) NOT NULL DEFAULT '',
  			        MODIFY `column_6` varchar(10) NOT NULL DEFAULT '',
			        MODIFY `column_7` varchar(10) NOT NULL DEFAULT '',
  			        MODIFY `column_8` varchar(10) NOT NULL DEFAULT '',
  			        MODIFY `column_9` varchar(10) NOT NULL DEFAULT '',
   			   	    MODIFY `column_10` varchar(10) NOT NULL DEFAULT '',
  			        MODIFY `column_11` varchar(10) NOT NULL DEFAULT '',
   			    	MODIFY `column_12` varchar(10) NOT NULL DEFAULT '',
     			    MODIFY `column_13` varchar(10) NOT NULL DEFAULT '',
 			        MODIFY `column_14` varchar(10) NOT NULL DEFAULT '',
   			       	MODIFY `column_15` varchar(10) NOT NULL DEFAULT '',
  			        MODIFY `column_16` varchar(10) NOT NULL DEFAULT '',
   			       	MODIFY `lang` varchar(30) NOT NULL DEFAULT '',
					DROP PRIMARY KEY,
				  	ADD PRIMARY KEY (id)";	
			$sqls[] = "SET foreign_key_checks = 1";
				
				$error = false;
			$wpdb->query('START TRANSACTION');
			foreach($sqls as $sql) {
				
				if(!$wpdb->query($sql)){
				$error = $wpdb->print_error();
				break;
				};
			}
			
				if($error) {
					$wpdb->query('COMMIT');
				update_option("lexicon_db_version", $lexicon_db_version);
				} else {
					 $wpdb->query('ROLLBACK');
					echo $error;
				}
				
				
			}
		/*
		*	Lexicon Roles Creation
		*/
		$this->lexicon_create_roles();
			
		/*
		*	Lexicon Page Creation
		*/
		if(get_page_by_title('LEXICON') == null)
 			{							
    			$post = array ();
   				$post['post_title'] = 'LEXICON';
 				$post['post_type'] = 'page';
			    $post['post_content'] = '[lexicon]';
			    $post['post_status'] = 'publish';
			    $post['post_author'] = 1;
			    $post['comment_status'] = 'closed';
			    $post['ping_satuts'] = 'closed';
			    $post['page_template'] = 'front-page.php';
			    wp_insert_post($post);									
			}
		/*
		*	Lexicon Options
		*/
		
		if (get_option("lexicon_install")=="") {	add_option("lexicon_install", '1'); } else {	update_option("lexicon_install", '1');	}
		if (get_option("lexicon_student_size")=="") {	add_option("lexicon_student_size", '16'); } else {	update_option("lexicon_student_size", '16');	}
		if (get_option("lexicon_order_flashcards")=="") {	add_option("lexicon_order_flashcards", 'basic'); } else {	update_option("lexicon_order_flashcards", 'basic');	}
		if (get_option("lexicon_presentation_flashcards")=="") {	add_option("lexicon_presentation_flashcards", 'buttons'); } else {	update_option("lexicon_presentation_flashcards", 'buttons');	}
		if (get_option("lexicon_perorm_course")=="") {	add_option("lexicon_perorm_course", 'categories'); } else {	update_option("lexicon_perorm_course", 'categories');	}
		if (get_option("lexicon_display_statistics")=="") {	add_option("lexicon_display_statistics", '1'); } else {	update_option("lexicon_display_statistics", '1');	}
		if (get_option("lexicon_algorythm")=="") {	add_option("lexicon_algorythm", '1'); } else {	update_option("lexicon_algorythm", '1');	}
		if (get_option("lexicon_custom_list_pages_default")=="") {	add_option("lexicon_custom_list_pages_default", '8'); } else {	update_option("lexicon_custom_list_pages_default", '8');	}
		//Dev only, set to 0 on release
		if (get_option("lexicon_cleanup_db")=="") {	add_option("lexicon_cleanup_db", '1'); } else {	update_option("lexicon_cleanup_db", '1');	}
		if (get_option("lexicon_clear_data_deactive")=="") {	add_option("lexicon_clear_data_deactive", '1'); } else {	update_option("lexicon_clear_data_deactive", '1');	}
		
		$this->lexicon_load_dev_data();
  
  
}

function lexicon_activation()
{
	
  global $wp_version;
  if ( version_compare( $wp_version, '3.5', '<' ) ){
    wp_die( 'This plugin requires WordPress version 3.5 or higher.' );
}
  if(get_option('lexicon_install') != 1){ //First Activation
    lexicon_install();
    load_plugin_textdomain('lexicon', false, basename(LEXICON_DIR).'/lang/' );
    lexicon_create_page_test();
  }
}
function lexicon_deactivation()
{
global $wpdb;

//  If clean lexicon data is selected in admin panel, clear DB
if(get_option('lexicon_clear_data_deactive') == 1)
{
	define('LEXICON_PSEUDO_UNINSTALL', true);
  include_once(LEXICON_DIR.'/uninstall.php');
	
}
  
}


function lexicon_create_roles()
{
	
			
  $res = add_role('lexicon_student', 'Lexicon Student', array(
  'read' => true, 
  'lexicon_course_enroll' => true, 
  'lexicon_course_study' => true));
		       
  if(!$res) 
  {
    // Assing student privileges
    $role = get_role('lexicon_student');
    if(!$role->has_cap('lexicon_course_enroll')) $role->add_cap('lexicon_course_enroll');
	if(!$role->has_cap('lexicon_course_study')) $role->add_cap('lexicon_course_study');
  } 
	
  $res = add_role('lexicon_teacher', 'Lexicon Teacher', array(
  'read' => true, 
  'lexicon_course_enroll' => true, 
  'lexicon_course_study' => true, 
  'lexicon_edit_course_custom_authorial' => true, 
  'lexicon_create_course_custom' => true));
		     
  if(!$res) 
  {
    // Assign teacher privileges
    $role = get_role('lexicon_teacher');
   if(!$role->has_cap('lexicon_course_enroll')) $role->add_cap('lexicon_course_enroll');
	if(!$role->has_cap('lexicon_course_study')) $role->add_cap('lexicon_course_study');
	if(!$role->has_cap('lexicon_edit_course_custom_authorial')) $role->add_cap('lexicon_edit_course_custom_authorial');
	if(!$role->has_cap('lexicon_create_course_custom')) $role->add_cap('lexicon_create_course_custom');
  }
  
  $res = add_role('lexicon_editor', 'Lexicon Editor', array(
  'read' => true, 
  'lexicon_course_enroll' => true, 
  'lexicon_course_study' => true, 
  'lexicon_edit_course_custom' => true, 
  'lexicon_create_course_custom' => true, 
  'lexicon_edit_course' => true, 
  'lexicon_create_course' => true));
		     
  if(!$res) 
  {
    // Assign editor privileges
    $role = get_role('lexicon_editor');
    if(!$role->has_cap('lexicon_course_enroll')) $role->add_cap('lexicon_course_enroll');
	if(!$role->has_cap('lexicon_course_study')) $role->add_cap('lexicon_course_study');
	if(!$role->has_cap('lexicon_edit_course_custom')) $role->add_cap('lexicon_edit_course_custom');
	if(!$role->has_cap('lexicon_create_course_custom')) $role->add_cap('lexicon_create_course_custom');
	if(!$role->has_cap('lexicon_edit_course')) $role->add_cap('lexicon_edit_course');
	if(!$role->has_cap('lexicon_create_course')) $role->add_cap('lexicon_create_course');
  }
  
  $res = add_role('lexicon_admin', 'Lexicon Admin', array(
  'read' => true, 
  'lexicon_course_enroll' => true, 
  'lexicon_course_study' => true, 
  'lexicon_edit_course_custom' => true, 
  'lexicon_create_course_custom' => true, 
  'lexicon_edit_course' => true, 
  'lexicon_create_course' => true, 
  'lexicon_management' => true));
		     
  if(!$res) 
  {
    // Si existe el rol "profesor" comprobar "capability" y asignarsela
    $role = get_role('lexicon_admin');
    if(!$role->has_cap('lexicon_course_enroll')) $role->add_cap('lexicon_course_enroll');
	if(!$role->has_cap('lexicon_course_study')) $role->add_cap('lexicon_course_study');
	if(!$role->has_cap('lexicon_edit_course_custom')) $role->add_cap('lexicon_edit_course_custom');
	if(!$role->has_cap('lexicon_create_course_custom')) $role->add_cap('lexicon_create_course_custom');
	if(!$role->has_cap('lexicon_edit_course')) $role->add_cap('lexicon_edit_course');
	if(!$role->has_cap('lexicon_create_course')) $role->add_cap('lexicon_create_course');
	if(!$role->has_cap('lexicon_management')) $role->add_cap('lexicon_management');
  }
	
  // Asignar al Administrador roles totales sobre el plugin
  $role = get_role('administrator');
  if(!$role->has_cap('lexicon_course_enroll')) $role->add_cap('lexicon_course_enroll');
	if(!$role->has_cap('lexicon_course_study')) $role->add_cap('lexicon_course_study');
	if(!$role->has_cap('lexicon_edit_course_custom')) $role->add_cap('lexicon_edit_course_custom');
	if(!$role->has_cap('lexicon_create_course_custom')) $role->add_cap('lexicon_create_course_custom');
	if(!$role->has_cap('lexicon_edit_course')) $role->add_cap('lexicon_edit_course');
	if(!$role->has_cap('lexicon_create_course')) $role->add_cap('lexicon_create_course');
	if(!$role->has_cap('lexicon_management')) $role->add_cap('lexicon_management');
  
}
function my_delete_user( $user_id ) {
	global $wpdb;
    $user_obj = get_userdata( $user_id );
	$sql = array();
    $sql[] = "Delete from ".$wpdb->prefix."lexicon_course_student WHERE student_id=".$user_id;
}
/*******************************  DEV ONLY ******************************************/

function lexicon_load_dev_data()
{
 
	//Para cada fichero en el directorio /idioms
  $dir = str_replace("\\","/",LEXICON_DIR) . '/lexicon_languages';
 $this->lexicon_load($dir, 'lang');

  //Para cada fichero en el directorio /courses
  $dir = str_replace("\\","/",LEXICON_DIR) . '/lexicon_courses';
  $this->lexicon_load($dir, 'course');
}

/*function lexicon_check_user()
{
  if ( is_user_logged_in() )
  {
    global $user;
    $user = wp_get_current_user();		
    return $user->ID;
  }	 
}*/



///////////////////////////////////////////////////////////////////////////////////
//  CSV to MySQL database.
///////////////////////////////////////////////////////////////////////////////////

function lexicon_load_lang($dir, $lang_name)
{	
	global $wpdb;
	$absolutepath = $dir . '/' .$lang_name;
	$lang = strstr($lang_name, '-', true);
  	$lang_name = strstr($lang_name, '-');
  	$lang_name = substr($lang_name, 1);
  	$level = strstr($lang_name, '.', true);
	$sqls = array();
	//load file
	$data = file($absolutepath);
	$isFirst = true;
	foreach($data as $line) {
		//Remove last CVC comma & new line
		$line = rtrim($line);
		$line = rtrim($line, ",");
		if ($isFirst) {
        $isFirst = false;
        continue;
    	} 
		$entry_data = explode(';', $line);
		$sqls[] = 'INSERT INTO '._LEXICON_WORDS.'(code, text, phrase, context, column_6, column_7, column_8, column_9, column_10, column_11, lang, level) values ("'.$entry_data[0].'" , "'.$entry_data[1].'" , "'.$entry_data[2].'" , "'.$entry_data[3].'" , "'.$entry_data[4].'" , "'.$entry_data[5].'" , "'.$entry_data[6].'" , "'.$entry_data[7].'" , "'.$entry_data[8].'" , "'.$entry_data[9].'" , "'.$lang.'", "'.$level.'")';
		
	}
			$error = false;
			$wpdb->query('START TRANSACTION');
			foreach($sqls as $sql) {
				
				if(!$wpdb->query($sql)){ $error = true;	break;} 
				if($error) {$wpdb->query('ROLLBACK');} else {$wpdb->query('COMMIT');}
			}
	return true;
}


function lexicon_load_course($dir, $course_name)
{
  global $wpdb;
  $absolutepath = $dir . '/' .$course_name;
  $level = strstr($course_name, '-', true);
  $course_name = strstr($course_name, '-');
  $course_name = substr($course_name, 1);
  $lang = strstr($course_name, '-', true);
  $course_name = strstr($course_name, '-');
  $course_name = substr($course_name, 1);
  $author = strstr($course_name, '.', true);
  $langs = $wpdb->get_results( "SELECT DISTINCT lang FROM "._LEXICON_WORDS." WHERE lang <> '" . $lang . "'");
 
  if(count($langs) > 0)
  {
    $user_id = 1; 

    foreach ($langs as $lang_z)
	  {
		 $sqls = array();
		  $course_id = set_courses($lang,$lang_z->lang,$level, 'A course by '.$author.'');
		  set_course_teacher($user_id, $course_id);
	//load file
	$data = file($absolutepath);
	$isFirst = true;
	foreach($data as $line) {
		//Remove last CVC comma & new line
		$line = rtrim($line);
		$line = rtrim($line, ",");
		if ($isFirst) {
        $isFirst = false;
        continue;
    	} 
		$entry_data = explode(';', $line);
		$sqls[] = 'INSERT INTO '._LEXICON_COURSE_CODES.'(code, context, course_id) values ("'.$entry_data[0].'" , "'.$entry_data[1].'" , "'.$course_id.'")';
		
	}
	  }
	$error = false;
	$wpdb->query('START TRANSACTION');
	foreach($sqls as $sql) {
				
				if(!$wpdb->query($sql)){ $error = true;	break;} 
				if($error) {$wpdb->query('ROLLBACK');} else {$wpdb->query('COMMIT');}
			}
	  }
	return true; 
 }

function lexicon_load($dir, $type)
{
  $directory=opendir($dir); 
  while ($archive = readdir($directory)){
    if($archive != '.' && $archive != '..'){
	switch($type){
		case 'lang':
			$x = $this->lexicon_load_lang($dir, $archive);
			break;
		case 'course':
			$x = $this->lexicon_load_course($dir, $archive);
			break;
		default:
		}
	}
  }
  closedir($directory);
}


///////////////////////////////////////////////////////////////////////////////////
//  Menu Creation
///////////////////////////////////////////////////////////////////////////////////

function lexicon_menu()
{
  add_menu_page('Lexicon', 'Lexicon', 'lexicon_management', 'lexicon_admin_courses',array(&$this, 'lexicon_admin_courses'));
    add_submenu_page('lexicon_admin_courses', 'Lexicon-Courses', 'Courses', 'lexicon_management', 'lexicon_admin_courses',array(&$this, 'lexicon_admin_courses'));
	add_submenu_page('lexicon_admin_courses', 'Lexicon-Lang-Modules', 'Language Modules', 'lexicon_management', 'lexicon_admin_lang_modules',array(&$this, 'lexicon_admin_lang_modules'));
    add_submenu_page('lexicon_admin_courses', 'Lexicon-Teachers', 'Teachers', 'lexicon_management', 'lexicon_admin_teachers',array(&$this, 'lexicon_admin_teachers'));
    add_submenu_page('lexicon_admin_courses', 'Lexicon-Students', 'Students', 'lexicon_management', 'lexicon_admin_students',array(&$this, 'lexicon_admin_students'));
    add_submenu_page('lexicon_admin_courses', 'Lexicon-Options', 'Options', 'lexicon_management', 'lexicon_admin_options',array(&$this, 'lexicon_admin_options'));
    add_submenu_page('lexicon_admin_courses', 'Lexicon-Help', 'Help', 'lexicon_management', 'lexicon_admin_help',array(&$this, 'lexicon_admin_help'));
	
  add_menu_page('Lexicon', 'Lexicon', "lexicon_teacher", 'lexicon_teacher_create_course',array(&$this, 'lexicon_teacher_create_course'));
    add_submenu_page('lexicon_teacher_create_course','Lexicon-Create-Course', 'Create course', 'lexicon_teacher','lexicon_teacher_create_course',array(&$this, 'lexicon_teacher_create_course'));
    add_submenu_page('lexicon_teacher_create_course','Lexicon-My-Courses', 'My courses', 'lexicon_teacher','lexicon_teacher_courses',array(&$this, 'lexicon_teacher_courses'));
    add_submenu_page('lexicon_teacher_create_course', 'Lexicon-Options', 'Options', 'lexicon_teacher', 'lexicon_teacher_options',array(&$this, 'lexicon_teacher_options'));
    add_submenu_page('lexicon_teacher_create_course', 'Lexicon-Help', 'Help', 'lexicon_teacher', 'lexicon_teacher_help',array(&$this, 'lexicon_teacher_help'));

  add_menu_page('Lexicon', 'Lexicon', "lexicon_student", 'lexicon_student_courses',array(&$this, 'lexicon_student_courses'));
    add_submenu_page('lexicon_student_courses','Lexicon-My-Courses', 'My Courses', 'lexicon_student','lexicon_student_courses',array(&$this, 'lexicon_student_courses'));
    add_submenu_page('lexicon_student_courses', 'Lexicon-Options', 'Options', 'lexicon_student', 'lexicon_student_options',array(&$this, 'lexicon_student_options'));
    add_submenu_page('lexicon_student_courses', 'Lexicon-Help', 'Help', 'lexicon_student', 'lexicon_student_help',array(&$this, 'lexicon_student_help'));
}
function lexicon_admin_courses(){
require(ABSPATH. '/wp-content/plugins/lexicon/includes/controllers/lexicon_menu_admin_courses.php');
}
function lexicon_admin_lang_modules(){
require(ABSPATH. '/wp-content/plugins/lexicon/includes/controllers/lexicon_menu_admin_lang_mods.php');
}
function lexicon_admin_teachers(){
require(ABSPATH. '/wp-content/plugins/lexicon/includes/controllers/lexicon_menu_admin_teachers.php');
}
function lexicon_admin_students()
{
  require(ABSPATH. '/wp-content/plugins/lexicon/includes/controllers/lexicon_menu_admin_students.php');
}

function lexicon_admin_options()
{	
  require(ABSPATH. '/wp-content/plugins/lexicon/includes/controllers/lexicon_menu_admin_options.php');	
}

function lexicon_admin_help()
{
   require(ABSPATH. '/wp-content/plugins/lexicon/includes/controllers/lexicon_menu_admin_help.php');
}



function lexicon_student_options()
{
  require(ABSPATH. '/wp-content/plugins/lexicon/includes/controllers/lexicon_menu_students_options.php'); 
}


///////////////////////////////////////////////////////////////////////////////////
//  Lexicon shortcodes
///////////////////////////////////////////////////////////////////////////////////

function lexicon_init_shortcode() // Init Lexicon
{
  if(!is_user_logged_in()) // If user is not logged in
  { 
    global $post; 
	echo "<div><p><b>";
	echo _e('You need to be registered and logged in to see this.', 'lexicon')."</b> <a href='".site_url("/wp-login.php?redirect_to=".urlencode(get_permalink( $post->ID )))."'>";
	echo _e('Log in', 'lexicon')."</a></p></div>";
    return false;
  }

  $user =  wp_get_current_user();
  $course_base = lexicon_get_course_base();
  // URI handler
 $course = false; // Contains course ISO codes f.e. 'ENGFRA'
 $level = false; // Containes course difficulty
 $context = false; // Containes course module
 if(!isset($_GET['SignUpCourse']) && !isset($_GET['EditCourse']) && !isset($_GET['CreateCourse'])) {
	$uri = $_SERVER['REQUEST_URI']; // Get URI
	if( strpos($uri, "?")) { // Check if '?' is present in URI
		$v = strstr($uri, '?', false); // If it is, break URI
		if(strpos($v, "/")) {	// If it contains course code
		$vars = explode("/", $v); // Assign vars
		$course = substr($vars[0],1);
		$level = $vars[1];
		if( isset($vars[2]) ) {
			$context = $vars[2];
		}
		}
	}
	
	
	//Logic if no URI present: show selection page
 }
	if(!$course && !isset($_GET['SignUpCourse']) && !isset($_GET['EditCourse']) && !isset($_GET['CreateCourse'])) {
		//If user is at least student
		$courseUser = get_course_user($user->ID); //getUser
		if(current_user_can('lexicon_course_enroll')){
			
		?>
<div>
  <div class="head" id="course_enrollCont" style="cursor:pointer;">
    <p>
      <?php _e('Enroll or continue course', 'lexicon')?>
    </p>
  </div>
</div>
<div id="course_enrollCont_cont" style="margin-bottom:10px; margin-top:10px; text-align:center; display:none;   max-width:90%; margin: 0 auto;">
  <?php  require(ABSPATH. '/wp-content/plugins/lexicon/includes/lexicon_page_courses.php'); ?>
</div>
<script>
jQuery(document).ready(function()
{
jQuery('#course_enrollCont').live('click', function(event)
  {
    jQuery('#course_enrollCont_cont').toggle('show');
    jQuery('#course_manage_cont:visible').hide(300);
    
  });
});
</script>
<?php } 

		if(current_user_can('lexicon_edit_course') || current_user_can('lexicon_edit_course_custom_authorial')) {// If current user is at least a teacher
?>
<div>
  <div class="head" id="course_manage" style="cursor:pointer;">
    <p >
      <?php _e('Manage Courses', 'lexicon')?>
    </p>
  </div>
</div>
<div id="course_manage_cont" style="margin-bottom:10px; margin-top:10px; text-align:center; display:none;   max-width:90%; margin: 0 auto;">
  <?php  require(ABSPATH. '/wp-content/plugins/lexicon/includes/lexicon_page_course_management.php'); //display course creation page ?>
</div>
<script>
jQuery(document).ready(function()
{
jQuery('#course_manage').live('click', function(event)
  {
    jQuery('#course_manage_cont').toggle('show');
    jQuery('#course_enrollCont_cont:visible').hide(300);
    
  });
});
</script>
<?php
	}
	
} elseif (isset($_GET['SignUpCourse'])) { // if enroll in course is selected
	 require(ABSPATH. '/wp-content/plugins/lexicon/includes/lexicon_page_sign_up_course.php'); //display sing up course page
} elseif (isset($_GET['EditCourse'])) {
	require(ABSPATH. '/wp-content/plugins/lexicon/includes/lexicon_page_edit_course.php'); //display sing up course page
} elseif (isset($_GET['CreateCourse'])) {
	require(ABSPATH. '/wp-content/plugins/lexicon/includes/lexicon_page_create_course.php'); //display sing up course page
} elseif($course) { 

// if course is present in URI
	
	if($context) { // if courses' section is present in URI
          require(ABSPATH. '/wp-content/plugins/lexicon/includes/lexicon_page_study_course.php'); //display course study page
	} else
        {
          if(get_option('lexicon_realizar_curso') == "completo") { //
            require(ABSPATH. '/wp-content/plugins/lexicon/includes/lexicon_page_study_course.php');
		  } else {
            require(ABSPATH. '/wp-content/plugins/lexicon/includes/lexicon_page_course.php'); //display course main page
		  } }
	}
 
}

/*function lexixon_curso()
{
  $user =  wp_get_current_user();
  
  //$IDPagCurso = get_the_ID();
  //$curso = get_cursoByCode(get_the_title($IDPagCurso)); // esto referencia a las funciones que tengo creadas en ( _lexicon_function.php )
  //$estado = get_estu_curso_card($CurrentUser,$curso->ID);

  if(current_user_can('lexicon_estudiante') || current_user_can('lexicon_admin')) //Esto último habría que quitarlo y solo dejar estudiante.
    require(ABSPATH. '/wp-content/plugins/lexicon/includes/_lexicon_pagina_cursar_curso.php');

}*/



//  CSS style neccessary for proper display of the plugin
function lexicon_add_stylesheet()
{
  $styleUrl = plugins_url('css/lexicon_style.css', __FILE__); 
	$styleFile = WP_PLUGIN_DIR . '/lexicon/css/lexicon_style.css';

	if ( file_exists($styleFile) ) 
  {
    wp_register_style('lexicon_style', $styleUrl, array(), '' , 'screen');
    wp_enqueue_style( 'lexicon_style');
  }
}

//  If you want to use ajax and want to get organized in just one file, it is created and associated with wordpress NOW NOT IN USE
function lexicon_add_javascript()
{
	$scriptUrl = plugins_url('js/ajax.js',__FILE__);
	$scriptFile = WP_PLUGIN_DIR . '/lexicon/js/ajax.js';

  if ( file_exists($scriptFile) )
  {
    wp_register_script('ajax', plugins_url('js/ajax.js',__FILE__), array('jquery'), '1.0', true);
    wp_enqueue_script('ajax');
   }	
}

//  Necessary canvas script
function lexicon_add_canvas()
{
	$scriptUrl_2 = plugins_url('js/canvasjs.min.js',__FILE__);
	$scriptFile_2 = WP_PLUGIN_DIR . '/lexicon/js/canvasjs.min.js';

  if ( file_exists($scriptFile_2) )
  {
    wp_register_script('canvas', plugins_url('js/canvasjs.min.js',__FILE__));
    wp_enqueue_script('canvas');
  }	
}


}

$myLexicon = new Lexicon();
?>
