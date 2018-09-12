<?php
/*
  Plugin URI: http://www.linkedin.com/pub/toni-devis-lopez/40/249/693
  Author: MY LANGUAGE SKILLS S.L.U
  Description: Eliminación del plugin lexicon.
*/

if(defined('WP_UNINSTALL_PLUGIN') or defined('TESTINGV1_PSEUDO_UNINSTALL')) {
  
} else {
	exit();
}
global $wpdb;

//  If clean lexicon data is selected in admin panel, clear DB
if(get_option('testingv1_cleanup_db') == 1)
{
  // Tables to be removed
  $wpdb->query("SET FOREIGN_KEY_CHECKS=0;");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}lexicon_course");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}lexicon_course_student");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}lexicon_course_student_card");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}lexicon_course_author");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}lexicon_course_codes");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}lexicon_words");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}_lexicon_idioma");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}lexicon_word_code");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}lexicon_word_details");
   $wpdb->query("SET FOREIGN_KEY_CHECKS=1;");

  //Lexicon page is deleted
  $wpdb->query("DELETE FROM {$wpdb->prefix}posts WHERE post_name = 'lexicon'");

  //Removing options
  delete_option('lexicon_install');
  delete_option('lexicon_cleanup_db');
  delete_option('lexicon_student_size');
  delete_option('lexicon_order_flashcards');
  delete_option('lexicon_presentation_flashcards');
  delete_option('lexicon_perorm_course');
  delete_option('lexicon_display_statistics');
  delete_option('lexicon_algorythm');
  delete_option('lexicon_db_version');
  delete_option('lexicon_clear_data_deactive');  
  delete_option('lexicon_custom_list_pages_default');
  
  //Removing user meta
  delete_metadata( 'user', 0, 'lexicon_custom_list_pages', '', true);
  
  
  // Capabilities removal
  
   // roles
   	$roles = array (
		'lexicon_student',
		'lexicon_teacher',
		'lexicon_editor',
		'lexicon_admin',
	);
    // A list of capabilities
    $caps = array(
        'lexicon_course_enroll',
		'lexicon_course_study',
		'lexicon_create_course',
		'lexicon_create_course_custom',
		'lexicon_edit_course',
		'lexicon_edit_course_custom',
		'lexicon_edit_course_custom_authorial',
		'lexicon_management',
    );
	
	 foreach ( $roles as $role ) {
		 
		 $r = get_role($role);
		 foreach($caps as $cap) {
    
        // Remove the capability.
		
        //$r->remove_cap( $cap );
		 }
    }
	
	// Roles removal
  remove_role('lexicon_student');
  remove_role('lexicon_teacher');
  remove_role('lexicon_editor');
  remove_role('lexicon_admin');
}

?>
