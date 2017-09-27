<?php

/*

  Description: Course study page

*/

global $wpdb;
$user = wp_get_current_user(); // get current user
$courseID = $_GET['course'];
$course_cat_sql = $wpdb->prepare("SELECT DISTINCT code FROM {$wpdb->prefix}mls_lexicon_course_codes WHERE course_id=%d", $courseID);
$course_categories = $wpdb->get_results($course_cat_sql);
$course_data = get_courseById($courseID);
$context_sql = $wpdb->prepare("SELECT CONCAT(notion_type, class, subclass, mgroup, subgroup) FROM {$wpdb->prefix}mls_lexicon_categories WHERE id=%d", $_GET['category']);
$context = $wpdb->get_var($context_sql);
$switchsym = '?';
if(sizeOf($_GET) > 0 && array_keys($_GET)[0] != 'course')
	 { $switchsym = '&'; }
if(isset($_REQUEST['res']) and $_REQUEST['res']) // If reply is given to question
{
  if($_POST['res'] == 'y') // If answered yes, increase proficiency level of the word
  {
    $sql = "UPDATE {$wpdb->prefix}mls_lexicon_course_student_card
      SET prog_level=prog_level+1 
      WHERE student_id = {$user->ID} 
      AND course_id = {$courseID} 
      AND code = '{$_POST['word']}'";
    $wpdb->get_results($sql);
  }
  elseif($_POST['res'] == 'n') // if answered no, reset proficiency level of the word
  {
    $sql = "UPDATE {$wpdb->prefix}mls_lexicon_course_student_card 
      SET prog_level=1 
      WHERE student_id = {$user->ID} 
      AND course_id = {$courseID} 
      AND code = '{$_POST['word']}'";
    $wpdb->get_results($sql);  
  }
  elseif($_POST['res'] == 'reset') //Reset all words' proficiency level
  {
    $sql = "UPDATE {$wpdb->prefix}mls_lexicon_course_student_card 
      SET prog_level=1 
      WHERE student_id = {$user->ID} 
      AND course_id = {$courseID}";
    $wpdb->get_results($sql);
  }
}

//////////////////////////////////

$estu_size = get_option('mls_lexicon_student_size');
$presentacion_flashcards = get_option('mls_lexicon_presentation_flashcards');
$realizar_curso = get_option('mls_lexicon_perform_course');
//////////////////////////////////

$curso = get_courseById($courseID);
$sql;

if(get_option('mls_lexicon_perform_course') == "completed")
$sql = "SELECT prog_level,code FROM {$wpdb->prefix}mls_lexicon_course_student_card 
  WHERE student_id = {$user->ID} 
  AND course_id = {$courseID}";
else
$sql = "SELECT prog_level,code FROM {$wpdb->prefix}mls_lexicon_course_student_card 
  WHERE student_id = {$user->ID} 
  AND course_id = {$courseID}
  AND code IN
          (SELECT code
          FROM   {$wpdb->prefix}mls_lexicon_course_codes
          WHERE  category_code = (SELECT CONCAT(notion_type, class, subclass, mgroup, subgroup) 
                                  FROM {$wpdb->prefix}mls_lexicon_categories 
                                  WHERE id={$_GET['category']}))";
echo $_GET['category'];
$estado = $wpdb->get_results($sql);
$estado_lenght=count($estado);
$cajas = array(0,0,0,0,0);
foreach ($estado as $row)
  $cajas[$row->prog_level-1]++;

///////////////////////////////////////////////////////////////////////////////////
//  Repetition Algorythm version 1
///////////////////////////////////////////////////////////////////////////////////

$palabras = array();
foreach ($estado as $row)
{
  if($row->prog_level < 5)
    $palabras[] = $row->code;
}
$palabras_lenght=count($palabras);

$index = 0;
if($palabras_lenght > 0)
{
  if(get_option('mls_lexicon_algoritmo') == "0")
  {
    natsort($palabras);
    
    if(isset($_REQUEST['index']))
      $index = ($_POST['index']+1) % $palabras_lenght;
  }
  else
    $index = rand(0,$palabras_lenght-1);

  $estado_palabra = mls_lexicon_next_flashcard($palabras[$index], $curso->lang_1, $curso->lang_2);
}

/*
$palabras = array();
$palabras_rep = array();
foreach ($estado as $row)
{
  if($row->nivelcubo < 5)
  {
    $palabras[] = $row->codigo;
    for($i=0; $i < 5-$row->nivelcubo; $i++)
      $palabras_rep[] = $row->codigo;
  }
}
$palabras_lenght=count($palabras);
$palabras_rep_lenght=count($palabras_rep);

$index = 0;
if($palabras_lenght > 0)
{
  if(get_option('mls_lexicon_algoritmo') == "no")
  {
    natsort($palabras);
    
    if(isset($_REQUEST['index']))
      $index = ($_POST['index']+1) % $palabras_lenght;
    $estado_palabra = mls_lexicon_next_flashcard($palabras[$index], $curso->idioma_1, $curso->idioma_2);
  }
  if(get_option('mls_lexicon_algoritmo') == "aleatorio")
  {
    $index = rand(0,$palabras_lenght-1);
    $estado_palabra = mls_lexicon_next_flashcard($palabras[$index], $curso->idioma_1, $curso->idioma_2);
  }
  else
  {
    $index = rand(0,$palabras_rep_lenght-1);
    $estado_palabra = mls_lexicon_next_flashcard($palabras_rep[$index], $curso->idioma_1, $curso->idioma_2);
  }
}*/
?>

<html>
<head>
<script src="<?php echo plugins_url() . '/mls_lexicon/js/floating-1.12.js'?>"></script>
<script type="text/javascript">

function mostrar()
{
  //document.getElementById("traduccion").innerHTML="<?php //echo $estado_palabra[1]->text;?>";
}

jQuery(document).ready(function()
{
  jQuery('#hideshow').live('click', function(event)
  {
    jQuery('#chartContainer1').toggle('show');
  });

  jQuery('#hideshow1').live('click', function(event)
  {
    jQuery("#traduccion").html("<?php 
	_e('Word', 'mls_lexicon'); 
	echo ':<br> '; 
	if(isset($estado_palabra[1]->text)) {
	 _e($estado_palabra[1]->text);
	  } 
	  else { 
	   _e('unknown', 'mls_lexicon');
	}
	?>");
    jQuery("#floatdiv2").html("<?php 
	_e('Phrase', 'mls_lexicon'); 
	echo ':<br> ';
	if(isset($estado_palabra[1]->phrase)){ 
	_e($estado_palabra[1]->phrase); 
	} else 
	{  _e('unknown', 'mls_lexicon'); 
	}?>");
    jQuery('#floatdiv5').show();
    jQuery('#hideshow1').hide();
    jQuery('#si').show();
    jQuery('#saltar').show();
    jQuery('#no').show();
  });

  jQuery('#hideshow2').live('click', function(event)
  {
    jQuery("#traduccion").html("<?php _e($estado_palabra[1]->text);?>");
    jQuery("#floatdiv2").html("<?php 
	
	write_log($estado_palabra);
	_e($estado_palabra[1]->phrase);?>");
    jQuery('#floatdiv5').show();
    jQuery('#hideshow2').hide();

    if("<?php echo $estado_palabra[1]->text;?>" == jQuery("#respuesta").val())
    {
      jQuery("#solucion").attr('value',"<?php _e('Right','mls_lexicon');?>");
      jQuery("#res").attr("value", "y");
    }
    else
    {
      jQuery("#solucion").attr('value',"<?php _e('Wrong','mls_lexicon');?>");
      jQuery("#res").attr("value", "n");
    }
    jQuery('#solucion').toggle('show');
  });

  jQuery( "#si" ).click(function()
  {
    jQuery("#res").attr("value", "y");
  });

  jQuery( "#no" ).click(function()
  {
    jQuery("#res").attr("value", "n");
  });

  jQuery( "#reset" ).click(function()
  {
    jQuery("#res").attr("value", "reset");
  });
});

window.onload = function () 
{
  //setTimeout("mostrar()",0000);

  var chart1 = new CanvasJS.Chart("chartContainer1",
  {
    theme: "theme2",
    title:{
      fontSize: 24,
      <?php if(get_option('mls_lexicon_perform_course') == "completed")
      {?>
      text: "<?php echo $curso->lang_1 . '-' . $curso->lang_2 . '-' . $curso->level . '             Flashcards:';?> <?php echo $estado_lenght;?>"
      <?php
      }else
      {?>
      text: "<?php echo $context . '             Flashcards:';?> <?php echo $estado_lenght;?>"
      <?php
     }?>
    },
    legend:{
      fontFamily: "calibri",
      fontSize: 16,
      verticalAlign: "bottom",
      horizontalAlign: "center"
    },
    data: [
    {       
     type: "pie",
     indexLabelFontColor: "#666666",
     indexLabelFontFamily: "calibri",
     toolTipContent: "{y} Flashcards",
     indexLabelFontSize : 16,
     showInLegend: true,
     dataPoints: [

     <?php if(get_option('mls_lexicon_display_statistics') == "1")
     {?>
     {  y: /*500*/parseInt("<?php echo $cajas[0]; ?>"), legendText:"<?php _e('Box', 'mls_lexicon') ?> 1", indexLabel: /*"500 - 17%"*/"<?php echo $cajas[0] . ' - ' . number_format(($cajas[0]/$estado_lenght)*100,2) .'%'; ?>",exploded: true },
     {  y: /*600*/parseInt("<?php echo $cajas[1]; ?>"), legendText:"<?php _e('Box', 'mls_lexicon') ?> 2", indexLabel: /*"600 - 17%"*/"<?php echo $cajas[1] . ' - ' . number_format(($cajas[1]/$estado_lenght)*100,2) .'%'; ?>",exploded: true },
     {  y: /*700*/parseInt("<?php echo $cajas[2]; ?>"), legendText:"<?php _e('Box', 'mls_lexicon') ?> 3", indexLabel: /*"700 - 17%"*/"<?php echo $cajas[2] . ' - ' . number_format(($cajas[2]/$estado_lenght)*100,2) .'%'; ?>",exploded: true },
     {  y: /*400*/parseInt("<?php echo $cajas[3]; ?>"), legendText:"<?php _e('Box', 'mls_lexicon') ?> 4", indexLabel: /*"400 - 17%"*/"<?php echo $cajas[3] . ' - ' . number_format(($cajas[3]/$estado_lenght)*100,2) .'%'; ?>",exploded: true },
     {  y: /*800*/parseInt("<?php echo $cajas[4]; ?>"), legendText:"<?php _e('Box', 'mls_lexicon') ?> 5", indexLabel: /*"800 - 17%"*/"<?php echo $cajas[4] . ' - ' . number_format(($cajas[4]/$estado_lenght)*100,2) .'%'; ?>",exploded: true },
     <?php
     }else
     {?>
     {  y: /*500*/parseInt("<?php echo $cajas[0]; ?>"), legendText:"<?php _e('Box', 'mls_lexicon') ?> 1", indexLabel: /*"500 - 17%"*/"",exploded: true },
     {  y: /*600*/parseInt("<?php echo $cajas[1]; ?>"), legendText:"<?php _e('Box', 'mls_lexicon') ?> 2", indexLabel: /*"600 - 17%"*/"",exploded: true },
     {  y: /*700*/parseInt("<?php echo $cajas[2]; ?>"), legendText:"<?php _e('Box', 'mls_lexicon') ?> 3", indexLabel: /*"700 - 17%"*/"",exploded: true },
     {  y: /*400*/parseInt("<?php echo $cajas[3]; ?>"), legendText:"<?php _e('Box', 'mls_lexicon') ?> 4", indexLabel: /*"400 - 17%"*/"",exploded: true },
     {  y: /*800*/parseInt("<?php echo $cajas[4]; ?>"), legendText:"<?php _e('Box', 'mls_lexicon') ?> 5", indexLabel: /*"800 - 17%"*/"",exploded: true },
     <?php
     }?>
     ]
   }
   ]
  });
  chart1.render();
  document.getElementById("chartContainer1").style.display="none";
}
</script>

<!--<script type="text/javascript" src="/assets/script/canvasjs.min.js"></script>-->
<div class="mls-lex-center"> <h2><?php _e('Study category', 'mls_lexicon');?></h2> </div>
<!--<div id="chartContainer1" style="height: 300px; width: 100%; display:none;"></div>-->
<ul class="mls-lex-center">
<li class="lex-li-2">
<input type='button' value='<?php _e('Position', 'mls_lexicon')?>' id='hideshow'>
</li>
<?php 
	if(get_option('mls_lexicon_perform_course') == "completed") {
		$h = "location.href='". get_permalink()."'";?>
		<li class="lex-li-2">
		<input id="volver" type="submit" onClick="<?=$h;?>" value="<?php _e('Return', 'mls_lexicon')?>">
		</li>
<?php
	} else {
		$h = "location.href='". get_permalink().$switchsym."course=".$courseID."'";?>
		<li class="lex-li-2">
		<input id="volver" type="submit" onClick="<?=$h?>" value="<?php _e('Return', 'mls_lexicon')?>">
		</li>
<?php
	}
?>
<div id="chartContainer1" style="height: 300px; width: 100%;"></div>
<hr>
<body>

<?php
if($palabras_lenght > 0)
{?>
<div class="mls-lex-center">
  <div>
	<img src=<?= get_encoded_img($palabras[$index], 'word');?> width="50%">
  </div>
    <ul>
    <li class="lex-li-2" id="floatdiv3">
    	<h5><?php echo $course_data->lang_1;?></h5> 
		<?php echo do_shortcode('[sc_embed_player/*_template1*/ fileurl="' . mls_lexicon_audio_path($palabras[$index], $curso->lang_1) . '"]');?>
        <p class="lex-li-single"><?php _e('Word', 'mls_lexicon'); echo ':<br> '.$estado_palabra[0]->text;?></p>
        <p class="lex-li-single"><?php _e('Phrase', 'mls_lexicon'); echo ':<br> '.$estado_palabra[0]->phrase;?></p>
	</li>


    <li class="lex-li-2" id="floatdiv5" style="display:none;"> 
    <h5><?php echo $course_data->lang_2; ?></h5>
<?php echo do_shortcode('[sc_embed_player/*_template1*/ fileurl="' . mls_lexicon_audio_path($palabras[$index], $curso->lang_2) . '"]');?>
<p id="traduccion" class="lex-li-single">
      <?php _e('?????', 'mls_lexicon');?></p>
      <p id="floatdiv2" class="lex-li-single">
<?php _e('?????', 'mls_lexicon');?>
</p> 
</li>

  </ul>          
           
</div>
<?php
}?>

<?php
if($palabras_lenght > 0)
{?>
 

 
<?php
}?>


<?php
    if($presentacion_flashcards == "write")
    {?>
      <?php
if($palabras_lenght > 0)
{?>
<input type='button' value='<?php _e('Check result', 'mls_lexicon')?>' id='hideshow2' style="width:100%; margin-bottom:5px; margin-top:-5px;">
<?php
}?>
  <?php
    }else{?>
<?php
if($palabras_lenght > 0)
{?>
<input type='button' value='<?php _e('Check answer', 'mls_lexicon')?>' id='hideshow1' style="width:100%; margin-bottom:5px; margin-top:-5px;">
<?php
}}?>

<?php
if($palabras_lenght > 0)
{?>
  <form name="post" action="" method="post" id="post" style="display:block; margin-left: auto; margin-right: auto; margin-top: 5px; text-align:center; margin-top: -5px;">
  <input type="hidden" id="index" name="index" value="<?php echo $index;?>">
  <input type="hidden" id="res" name="res" value="">
  <input type="hidden" name="word" value="<?php echo $palabras[$index];?>">
  <input type="submit" id="si" value="<?php _e('I know', 'mls_lexicon')?>" style="display:none;">
  <input type="submit" id="saltar" value="<?php _e('Skip', 'mls_lexicon')?>" style="display:none;">
  <input type="submit" id="no" value="<?php _e('I do not know', 'mls_lexicon')?>" style="display:none;">
  <input type="submit" id="solucion" value="<?php _e('', 'mls_lexicon')?>" style="display:none;">
  </form>
<?php
}
else
{?>
  <form name="post" action="" method="post" id="post" style="text-align:center; margin-top: -15px;">
  <input type="hidden" id="res" name="res" value=""/>
  <input type="submit" id="reset" value="<?php _e('Repeat', 'mls_lexicon')?> " style="">
  </form>
<?php
}?>

</body>
</html>