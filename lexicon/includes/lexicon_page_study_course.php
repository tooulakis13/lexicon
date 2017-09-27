<?php

/*

  Description: Course study page

*/

global $wpdb;
$user = wp_get_current_user(); // get current user
$courseID = get_course(substr($course, 3), substr($course, 0, 3), $level); // get course ID from URI
$courseID = $courseID[0]->id;

if(isset($_REQUEST['res']) and $_REQUEST['res']) // If reply is given to question
{
  if($_POST['res'] == 'y') // If answered yes, increase proficiency level of the word
  {
    $sql = "UPDATE {$wpdb->prefix}lexicon_course_student_card
      SET prog_level=prog_level+1 
      WHERE student_id = {$user->ID} 
      AND course_id = {$courseID} 
      AND code = '{$_POST['word']}'";
    $wpdb->get_results($sql);
  }
  elseif($_POST['res'] == 'n') // if answered no, reset proficiency level of the word
  {
    $sql = "UPDATE {$wpdb->prefix}lexicon_course_student_card 
      SET prog_level=1 
      WHERE student_id = {$user->ID} 
      AND course_id = {$courseID} 
      AND code = '{$_POST['word']}'";
    $wpdb->get_results($sql);  
  }
  elseif($_POST['res'] == 'reset') //Reset all words' proficiency level
  {
    $sql = "UPDATE {$wpdb->prefix}lexicon_course_student_card 
      SET prog_level=1 
      WHERE student_id = {$user->ID} 
      AND curso_id = {$courseID}";
    $wpdb->get_results($sql);
  }
}

//////////////////////////////////

$estu_size = get_option('lexicon_student_size');
$presentacion_flashcards = get_option('lexicon_presentation_flashcards');
$realizar_curso = get_option('lexicon_perform_course');

//////////////////////////////////

$curso = get_courseById($courseID);
$sql;

if(get_option('lexicon_perform_course') == "completed")
$sql = "SELECT prog_level,code FROM {$wpdb->prefix}lexicon_course_student_card 
  WHERE student_id = {$user->ID} 
  AND course_id = {$courseID}";
else
$sql = "SELECT prog_level,code FROM {$wpdb->prefix}lexicon_course_student_card 
  WHERE student_id = {$user->ID} 
  AND course_id = {$courseID}
  AND code IN
          (SELECT code
          FROM   {$wpdb->prefix}lexicon_course_codes
          WHERE  context = '{$context}')";
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
  if(get_option('lexicon_algoritmo') == "0")
  {
    natsort($palabras);
    
    if(isset($_REQUEST['index']))
      $index = ($_POST['index']+1) % $palabras_lenght;
  }
  else
    $index = rand(0,$palabras_lenght-1);

  $estado_palabra = lexicon_next_flashcard($palabras[$index], $curso->lang_1, $curso->lang_2);
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
  if(get_option('lexicon_algoritmo') == "no")
  {
    natsort($palabras);
    
    if(isset($_REQUEST['index']))
      $index = ($_POST['index']+1) % $palabras_lenght;
    $estado_palabra = lexicon_next_flashcard($palabras[$index], $curso->idioma_1, $curso->idioma_2);
  }
  if(get_option('lexicon_algoritmo') == "aleatorio")
  {
    $index = rand(0,$palabras_lenght-1);
    $estado_palabra = lexicon_next_flashcard($palabras[$index], $curso->idioma_1, $curso->idioma_2);
  }
  else
  {
    $index = rand(0,$palabras_rep_lenght-1);
    $estado_palabra = lexicon_next_flashcard($palabras_rep[$index], $curso->idioma_1, $curso->idioma_2);
  }
}*/
?>

<html>
<head>
<script src="<?php echo plugins_url() . '/lexicon/js/floating-1.12.js'?>"></script>
<script type="text/javascript">

function mostrar()
{
  //document.getElementById("traduccion").innerHTML="<?php echo $estado_palabra[1]->text;?>";
}

jQuery(document).ready(function()
{
  jQuery('#hideshow').live('click', function(event)
  {
    jQuery('#chartContainer1').toggle('show');
  });

  jQuery('#hideshow1').live('click', function(event)
  {
    jQuery("#traduccion").html("<?php _e($estado_palabra[1]->text,'lexicon');?>");
    //jQuery("#floatdiv1").html("<?php echo $estado_palabra[0]->phrase;?>");
    jQuery("#floatdiv2").html("<?php _e($estado_palabra[1]->phrase,'lexicon');?>");
    jQuery('#floatdiv5').show();
    jQuery('#hideshow1').hide();
    //jQuery('#floatdiv1').toggle('show');
    //jQuery('#floatdiv2').toggle('show');
    jQuery('#si').show();
    jQuery('#saltar').show();
    jQuery('#no').show();
    //jQuery('#volver').toggle('show');
  });

  jQuery('#hideshow2').live('click', function(event)
  {
    jQuery("#traduccion").html("<?php _e($estado_palabra[1]->text,'lexicon');?>");
    jQuery("#floatdiv2").html("<?php _e($estado_palabra[1]->phrase,'lexicon');?>");
    jQuery('#floatdiv5').show();
    jQuery('#hideshow2').hide();

    if("<?php echo $estado_palabra[1]->text;?>" == jQuery("#respuesta").val())
    {
      jQuery("#solucion").attr('value',"<?php _e('Right','lexicon');?>");
      jQuery("#res").attr("value", "y");
    }
    else
    {
      jQuery("#solucion").attr('value',"<?php _e('Wrong','lexicon');?>");
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
      <?php if(get_option('lexicon_perform_course') == "completed")
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

     <?php if(get_option('lexicon_display_statistics') == "1")
     {?>
     {  y: /*500*/parseInt("<?php echo $cajas[0]; ?>"), legendText:"<?php _e('Box', 'lexicon') ?> 1", indexLabel: /*"500 - 17%"*/"<?php echo $cajas[0] . ' - ' . number_format(($cajas[0]/$estado_lenght)*100,2) .'%'; ?>",exploded: true },
     {  y: /*600*/parseInt("<?php echo $cajas[1]; ?>"), legendText:"<?php _e('Box', 'lexicon') ?> 2", indexLabel: /*"600 - 17%"*/"<?php echo $cajas[1] . ' - ' . number_format(($cajas[1]/$estado_lenght)*100,2) .'%'; ?>",exploded: true },
     {  y: /*700*/parseInt("<?php echo $cajas[2]; ?>"), legendText:"<?php _e('Box', 'lexicon') ?> 3", indexLabel: /*"700 - 17%"*/"<?php echo $cajas[2] . ' - ' . number_format(($cajas[2]/$estado_lenght)*100,2) .'%'; ?>",exploded: true },
     {  y: /*400*/parseInt("<?php echo $cajas[3]; ?>"), legendText:"<?php _e('Box', 'lexicon') ?> 4", indexLabel: /*"400 - 17%"*/"<?php echo $cajas[3] . ' - ' . number_format(($cajas[3]/$estado_lenght)*100,2) .'%'; ?>",exploded: true },
     {  y: /*800*/parseInt("<?php echo $cajas[4]; ?>"), legendText:"<?php _e('Box', 'lexicon') ?> 5", indexLabel: /*"800 - 17%"*/"<?php echo $cajas[4] . ' - ' . number_format(($cajas[4]/$estado_lenght)*100,2) .'%'; ?>",exploded: true },
     <?php
     }else
     {?>
     {  y: /*500*/parseInt("<?php echo $cajas[0]; ?>"), legendText:"<?php _e('Box', 'lexicon') ?> 1", indexLabel: /*"500 - 17%"*/"",exploded: true },
     {  y: /*600*/parseInt("<?php echo $cajas[1]; ?>"), legendText:"<?php _e('Box', 'lexicon') ?> 2", indexLabel: /*"600 - 17%"*/"",exploded: true },
     {  y: /*700*/parseInt("<?php echo $cajas[2]; ?>"), legendText:"<?php _e('Box', 'lexicon') ?> 3", indexLabel: /*"700 - 17%"*/"",exploded: true },
     {  y: /*400*/parseInt("<?php echo $cajas[3]; ?>"), legendText:"<?php _e('Box', 'lexicon') ?> 4", indexLabel: /*"400 - 17%"*/"",exploded: true },
     {  y: /*800*/parseInt("<?php echo $cajas[4]; ?>"), legendText:"<?php _e('Box', 'lexicon') ?> 5", indexLabel: /*"800 - 17%"*/"",exploded: true },
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
<div class="head"> <p>LEXICON - <?php _e('Study category', 'lexicon');?></p> </div>
<!--<div id="chartContainer1" style="height: 300px; width: 100%; display:none;"></div>-->
<input type='button' value='<?php _e('Position', 'lexicon')?>' id='hideshow' style="width:80%">

<?php if(get_option('lexicon_perform_course') == "completed")
{?>
<?php $h = "location.href='". get_permalink(get_the_ID())."'";?>
<input id="volver" type="submit" onClick="<?=$h?>" value="<?php _e('Return', 'lexicon')?>" style="float:right; width:19%;">
<?php
}else
{?>
<?php $h = "location.href='". get_permalink(get_the_ID()) ."?".$course."/".$level."'";?>
<input id="volver" type="submit" onClick="<?=$h?>" value="<?php _e('Return', 'lexicon')?>" style="float:right; width:19%;">
<?php
}?>
<div id="chartContainer1" style="height: 300px; width: 100%;"></div>
<hr>
<body>

<?php
if($palabras_lenght > 0)
{?>
<div class="wrappera" style="margin-top: -15px; margin-bottom:5px">
  <div class="left3">
    <div id="floatdiv" style="  
border-radius:3px;
padding:16px;background:#FFFFFF;  
border:1px solid #000000;  
z-index:100">  
<img src=<?= lexicon_image_path($palabras[$index]);?> width="100%" height="100%;">
</div>

  </div>
    <div class="left4">
    <div id="floatdiv3" style="
     border-radius:3px;
padding:10px;background:#FFFFFF;  
border:1px solid #000000; margin: 0 0 10px; 
z-index:100"> 
<?php echo do_shortcode('[sc_embed_player/*_template1*/ fileurl="' . lexicon_audio_path($palabras[$index], $curso->lang_1) . '"]');?>
</div>


    <div id="floatdiv5" style="
border-radius:3px;
padding:10px;background:#FFFFFF;  
border:1px solid #000000; margin: 0 0 10px; display:none;
z-index:100">  
<?php echo do_shortcode('[sc_embed_player/*_template1*/ fileurl="' . lexicon_audio_path($palabras[$index], $curso->lang_2) . '"]');?>
</div>

  </div>          
  <div class="right1"style="">
    <div class="content-box-blue" style="font-size:<?php echo $estu_size;?>px;"><?php echo $estado_palabra[0]->text;?></div>
    <?php
    if($presentacion_flashcards == "write")
    {?>
      <input id="respuesta" class="content-box-blue" value="<?php _e('Show answer', 'lexicon')?>" onMouseMove="this.select()">
      <?php
    }?>
      <div id="traduccion" class="content-box-blue"><div id="traduccion" type="text" style="font-size:<?php echo $estu_size;?>px;"><?php _e('?????', 'lexicon');?></div></div>
  </div>           
</div>
<?php
}?>

<?php
if($palabras_lenght > 0)
{?>
<div id="floatdiv1" class="content-box-blue" style="
border-radius:3px;
border:1px solid #000000;
text-align:center; text-align:left">
<?php echo $estado_palabra[0]->phrase;?>
<?php /*_e('?????', 'lexicon');*/?>
</div>  

<div id="floatdiv2" class="content-box-blue" style=" 
border-radius:3px;
border:1px solid #000000; margin-top: -5px; text-align:left">
<?php _e('?????', 'lexicon');?>
</div>  
<?php
}?>


<?php
    if($presentacion_flashcards == "write")
    {?>
      <?php
if($palabras_lenght > 0)
{?>
<input type='button' value='<?php _e('Check result', 'lexicon')?>' id='hideshow2' style="width:100%; margin-bottom:5px; margin-top:-5px;">
<?php
}?>
  <?php
    }else{?>
<?php
if($palabras_lenght > 0)
{?>
<input type='button' value='<?php _e('Check answer', 'lexicon')?>' id='hideshow1' style="width:100%; margin-bottom:5px; margin-top:-5px;">
<?php
}}?>

<?php
if($palabras_lenght > 0)
{?>
  <form name="post" action="" method="post" id="post" style="display:block; margin-left: auto; margin-right: auto; margin-top: 5px; text-align:center; margin-top: -5px;">
  <input type="hidden" id="index" name="index" value="<?php echo $index;?>">
  <input type="hidden" id="res" name="res" value="">
  <input type="hidden" name="word" value="<?php echo $palabras[$index];?>">
  <input type="submit" id="si" value="<?php _e('I know', 'lexicon')?>" style="display:none;">
  <input type="submit" id="saltar" value="<?php _e('Skip', 'lexicon')?>" style="display:none;">
  <input type="submit" id="no" value="<?php _e('I don not know', 'lexicon')?>" style="display:none;">
  <input type="submit" id="solucion" value="<?php _e('', 'lexicon')?>" style="display:none;">
  </form>
<?php
}
else
{?>
  <form name="post" action="" method="post" id="post" style="text-align:center; margin-top: -15px;">
  <input type="hidden" id="res" name="res" value=""/>
  <input type="submit" id="reset" value="<?php _e('Repeat', 'lexicon')?> " style="">
  </form>
<?php
}?>

</body>
</html>