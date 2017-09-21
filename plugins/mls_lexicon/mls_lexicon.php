<?php
/**
 * Plugin Name: MLS Lexicon
 * Plugin URI: http://www.linkedin.com/pub/toni-devis-lopez/40/249/693
 * Description: Plugin to learn vocabulary in different languages.
 * Version: 0.2
 * Author: My Language Skills SLU
 * Author URI: http://www.on-lingua.com
 * Text Domain: mls_lexicon
 * Domain Path: /lang/
 * Network: true
 * License: GPLv2 or later
 */
 
 /*  Copyright 2015  Patryk Miroslaw  (email : miroslaw.patryk@gmail.com)

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
define('mls_lexicon_DIR', dirname( __FILE__ ) );
define('mls_lexicon_DIR_RELATIVO', dirname( plugin_basename( __FILE__ )));
define('mls_lexicon_URL', plugin_dir_url( __FILE__ ));

//Links to necessary files
require_once(mls_lexicon_DIR.'/includes/controllers/mls_lexicon_functions.php');
require_once(mls_lexicon_DIR.'/includes/controllers/mls_lexicon_ajax.php');
require_once(mls_lexicon_DIR.'/includes/controllers/mls_lexicon_install.php');
include_once(mls_lexicon_DIR.'/includes/mls_lexicon_ui.php');

class mls_lexicon {
	function mls_lexicon() {
		if(isset($this)) {
			//  Actions
			add_action('admin_menu', array(&$this, 'mls_lexicon_menu'));
			add_action('activate_mls_lexicon/mls_lexicon.php', 'mls_lexicon_install');
			add_action('admin_post_createCourse', 'admin_post_createCourse' );
			add_action('admin_enqueue_scripts', array(&$this, 'mls_lexicon_add_stylesheet'));
			add_action('admin_enqueue_scripts', array(&$this, 'mls_lexicon_add_javascript'));
			add_action('admin_enqueue_scripts', array(&$this, 'mls_lexicon_add_canvas'));
			add_action('wp_print_styles', array(&$this, 'mls_lexicon_add_stylesheet'));
			add_action('wp_print_scripts', array(&$this, 'mls_lexicon_add_javascript'));
			add_action('wp_print_scripts', array(&$this, 'mls_lexicon_add_canvas'));
			add_action('wp_ajax_my_action_one', 'mls_lexicon_my_action_callback');   //this line is for logged in users
			add_action('wp_ajax_admin_submits', 'mls_lexicon_admin_submits');   // Handles for admin submits
			add_action('wp_ajax_admin_data', 'mls_lexicon_admin_data');   // Handles for admin file upload
			add_action('wp_ajax_nopriv_my_action_one', 'mls_lexicon_my_action_callback'); // this is for not logged in users
			add_action('wp_ajax_nopriv_admin_submits', 'mls_lexicon_admin_submits');  
			add_action('wp_ajax_nopriv_admin_data', 'mls_lexicon_admin_data');   
			add_action( 'delete_user', array(&$this, 'mls_lexicon_delete_user') );
			register_activation_hook(__FILE__,array(&$this, 'mls_lexicon_activation'));
			register_deactivation_hook(__FILE__,array(&$this, 'mls_lexicon_deactivation'));
			add_shortcode('mls_lexicon', 'mls_lexicon_init_shortcode'); //ShortCode necessary to ininiate the lexion page
		}
	}



function mls_lexicon_activation()
{
	
  global $wp_version;
  if ( version_compare( $wp_version, '3.5', '<' ) ){
    wp_die( 'This plugin requires WordPress version 3.5 or higher.' );
}
  if(get_option('mls_lexicon_install') != 1){ //First Activation
   mls_lexicon_install();
   load_plugin_textdomain('mls_lexicon', false, basename(mls_lexicon_DIR).'/lang/' );
  }
}
function mls_lexicon_deactivation()
{
global $wpdb;

//  If clean mls_lexicon data is selected in admin panel, clear DB
if(get_option('mls_lexicon_clear_data_deactive') == 1)
{
	define('mls_lexicon_PSEUDO_UNINSTALL', true);
  include_once(mls_lexicon_DIR.'/uninstall.php');
	
}
  
}

/*
 * MLS Lexicon Menu
 */

function mls_lexicon_menu()
{
  add_menu_page('mls_lexicon', 'MLS Lexicon', 'mls_lexicon_management', 'mls_lexicon_admin_courses',array(&$this, 'mls_lexicon_admin_courses'));
    add_submenu_page('mls_lexicon_admin_courses', 'MLS Lexicon Courses', 'Courses', 'mls_lexicon_management', 'mls_lexicon_admin_courses',array(&$this, 'mls_lexicon_admin_courses'));
	add_submenu_page('mls_lexicon_admin_courses', 'MLS Lexicon Lang-Modules', 'Language Modules', 'mls_lexicon_management', 'mls_lexicon_admin_lang_modules',array(&$this, 'mls_lexicon_admin_lang_modules'));
	add_submenu_page('mls_lexicon_admin_courses', 'MLS Lexicon Editors', 'Editors', 'mls_lexicon_management', 'mls_lexicon_admin_editors',array(&$this, 'mls_lexicon_admin_editors'));
    add_submenu_page('mls_lexicon_admin_courses', 'MLS Lexicon Teachers', 'Teachers', 'mls_lexicon_management', 'mls_lexicon_admin_teachers',array(&$this, 'mls_lexicon_admin_teachers'));
    add_submenu_page('mls_lexicon_admin_courses', 'MLS Lexicon Students', 'Students', 'mls_lexicon_management', 'mls_lexicon_admin_students',array(&$this, 'mls_lexicon_admin_students'));
    add_submenu_page('mls_lexicon_admin_courses', 'MLS Lexicon Options', 'Options', 'mls_lexicon_management', 'mls_lexicon_admin_options',array(&$this, 'mls_lexicon_admin_options'));
    add_submenu_page('mls_lexicon_admin_courses', 'MLS Lexicon Help', 'Help', 'mls_lexicon_management', 'mls_lexicon_admin_help',array(&$this, 'mls_lexicon_admin_help'));
	add_submenu_page('mls_lexicon_admin_courses', 'MLS Lexicon Data Import/Export', 'Data Import/Export', 'mls_lexicon_management', 'mls_lexicon_admin_data',array(&$this, 'mls_lexicon_admin_data'));
	
  add_menu_page('mls_lexicon', 'MLS Lexicon', "mls_lexicon_teacher", 'mls_lexicon_teacher_create_course',array(&$this, 'mls_lexicon_teacher_create_course'));
    add_submenu_page('mls_lexicon_teacher_create_course','MLS Lexicon Create Course', 'Create course', 'mls_lexicon_teacher','mls_lexicon_teacher_create_course',array(&$this, 'mls_lexicon_teacher_create_course'));
    add_submenu_page('mls_lexicon_teacher_create_course','MLS Lexicon My Courses', 'My courses', 'mls_lexicon_teacher','mls_lexicon_teacher_courses',array(&$this, 'mls_lexicon_teacher_courses'));
    add_submenu_page('mls_lexicon_teacher_create_course', 'MLS Lexicon Options', 'Options', 'mls_lexicon_teacher', 'mls_lexicon_teacher_options',array(&$this, 'mls_lexicon_teacher_options'));
    add_submenu_page('mls_lexicon_teacher_create_course', 'MLS Lexicon Help', 'Help', 'mls_lexicon_teacher', 'mls_lexicon_teacher_help',array(&$this, 'mls_lexicon_teacher_help'));

  add_menu_page('mls_lexicon', 'MLS Lexicon', "mls_lexicon_student", 'mls_lexicon_student_courses',array(&$this, 'mls_lexicon_student_courses'));
    add_submenu_page('mls_lexicon_student_courses','MLS Lexicon My Courses', 'My Courses', 'mls_lexicon_student','mls_lexicon_student_courses',array(&$this, 'mls_lexicon_student_courses'));
    add_submenu_page('mls_lexicon_student_courses', 'MLS Lexicon Options', 'Options', 'mls_lexicon_student', 'mls_lexicon_student_options',array(&$this, 'mls_lexicon_student_options'));
    add_submenu_page('mls_lexicon_student_courses', 'MLS Lexicon Help', 'Help', 'mls_lexicon_student', 'mls_lexicon_student_help',array(&$this, 'mls_lexicon_student_help'));
}
function mls_lexicon_admin_courses(){
require(ABSPATH. '/wp-content/plugins/mls_lexicon/includes/controllers/mls_lexicon_menu_admin_courses.php');
}
function mls_lexicon_admin_lang_modules(){
require(ABSPATH. '/wp-content/plugins/mls_lexicon/includes/controllers/mls_lexicon_menu_admin_lang_mods.php');
}
function mls_lexicon_admin_editors(){
require(ABSPATH. '/wp-content/plugins/mls_lexicon/includes/controllers/mls_lexicon_menu_admin_editors.php');
}
function mls_lexicon_admin_teachers(){
require(ABSPATH. '/wp-content/plugins/mls_lexicon/includes/controllers/mls_lexicon_menu_admin_teachers.php');
}
function mls_lexicon_admin_students()
{
  require(ABSPATH. '/wp-content/plugins/mls_lexicon/includes/controllers/mls_lexicon_menu_admin_students.php');
}

function mls_lexicon_admin_options()
{	
  require(ABSPATH. '/wp-content/plugins/mls_lexicon/includes/controllers/mls_lexicon_menu_admin_options.php');	
}

function mls_lexicon_admin_help()
{
   require(ABSPATH. '/wp-content/plugins/mls_lexicon/includes/controllers/mls_lexicon_menu_admin_help.php');
}

function mls_lexicon_admin_data()
{
   require(ABSPATH. '/wp-content/plugins/mls_lexicon/includes/controllers/mls_lexicon_menu_admin_data.php');
}

function mls_lexicon_student_options()
{
  require(ABSPATH. '/wp-content/plugins/mls_lexicon/includes/controllers/mls_lexicon_menu_students_options.php'); 
}



//  CSS style neccessary for proper display of the plugin
function mls_lexicon_add_stylesheet()
{
  $styleUrl = plugins_url('css/mls_lexicon_style.css', __FILE__); 
	$styleFile = WP_PLUGIN_DIR . '/mls_lexicon/css/mls_lexicon_style.css';

	if ( file_exists($styleFile) ) 
  {
    wp_register_style('mls_lexicon_style', $styleUrl, array(), '' , 'screen');
    wp_enqueue_style( 'mls_lexicon_style');
  }
}

//  If you want to use ajax and want to get organized in just one file, it is created and associated with wordpress NOW NOT IN USE
function mls_lexicon_add_javascript()
{
	$scriptUrl = plugins_url('js/ajax.js',__FILE__);
	$scriptFile = WP_PLUGIN_DIR . '/mls_lexicon/js/ajax.js';

  if ( file_exists($scriptFile) )
  {
    wp_register_script('ajax', plugins_url('js/ajax.js',__FILE__), array('jquery'), '1.0', true);
    wp_enqueue_script('ajax');
   }	
}

//  Necessary canvas script
function mls_lexicon_add_canvas()
{
	$scriptUrl_2 = plugins_url('js/canvasjs.min.js',__FILE__);
	$scriptFile_2 = WP_PLUGIN_DIR . '/mls_lexicon/js/canvasjs.min.js';

  if ( file_exists($scriptFile_2) )
  {
    wp_register_script('canvas', plugins_url('js/canvasjs.min.js',__FILE__));
    wp_enqueue_script('canvas');
  }	
}


}

$mymls_lexicon = new mls_lexicon();
?>
