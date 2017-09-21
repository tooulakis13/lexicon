<?php

/* 
 * MLS Lexicon UI init
 */
 // [mls_lexicon list="att" data-selector="att2"]
function mls_lexicon_init_shortcode($atts, $content = null) // $atts are used to determine if plugin should show specific list of data
{
	// Acceptable data: list: course
// data-selector: all, 3-letter language code - comma separated (ENG,SPA,RUS...etc.)
$sc_data = shortcode_atts(array(
'list' => 'none',
'data_selector' => 'all',
), $atts);
  if(!is_user_logged_in()) // If user is not logged in
  { 
    global $post; 
	echo "<div class='textwidget'><p><b>";
	echo _e('You need to be registered and logged in to see this.', 'mls_lexicon')."</b> <a href='".site_url("/wp-login.php?redirect_to=".urlencode(get_permalink( $post->ID )))."'>";
	echo _e('Log in', 'mls_lexicon')."</a></p></div>";
    return false;
  }
  ?>
  <div class="mls-lexicon-cont">
  <?php
	global $wpdb;
  $user =  wp_get_current_user();
  if($sc_data['list'] == 'none') {
  $course_base = mls_lexicon_get_course_base();
  // URI handler
 $course = false; // Contains course ID
 $level = false; // Containes course difficulty
  if(!isset($_GET['SignUpCourse']) && !isset($_GET['EditCourse']) && !isset($_GET['CreateCourse']) && !isset($_GET['course'])) {
	
	
	//Logic if no URI present: show selection page
 }
	if(!isset($_GET['course']) && !isset($_GET['SignUpCourse']) && !isset($_GET['EditCourse']) && !isset($_GET['CreateCourse'])) {
		//If user is at least student
		$courseUser = get_course_user($user->ID); //getUser
		if(current_user_can('mls_lexicon_course_enroll')){
			
		?>
	<h1 class="mls-lex-center">MLS Lexicon <?php _e('Vocabulary Learning Tool ', 'mls_lexicon')?></h1>
  <h2 class="mls-lex-pointer mls-lex-center" id="course_enrollCont">

      <?php _e('Enroll or continue course', 'mls_lexicon')?>
  
</h2>
<h3 class="ls-lex-pointer mls-lex-center" id="course_enrollCont_cont" style="display:none;">
  <?php  require(ABSPATH. '/wp-content/plugins/mls_lexicon/includes/mls_lexicon_page_courses.php'); ?>
</h3>
  
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

		
	
} elseif (isset($_GET['SignUpCourse'])) { // if enroll in course is selected
	 require(ABSPATH. '/wp-content/plugins/mls_lexicon/includes/mls_lexicon_page_sign_up_course.php'); //display sing up course page
}  elseif(isset($_GET['course'])) { 
	$course_state = $wpdb->get_var("SELECT state FROM {$wpdb->prefix}mls_lexicon_course_student WHERE course_id={$_GET['course']} AND student_id={$user->ID}");
          if($course_state == 0 && isset($_GET['category'])) { //
            require(ABSPATH. '/wp-content/plugins/mls_lexicon/includes/mls_lexicon_page_study_course.php');
		  } else {
            require(ABSPATH. '/wp-content/plugins/mls_lexicon/includes/mls_lexicon_page_course.php'); //display course main page
		  } 
		 }

} else { // Handle Lexicon Lists
	require(ABSPATH. '/wp-content/plugins/mls_lexicon/includes/mls_lexicon_page_lists.php'); //display course main page
	
}
}
?>
</div>