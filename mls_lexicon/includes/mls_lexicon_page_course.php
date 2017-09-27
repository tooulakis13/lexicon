<?php
global $wpdb;
$user = wp_get_current_user();

$courseID = $_GET['course'];
$course_cat_sql = $wpdb->prepare("SELECT DISTINCT code FROM {$wpdb->prefix}mls_lexicon_course_codes WHERE course_id=%d", $courseID);
$course_categories = $wpdb->get_results($course_cat_sql, OBJECT);
$cat_transes;
$switchsym = '?';
	if(sizeOf($_GET) > 0 && array_keys($_GET)[0] != 'course') { $switchsym = '&'; }
foreach ($course_categories as $c_cat) {
	$temp_arr[] = $c_cat->category_code;
}
$course_categories = $temp_arr;
asort($course_categories); // Orderds category array from lowest code to highest, so that the parent category will always be processed first
$course_data = get_courseById($courseID);
// Preparing array of categories with hierarchy
// 1st level: nt - 0,1 => 1,2
// Format: (category id, category code,category code array(nt, class, subclass, mgroup, group), category translation, category image, children arrray)
$cat_hier = array();
$cats_size = count($course_categories);
foreach ($course_categories as $c_cat) {
	
	
	
$palabras_contexto = array();
$porcentaje_categoria = array();
$imagen_categoria = array();
write_log($c_cat);
	$cat_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mls_lexicon_categories WHERE CONCAT(notion_type, class, subclass, mgroup, subgroup)=%s", $c_cat));

	write_log($cat_data);

	$cat_id = $cat_data->id;
	$cat_nt = $cat_data->notion_type;
	$cat_class = $cat_data->class;
	$cat_sclass = $cat_data->subclass;
	$cat_group = $cat_data->mgroup;
	$cat_sgroup = $cat_data->subgroup;
	$cat_trans = $wpdb->get_var($wpdb->prepare("SELECT name from {$wpdb->prefix}mls_lexicon_categories_trans WHERE cat_id=%d AND lang=%s", $cat_id, $course_data->lang_1));
	
	
		$cat_hier[] = array($cat_id, $c_cat, $cat_nt,  array($cat_class, $cat_sclass, $cat_group, $cat_sgroup), $cat_trans);
		$cat_transes[] = addslashes($cat_trans); // Escape characters for json encoding
}

for($i = 0; $i < 5; $i++) {
	for($j = 0; $j < $cats_size; $j++) {
  $palabras_contexto[$i][$j] = 0;
	}
}

for($i = 0; $i < $cats_size; $i++)
{
  $sql = "SELECT prog_level,code FROM {$wpdb->prefix}mls_lexicon_course_student_card WHERE student_id = {$user->ID} AND course_id = {$courseID} AND code IN
          (SELECT code
          FROM   {$wpdb->prefix}mls_lexicon_course_codes
          WHERE  category_code = '{$course_categories[$i]}')";
  $nivelcubo_categoria = $wpdb->get_results($sql);

  foreach($nivelcubo_categoria as $row)
    $palabras_contexto[$row->prog_level-1][$i]++;

  $porcentaje_categoria[$course_categories[$i]] = $palabras_contexto[4][$i]/count($nivelcubo_categoria)*100;
  $imagen_categoria[$course_categories[$i]] = $nivelcubo_categoria[rand(0,count($nivelcubo_categoria)-1)]->code;
}
 

?>

<html>
<head>
<script src="<?php echo plugins_url() . '/mls_lexicon/js/Chart.js'?>"></script>
<script src="<?php echo plugins_url() . '/mls_lexicon/js/jquery.ddslick.min.js'?>"></script>
<script type="text/javascript">

var porcentaje_categoria = jQuery.parseJSON('<?php echo json_encode($porcentaje_categoria); ?>');
var imagen_categoria = jQuery.parseJSON('<?php echo json_encode($imagen_categoria); ?>');

jQuery(document).ready(function()
{
  jQuery("#categorias").change(function(event)
  {
  	jQuery("#progress").val(porcentaje_categoria[jQuery("#categorias").find(':selected').val()]);

  
	
   
    <?php $href = "location.href='". get_permalink().$switchsym."course=".$courseID; ?>
    var link = "<?=$href;?>";
    link = link.concat(jQuery("#categorias").find(':selected').val(),"'");
    jQuery("#input").attr('onClick', link);
  });
});

var radarChartData =
{
   labels: jQuery.parseJSON('<?php echo json_encode($cat_transes); ?>'),
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
<div class="mls-lex-center"> <h2><?php _e('Course Studying', 'mls_lexicon')?> : <?php echo $course_data->lang_1.'-'.$course_data->lang_2.'-'.$course_data->level; ?></h2></div>
<canvas id="canvas" style="width:100%"></canvas>
<hr>
<div class="mls-lex-center">
<h2><?php _e('Select category', 'mls_lexicon')?></h2>
<ul>
<?php 

	foreach($cat_hier as $cat_hier_s){

	?>

    <?php $h = "location.href='". get_permalink().$switchsym."course=".$courseID."&category=".$cat_hier_s[0]."'";?>
    <li class="mls-lex-pointer" onClick="<?=$h?>" style="border: 1px black solid;">

    <p class="title"><?php echo ucfirst(strtolower($cat_hier_s[4]));?></p>

    <img id="imagen" src=<?= get_encoded_img($cat_hier_s[0], 'cat');?>>

    <progress id ="progress" value="<?php echo $porcentaje_categoria[$cat_hier_s[1]]; ?>" max="100"></progress>

    </li>

<?php
    }
?>
</ul>
</div>
</body>
</html>
