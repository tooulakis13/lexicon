
<html>
<head>    
<script type="text/javascript">
</script>
<body>
<div class="head"> <p style="height:10%;">LEXICON - <?php _e('Enrolled courses', 'lexicon')?></p> </div> 
    <div>
     <?php global $wpdb; $h ="location.href='".get_permalink(get_the_ID())."?SignUpCourse'";?>
     <div class="list" onClick="<?=$h?>">                     
     <p><?php _e('Start new course:', 'lexicon') ?></p></div>

   	<?php foreach ($courseUser as $courseU){
			
			$ID = $courseU->course_id;							
			$course = get_courseById($ID);
			$href = "location.href='".get_permalink()."?{$course->lang_2}{$course->lang_1}/{$course->level}'";						
			?>                                                    
            <div class="list" onClick="<?=$href?>">
                <p> <?php echo $course->lang_2?>  ---  <?php echo $course->lang_1?>  ---  <?php echo $course->level?>  --- <?php echo $course->description?></p></div>
        <?php }?>
    
    </div>               
</body>
</head>
</html>