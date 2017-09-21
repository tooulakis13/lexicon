
<?php
global $wpdb;
$user = wp_get_current_user();
$courseID = get_course(substr($course, 3), substr($course, 0, 3), $level);
$courseID = $courseID[0]->id;
$sql = "SELECT DISTINCT context FROM {$wpdb->prefix}lexicon_course_codes WHERE course_id = {$courseID}";

$contextos = $wpdb->get_results($sql);

foreach ($contextos as $row)
        $arr[] = $row->context;

$size_contextos = count($contextos);
$palabras_contexto = array();
$porcentaje_categoria = array();
$imagen_categoria = array();

for($i = 0; $i < 5; $i++)
	for($j = 0; $j < $size_contextos; $j++)
  $palabras_contexto[$i][$j] = 0;

for($i = 0; $i < $size_contextos; $i++)
{
  $sql = "SELECT prog_level,code FROM {$wpdb->prefix}lexicon_course_student_card WHERE student_id = {$user->ID} AND course_id = {$courseID} AND code IN
          (SELECT code
          FROM   {$wpdb->prefix}lexicon_course_codes
          WHERE  context = '{$contextos[$i]->context}')";
  $nivelcubo_categoria = $wpdb->get_results($sql);

  foreach($nivelcubo_categoria as $row)
    $palabras_contexto[$row->prog_level-1][$i]++;

  $porcentaje_categoria[$contextos[$i]->context] = $palabras_contexto[4][$i]/count($nivelcubo_categoria)*100;
  $imagen_categoria[$contextos[$i]->context] = $nivelcubo_categoria[rand(0,count($nivelcubo_categoria)-1)]->code;
}

$image_path = plugins_url() . "/lexicon/images/A1/";

?>

<html>
<head>
<script src="<?php echo plugins_url() . '/lexicon/js/Chart.js'?>"></script>
<script src="<?php echo plugins_url() . '/lexicon/js/jquery.ddslick.min.js'?>"></script>
<script type="text/javascript">

var porcentaje_categoria = jQuery.parseJSON('<?php echo json_encode($porcentaje_categoria); ?>');
var imagen_categoria = jQuery.parseJSON('<?php echo json_encode($imagen_categoria); ?>');

jQuery(document).ready(function()
{
  jQuery("#categorias").change(function(event)
  {
  	jQuery("#progress").val(porcentaje_categoria[jQuery("#categorias").find(':selected').val()]);

  	var path = "<?php echo $image_path; ?>";
  	path = path.concat(imagen_categoria[jQuery("#categorias").find(':selected').val()],".jpg");
    jQuery("#imagen").attr("src", path);
	
   
    <?php $href = "location.href='". get_permalink(get_the_ID()) ."?".$course."/".$level."/";?>
    var link = "<?=$href?>";
    link = link.concat(jQuery("#categorias").find(':selected').val(),"'");
    jQuery("#input").attr('onClick', link);
  });
});

var radarChartData =
{
   labels: jQuery.parseJSON('<?php echo json_encode($arr); ?>'),
   datasets: [
		{
			label: "Caja 1",
			fillColor: "rgba(151,187,205,0.2)",
			strokeColor: "rgba(151,187,205,1)",
			pointColor: "rgba(151,187,205,1)",
			pointStrokeColor: "#fff",
			pointHighlightFill: "#fff",
			pointHighlightStroke: "rgba(151,187,205,1)",
			data: jQuery.parseJSON('<?php echo json_encode($palabras_contexto[0]); ?>')
		},
		{
			label: "Caja 2",
			fillColor: "rgba(205,151,160,0.2)",
			strokeColor: "rgba(205,151,160,1)",
			pointColor: "rgba(205,151,160,1)",
			pointStrokeColor: "#fff",
			pointHighlightFill: "#fff",
			pointHighlightStroke: "rgba(205,151,160,1)",
			data: jQuery.parseJSON('<?php echo json_encode($palabras_contexto[1]); ?>')
		},
		{
			label: "Caja 3",
			fillColor: "rgba(160,205,151,0.2)",
			strokeColor: "rgba(160,205,151,1)",
			pointColor: "rgba(160,205,151,1)",
			pointStrokeColor: "#fff",
			pointHighlightFill: "#fff",
			pointHighlightStroke: "rgba(160,205,151,1)",
			data: jQuery.parseJSON('<?php echo json_encode($palabras_contexto[2]); ?>')
		},
		{
			label: "Caja 4",
			fillColor: "rgba(151,205,196,0.2)",
			strokeColor: "rgba(151,205,196,1)",
			pointColor: "rgba(151,205,196,1)",
			pointStrokeColor: "#fff",
			pointHighlightFill: "#fff",
			pointHighlightStroke: "rgba(151,205,196,1)",
			data: jQuery.parseJSON('<?php echo json_encode($palabras_contexto[3]); ?>')
		},
		{
			label: "Caja 5",
			fillColor: "rgba(151,160,205,0.2)",
			strokeColor: "rgba(151,160,205,1)",
			pointColor: "rgba(151,160,205,1)",
			pointStrokeColor: "#fff",
			pointHighlightFill: "#fff",
			pointHighlightStroke: "rgba(151,160,205,1)",
			data: jQuery.parseJSON('<?php echo json_encode($palabras_contexto[4]); ?>')
		}
]
};
  
window.onload = function()
{
  window.myRadar = new Chart(document.getElementById("canvas").getContext("2d")).Radar(radarChartData, {responsive: true});
}

</script>
</head>
<body>
<div class="head"> <p style="height:10%; margin-bottom:15px">LEXICON - <?php _e('Course Studying', 'lexicon')?> - <?php echo $course; ?></p></div>
<canvas id="canvas" style="width:100%"></canvas>
<hr>
<h2 style="text-align:center; margin-top:-15px"><?php _e('Select category', 'lexicon')?></h2>

<?php for($i=0; $i <  count($contextos); $i++)
{?>

<?php $h = "location.href='". get_permalink(get_the_ID()) ."?".$course."/".$level."/".$contextos[$i]->context."'";?>
<div  onClick="<?=$h?>" onmouseover="this.style.border='2px solid #2266AA';" onmouseout="this.style.border='2px solid #000000';" style="float:left; margin-right:2px; margin-bottom:2px;
width:120px;height:150px;top:10px;right:10px; border-radius:3px;
padding:16px;background:#FFFFFF;  
border:2px solid #000000;
z-index:100">

<div style="font-size: 10px; margin-bottom:3px; text-align:center;"><?php echo ucfirst(strtolower($contextos[$i]->context));?></div>
<img id ="imagen" style="display:block; margin-left: auto; margin-right: auto; margin-top:-3px" src=<?= lexicon_image_path($imagen_categoria[$contextos[$i]->context]);?> width="90%" height="90%;">
<progress id ="progress" style="display:block; margin-left: auto; margin-right: auto; margin-top:3px" value="<?php echo $porcentaje_categoria[$contextos[$i]->context]; ?>" max="100"></progress>
</div>

<?php
}?>

</body>
</html>