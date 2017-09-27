<?php
// Create list of editable courses   || MODIFY WHEN CUSTOM COURSES DONE
$normal_courses;
$custom_courses;
$page_insert;



if(current_user_can('lexicon_edit_course')) {  // Editor level
	$normal_courses = get_official_courses();
	foreach($normal_courses as $r) {
	
	}
}



?>



<html>
<head>
<script>
jQuery(document).ready(function()
{

  jQuery("#course_edit").click(function(event)
  {
    
	window.location = '<?php echo get_permalink(get_the_ID()); ?>'+'?EditCourse';
  

  });





});
</script>
</head>
<body>
<?php if(current_user_can('lexicon_edit_course')) {
	?>
<!-- COURSE EDITION -->
<div>
  <div class="head" id="course_edit" style="cursor:pointer;">
    <p>
      <?php _e('Edit Courses', 'lexicon')?>
    </p>
  </div>
</div>
<!-- END OF COURSE EDITION -->
<?php 
}
if(current_user_can('lexicon_create_course')) {
?>
<!-- CREATE COURSE -->
<div>
  <div class="head" id="course_create" style="cursor:pointer;">
    <p>
      <?php _e('Create Course', 'lexicon')?>
    </p>
  </div>
</div>
<!-- END OF CREATE COURSE -->
<?php }
if(current_user_can('lexicon_edit_course_custom') || current_user_can('lexicon_edit_course_custom_authorial')) {

?>
<!-- CUSTOM COURSE EDITION -->
<div>
  <div class="head" id="course_edit" style="cursor:pointer;">
    <p>
      <?php _e('Edit Custom Courses', 'lexicon')?>
    </p>
  </div>
</div>
<!-- END OF CUSTOM COURSE EDITION -->
<?php
}
if(current_user_can('lexicon_create_course_custom')) {

?>
<!-- CREATE CUSTOM COURSE -->
<div>
  <div class="head" id="course_create" style="cursor:pointer;">
    <p>
      <?php _e('Create Custom Course', 'lexicon')?>
    </p>
  </div>
</div>
<!-- END OF CREATE CUSTOM COURSE -->

<?php
}
?>





