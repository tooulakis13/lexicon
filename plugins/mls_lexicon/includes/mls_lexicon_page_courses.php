<?php global $wpdb; 
	 $switchsym = '?';
	if(sizeOf($_GET) > 0 ) { $switchsym = '&'; }
	 $h ="location.href='".get_permalink(get_the_ID()).$switchsym."SignUpCourse'";?>
<div>
<h3>
<a class="list" onClick="<?=$h?>">                     
     <p><?php _e('Start new course', 'mls_lexicon') ?></p></a>
     </h3>  
     </div>   
<div class="mls-lex-center"> 
<p><?php _e('Enrolled courses', 'mls_lexicon')?>:</p>


   	<?php 
	
	
	foreach ($courseUser as $courseU){
			
			$ID = $courseU->course_id;							
			$course = get_courseById($ID);
			$href = "location.href='".get_permalink().$switchsym."course={$course->id}'";						
			?>                                                    
            <a class="list" onClick="<?=$href?>">
                <p> <?php echo $course->lang_1?>  ---  <?php echo $course->lang_2?>  ---  <?php echo $course->level?>  --- <?php echo $course->description?></p></a>
        <?php }?>
    
                 

</div> 