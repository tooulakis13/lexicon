<?php
/*
 *	MLS Lexicon uninstallation
 *	Remove plugin options, post pages, user metadata and/or courses data
 *	@package MLS Lexicon
*/

if(defined('WP_UNINSTALL_PLUGIN') or defined('mls_lexicon_PSEUDO_UNINSTALL')) {
} else {
	exit();
}
global $wpdb;
//  Check for DB deletion option
if(get_option('mls_lexicon_cleanup_db') == 1)
{
  // Tables to be removed
  $wpdb->query("SET FOREIGN_KEY_CHECKS=0;");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mls_lexicon_lang_mod");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mls_lexicon_course");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mls_lexicon_course_student");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mls_lexicon_course_student_card");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mls_lexicon_course_author");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mls_lexicon_course_codes");
   $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mls_lexicon_categories_img");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mls_lexicon_categories_trans");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mls_lexicon_categories");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mls_lexicon_codes_img");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mls_lexicon_codes");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mls_lexicon_words");
  $wpdb->query("SET FOREIGN_KEY_CHECKS=1;");
  // Procedures to be removed
  $wpdb->query("DROP PROCEDURE IF EXISTS dropForeignKeysFromTable");
  // Triggers to be removed
  $wpdb->query("DROP TRIGGER IF exists mls_lexicon_teacher_delete;");
  //mls_lexicon page is deleted
  $wpdb->query("DELETE FROM {$wpdb->prefix}posts WHERE post_name = 'mls_lexicon'");
   delete_option('mls_lexicon_db_version');
}
// Removing pages
if(get_option('mls_lexicon_delete_page') == 1) {
	$res_p = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_content like '%[mls_lexicon]%' AND post_status = 'publish'");
	foreach($res_p as $p) {
		wp_delete_post($p->ID);
	}
}


  //Removing options
  delete_option('mls_lexicon_install');
  delete_option('mls_lexicon_cleanup_db');
  delete_option("mls_lexicon_delete_page");
  delete_option('mls_lexicon_student_size');
  delete_option('mls_lexicon_order_flashcards');
  delete_option('mls_lexicon_presentation_flashcards');
  delete_option('mls_lexicon_perorm_course');
  delete_option('mls_lexicon_display_statistics');
  delete_option('mls_lexicon_algorythm');
  delete_option('mls_lexicon_clear_data_deactive');  
  delete_option('mls_lexicon_custom_list_pages_default');
  
  //Removing users meta
  delete_metadata( 'user', 0, 'mls_lexicon_custom_list_pages', '', true);
  
  
  // Capabilities removal
  
   // roles
   	$roles = array (
		'mls_lexicon_student',
		'mls_lexicon_teacher',
		'mls_lexicon_editor',
		'mls_lexicon_admin',
	);
    // A list of capabilities
    $caps = array(
        'mls_lexicon_course_enroll',
		'mls_lexicon_course_study',
		'mls_lexicon_create_course',
		'mls_lexicon_create_course_custom',
		'mls_lexicon_edit_course',
		'mls_lexicon_edit_course_custom',
		'mls_lexicon_edit_course_custom_authorial',
		'mls_lexicon_management',
    );
	
	 foreach ( $roles as $role ) {
		 $r = get_role($role);
		 if($r) {
		 foreach($caps as $cap) {
    
        // Remove the capability.
		
        $r->remove_cap( $cap );
		 }
		 }
    }
	
	// Roles removal
  remove_role('mls_lexicon_student');
  remove_role('mls_lexicon_teacher');
  remove_role('mls_lexicon_editor');
  remove_role('mls_lexicon_admin');


?>