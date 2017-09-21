<script type="text/javascript">
var ajaxurl= '<?php echo admin_url("admin-ajax.php"); ?>';
var value = 0;

jQuery(document).ready(function()
{
  jQuery("#course-target-select").change(function(event) // Course selection handel. If first option is selected...
  {
    jQuery("#sign").hide('slow');
    var cTarget = jQuery("#course-target-select").find(':selected').val();
    value = 1;
    jQuery("#course-base-select").empty();

    //...find available courses....
    var data = {action: 'my_action_one',  cTarget: cTarget, func: value};
    
    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data, function(response) //...insert available courses as selection
    {
      var obj = jQuery.parseJSON(response);
      var i;
      jQuery("#course-base-select").append("<option>Select language</option>");
      for(i=0; i<obj.length; i++)
	    jQuery("#course-base-select").append(obj[i]);
    });

  });

  jQuery("#course-base-select").change(function(event)// When course languages are selected...
  {
    jQuery("#sign").hide('slow');
    var cBase = jQuery("#course-base-select").find(':selected').val();
    var cTarget = jQuery("#course-target-select").find(':selected').val();
    value = 2;
    
    jQuery("#course-level-select").empty();

    // Set up data
    var data = {action: 'my_action_one',  cBase: cBase, cTarget: cTarget, func: value};
    
    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data, function(response)//...show course levels available
    {
      var obj = jQuery.parseJSON(response);
      var i;
      jQuery("#course-level-select").append("<option>Select level</option>");
      for(i=0; i<obj.length; i++)
	    jQuery("#course-level-select").append(obj[i]);
    });
    
  });

  jQuery("#course-level-select").change(function(event) // When course level is selected...
  {
    if(jQuery("#course-level-select").find(':selected').val()=="Select level") 
      jQuery("#sign").hide('slow');
    else
      jQuery("#sign").show('slow'); //...show subscription button
  });
  
  jQuery("#course-enroll").click(function() // Sing up for course
  {
    var cBase = jQuery("#course-base-select").find(':selected').val();
    var cTarget = jQuery("#course-target-select").find(':selected').val();
    var cLevel = jQuery("#course-level-select").find(':selected').val();
    value = 3;

    /*jQuery("#courses").load(url+'?cursBase='+cursBase+'&cursDest='+cursDest+'&curslvl='+curslvl+'&func='+value+'');*/

    //The name for the action is the first parameter
    var data = {action: 'my_action_one',  cBase: cBase, cTarget: cTarget, cLevel: cLevel, func: value};
    
    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data, function(response)
    {
      var obj = jQuery.parseJSON(response);
      if(obj[0] == 1) 
	  <?php 
	  if (sizeOf($_GET) <= 1 ) { $split = '?'; } else { $split = '&'; }
	  ?>      
        window.location = window.location.href.split("<?php echo $split; ?>")[0];
      if(obj[0] == 0)
      	alert("Already enrolled in this course.");
    });

  });					 
	
});
</script>
<div class="mls-lex-center"><h1><p><?php _e('Enroll', 'mls_lexicon')?></p></h1>
<ul class="inline">			 
<li id="course-target">
  <p><?php _e('Course to learn:', 'mls_lexicon')?></p>
    <select id="course-target-select" name="course-target-select" class="langs"> 
    <option value=""><?php _e('Select language', 'mls_lexicon')?></option>              
      <?php 
        foreach ($course_base as $course){?>							
          <option value="<?php echo $course->lang_1?>"><?php echo $course->lang_1?></option>
      <?php }?>		               
    </select>    	
</li>
<li id="course-base">
  <p><?php _e('Course Language:', 'mls_lexicon')?></p>							
    <select id="course-base-select" name="course-base-select" class="langs">   
							    
    </select>  
</li>

<li id="course-level">        	
  <p><?php _e('Level:', 'mls_lexicon')?></p>
    <select id="course-level-select" name="course-level-select" class="langs">
    </select>
</li>
</ul>
<div>
<div class="mls-lex-center" id="sign" style="display: none;">
  <input id="course-enroll" type="button" value="<?php _e('Enroll', 'mls_lexicon')?>"></input> 
</div>