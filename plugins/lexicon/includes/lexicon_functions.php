<?php

function get_course_teacher($course_id){
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'lexicon_course_author';
	
	$query =  "SELECT teacher_id FROM ".$tablaprefix. " WHERE course_id ='".$course_id."'";
	$registro = $wpdb->get_results($query);
	
	return $registro;
	
}

function get_lexicon_users($exclude = NULL) { 

    $users = array();
    $roles = array('lexicon_admin', 'lexicon_editor', 'lexicon_teacher', 'lexicon_student');
	if($exclude) {
		$roles = array_diff($roles, $exclude);
	}

    foreach ($roles as $role) {
        $users_query = new WP_User_Query( array( 
            'fields' => 'all_with_meta', 
            'role' => $role, 
            'orderby' => 'display_name'
            ) );
        $results = $users_query->get_results();
        if ($results) $users = array_merge($users, $results);
	}

    return $users;
}

function get_teacher_courses($teacher_id){
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'lexicon_course_author';
	
	$query =  "SELECT course_id FROM ".$tablaprefix. " WHERE teacher_id ='".$teacher_id."'";
	$registros = $wpdb->get_results($query);
	
	return $registros;
	
}

function set_course_teacher($teacher_id , $course_id){
	global $wpdb;
			
	$wpdb->insert( 
		$wpdb->prefix.'lexicon_course_author' ,
		array(
			'teacher_id' => $teacher_id,
			'course_id' => $course_id
			
			),
		array(
			 '%d',
			 '%d'
			 )
	);
	return $wpdb->insert_id;
}

function get_words($code){
	global $wpdb;
	$tableprefix = $wpdb->prefix.'lexicon_words';
	
	$query =  "SELECT * FROM ".$tableprefix. " WHERE codigo ='".$code."'";
	$registro = $wpdb->get_results($query);
	
	return $registro;
	
}

function get_lang_words_by_level($lang,$level){
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'lexicon_words';
	
	$query =  "SELECT code FROM ".$tablaprefix. " WHERE lang ='".$idioma."' AND level='".$nivel."' ORDER BY code DESC";
	$registro = $wpdb->get_results($query);
	
	return $registro;
	
}

function set_word($code,$lang,$level,$text,$context,$audio,$teacher_id){
	global $wpdb;
			
	$wpdb->insert( 
		$wpdb->prefix.'lexicon_words' ,
		array(
			'code' => $code,
			'lang' => $lang,
			'level' => $level,
			'text' => $text,
			'context' => $context,
			'audio' => $audio,
			'teacher_id' => $teacher_id,
					
			),
		array(
			 '%d',
			 '%s',
			 '%s',
			 '%s',
			 '%s',
			 '%s',
			 '%d'
			 )
	);
	return $wpdb->insert_id;
}
/**************************************************************************************************/
function get_flashcard($cardid){
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'lexicon_flashcard';
	
	$query =  "SELECT * FROM ".$tablaprefix. " WHERE ID ='".$cardid."'";
	$registro = $wpdb->get_results($query);
	
	return $registro;
	
}

function set_flashcard($pregunta_id , $respuesta_id, $img){
	global $wpdb;
			
	$wpdb->insert( 
		$wpdb->prefix.'lexicon_flashcard' ,
		array(
			'id_pregunta' => $pregunta_id,
			'id_respuesta' => $respuesta_id,
			'imagen' => $img			
			),
		array(
			 '%s',
			 '%s',
			 '%s'
			 )
	);
	return $wpdb->insert_id;
}

function get_cursos_card($cursoid){
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'_lexicon_curso_card';
	
	$query =  "SELECT flashcard_id FROM ".$tablaprefix. " WHERE curso_id = '".$cursoid."'";
	$registros = $wpdb->get_results($query);
	
	return $registros;
	
}

function set_cursos_card($cursoid , $cardid){
	global $wpdb;
			
	$wpdb->insert( 
		$wpdb->prefix.'_lexicon_curso_card' ,
		array(
			'curso_id' => $cursoid,
			'flashcard_id' => $cardid
			),
		array(
			 '%d',
			 '%d'
			 )
	);
	return $wpdb->insert_id;
}
/********************************************************************************/
function get_course_student_card($Idestu,$idCurso){
	
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'lexicon_course_student_card';
	
	//$query =  "SELECT flashcard_id,nivelcubo FROM '".$tablaprefix." WHERE estudiante_id ='".$Idestu."' AND curso_id ='".$idCurso."'";
	$query =  "SELECT code, nivelcubo FROM ".$tablaprefix." WHERE student_id ='".$Idestu."' AND course_id ='".$idCurso."'";
	
	$registros = $wpdb->get_results($query);
	
	return $registros;
}
	

function set_courses($lang1,$lang2,$level,$des){
	global $wpdb;
			
	$wpdb->insert( 
		$wpdb->prefix.'lexicon_course' ,
		array(
			'lang_1' =>  $lang1,
			'lang_2' => $lang2,
			'level' => $level,
			'description' => $des			
			),
		array(
			 '%s',
			 '%s',
			 '%s',
			 '%s'
			 )
	);
	
	return $wpdb->insert_id;	
}



 
function get_cursosDestino($cursoBase){
	$registros = "";
	if($cursoBase != ""){		
		global $wpdb;
		$tablaprefix = $wpdb->prefix.'_lexicon_curso';
		
		$query =  "SELECT idioma_2 FROM ".$tablaprefix. " WHERE idioma_1 = '".$cursoBase."'";
		$registros = $wpdb->get_results($query);		
	}
	
}

function get_course($lang_1,$lang_2,$level){
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'lexicon_course';
	
	$query =  "SELECT id FROM ".$tablaprefix. " WHERE lang_1 ='".$lang_1."' AND lang_2 ='".$lang_2."' AND level ='".$level."'";
	$registro = $wpdb->get_results($query);
	
	return $registro;
	
}

function get_courseById($courseID){
	
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'lexicon_course';
	
	$query =  "SELECT * FROM ".$tablaprefix." WHERE id ='".$courseID."'";
	
	$registro = $wpdb->get_row($query);
	
	return $registro;	
}

function get_cursoByCode($code){
	
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'_lexicon_curso';
	
	$query =  "SELECT * FROM ".$tablaprefix;
	
	$registro = $wpdb->get_results($query);
	
	foreach ($registro as $reg){
	
		if(substr($reg->idioma_1,0,3) == substr($code,1,3) && substr($reg->idioma_2,0,3) == substr($code,5,3) && $reg->nivel == substr($code,9,2)){		
			return $reg;
		}
			
	}
	
}



function set_course_user($user , $course_id){
	global $wpdb;
			
	 $res=$wpdb->insert( 
		$wpdb->prefix.'lexicon_course_student' ,
		array(
			'student_id' => $user,
			'course_id' => $course_id
			),
		array(
			 '%d',
			 '%d'
			 )
	);

	return $res;
}

function get_official_courses(){
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'lexicon_course';
	$query =  "SELECT * FROM ".$tablaprefix;
	$result = $wpdb->get_results($query);
	
	return $result;
}

function get_course_user($user){
	global $wpdb;
	$tablaprefix = $wpdb->prefix.'lexicon_course_student';
	
	$query =  "SELECT course_id FROM ".$tablaprefix. " WHERE student_id ='".$user."'";
	$registros = $wpdb->get_results($query);
	
	return $registros;
}

function crearValueCurso($idio1,$idio2,$lvl){
	$idiom1=substr($idio1,0,3);
	$idiom2=substr($idio2,0,3);
	
	$cursFinal = "[".$idiom1."-".$idiom2."-".$lvl."]";
	return $cursFinal;
}

function asociarPalabras($aux,$auxIdCurso){
	global $wpdb;
	
	$ShortBase = strtoupper(substr($aux,1,3));
	$ShortDestino = strtoupper(substr($aux,5,3));
	$ShortLevel = strtoupper(substr($aux,9,2));	
	
	$aux_1 = get_palabrabyIDIOMALVL($ShortBase,$ShortLevel);
	
	$aux_2 = get_palabrabyIDIOMALVL($ShortDestino,$ShortLevel);
	
	
	for($i = 0; $i <= sizeof($aux_1); $i++){
			
			set_flashcard($aux_1[$i]->codigo,$aux_2[$i]->codigo," ");
			set_cursos_card($auxIdCurso, $wpdb->insert_id);
					
	}
	
}

function lex_profe_crearCurso()
{

}

function crearShortcode($Ib,$Id,$Il){
	
	$CodesAux = array();
	$CodesAux[0] = "[level-".strtolower(substr($Ib,0,3))."-".strtolower(substr($Id,0,3))."-".strtolower(substr($Il,0,2))."]";
	$CodesAux[1] = "[/level-".strtolower(substr($Ib,0,3))."-".strtolower(substr($Id,0,3))."-".strtolower(substr($Il,0,2))."]";
	
	return $CodesAux;
	
	
	
}
	
function admin_post_crearCurso(){	
	
	check_admin_referer( 'crearCurso_Verificar' );
		
	$Ibase = $_POST['idiomaBase'];
	$Idest = $_POST['idiomaDestino'];
	$Ilvl  = $_POST['nivel'];
	$descrip = utf8_decode($_POST['descripcion']);
	
	if( $Ibase == null || $Idest == null || $Ilvl == null ){
		wp_redirect(  admin_url( 'admin.php?page=lex_profe_crearCurso&m=3'));
	}else{
		
		if(get_cursos($Ibase, $Idest,$Ilvl) != null || get_page_by_title($CursFinal) != null){
			
			wp_redirect(  admin_url( 'admin.php?page=lex_profe_crearCurso&m=2'));		
			
		}else{			
			
			$IdCursoFinal = set_cursos( $Ibase , $Idest, $Ilvl , $descrip);// DEVUELVE EL ID DEL CURSO RECIÉN CREADO
			
			set_cursos_profe(get_current_user_id(),$IdCursoFinal);// ASOCIA EL CURSO CON EL PROFESOR QUE LO HA CREADO
			
			$CursFinal = crearValueCurso($Ibase,$Idest,$Ilvl);//LLAMA A LA FUNCIÖN QUE CREA EL SHORTCODE
		
			$ShortCode = crearShortcode($Ibase,$Idest,$Ilvl);//CREA EL SHORTCODE PARA INTRODUCIR A LA PAGINA CREADA
			
		 	$Pageslug = '_lexicon_'.$CursFinal;					
						
		 	add_posts_page($CursFinal,$CursFinal,'read',$Pageslug,'lexicon_check_user');
												
				/*******************/
				//Creacion de la pagina!
				$post = array ();
				$post['post_title'] = $CursFinal;
				$post['post_type'] = 'page';
				$post['post_content'] = $ShortCode[0].' [curso] '.$ShortCode[1];
				$post['post_status'] = 'publish';
				$post['post_author'] = get_current_user_id();
				$post['comment_status'] = 'closed';
				$post['ping_satuts'] = 'closed';
				$post['page_template'] = 'front-page.php';
				/*******************/	
			
				wp_insert_post($post);	
				asociarPalabras($CursFinal,$IdCursoFinal);
				wp_redirect(  admin_url( 'admin.php?page=lex_profe_crearCurso&m=1')); 							
		 }
	}
	
}

/*

  Funciones

*/

//header( 'Location: http://www.ejemplo.travel/en' ); 
function getUserLanguage()
{ 
  $lang = substr($_SERVER["HTTP_ACCEPT_LANGUAGE"],0,2);
  return $lang;
} 

function lexicon_get_course_base()
{
  global $wpdb;
  $tabla = $wpdb->prefix.'lexicon_course';
  
  $query =  "SELECT DISTINCT lang_2 FROM ".$tabla."";
  return $wpdb->get_results($query);
}

function lexicon_image_path($code)
{
  return plugins_url() . "/lexicon/images/A1/" . $code . ".jpg";
}

function lexicon_audio_path($codigo, $idiom)
{
  return plugins_url() . "/lexicon/audios/" . $idiom . $codigo . ".mp3";
}

function lexicon_next_flashcard($code, $lang_1, $lang_2)
{
  global $wpdb;
  $sql = "SELECT text,phrase,lang FROM {$wpdb->prefix}lexicon_words WHERE code = '{$code}' AND (lang = '{$lang_1}' OR lang = '{$lang_2}')";
  $res = $wpdb->get_results($sql);

  $order_flashcards = get_option('lexicon_order_flashcards');
  $estado_palabra = array();
  
  if($order_flashcards == "basic")
  {
    if($res[0]->lang == $lang_1)
    {
      $estado_palabra[] = $res[0];
      $estado_palabra[] = $res[1];
    }
    else
    {
      $estado_palabra[] = $res[1];
      $estado_palabra[] = $res[0];
    }
  }
  elseif($order_flashcards == "fast")
  {
    if($res[0]->lang == $lang_1)
    {
      $estado_palabra[] = $res[1];
      $estado_palabra[] = $res[0];
    }
    else
    {
      $estado_palabra[] = $res[0];
      $estado_palabra[] = $res[1];
    }
  }
  elseif($order_flashcards == "random")
  {
    $aleatorio = rand(0,1);
    $estado_palabra[] = $res[$aleatorio];
    if($aleatorio == 0)
      $estado_palabra[] = $res[1];
    else
      $estado_palabra[] = $res[0];
  }
  return $estado_palabra;
}


//////////////////////////////////////////////////////////////////////
function user_can_save( $post_id, $plugin_file, $nonce )
{
  $is_autosave = wp_is_post_autosave( $post_id );
  $is_revision = wp_is_post_revision( $post_id );
  $is_valid_nonce = ( isset( $_POST[ $nonce ] ) && wp_verify_nonce( $_POST[ $nonce ], $plugin_file ) );
  // Return true if the user is able to save; otherwise, false.
  return ! ( $is_autosave || $is_revision ) && $is_valid_nonce;
}
function has_files_to_upload( $id )
{
  return (!empty($_FILES)) && isset($_FILES[ $id ]);
}
//////////////////////////////////////////////////////////////////////

function iso639_1_to_english_string($code)
{
  $languageCodes = array(
 "aa" => "Afar",
 "ab" => "Abkhazian",
 "ae" => "Avestan",
 "af" => "Afrikaans",
 "ak" => "Akan",
 "am" => "Amharic",
 "an" => "Aragonese",
 "ar" => "Arabic",
 "as" => "Assamese",
 "av" => "Avaric",
 "ay" => "Aymara",
 "az" => "Azerbaijani",
 "ba" => "Bashkir",
 "be" => "Belarusian",
 "bg" => "Bulgarian",
 "bh" => "Bihari",
 "bi" => "Bislama",
 "bm" => "Bambara",
 "bn" => "Bengali",
 "bo" => "Tibetan",
 "br" => "Breton",
 "bs" => "Bosnian",
 "ca" => "Catalan",
 "ce" => "Chechen",
 "ch" => "Chamorro",
 "co" => "Corsican",
 "cr" => "Cree",
 "cs" => "Czech",
 "cu" => "Church Slavic",
 "cv" => "Chuvash",
 "cy" => "Welsh",
 "da" => "Danish",
 "de" => "German",
 "dv" => "Divehi",
 "dz" => "Dzongkha",
 "ee" => "Ewe",
 "el" => "Greek",
 "en" => "English",
 "eo" => "Esperanto",
 "es" => "Spanish",
 "et" => "Estonian",
 "eu" => "Basque",
 "fa" => "Persian",
 "ff" => "Fulah",
 "fi" => "Finnish",
 "fj" => "Fijian",
 "fo" => "Faroese",
 "fr" => "French",
 "fy" => "Western Frisian",
 "ga" => "Irish",
 "gd" => "Scottish Gaelic",
 "gl" => "Galician",
 "gn" => "Guarani",
 "gu" => "Gujarati",
 "gv" => "Manx",
 "ha" => "Hausa",
 "he" => "Hebrew",
 "hi" => "Hindi",
 "ho" => "Hiri Motu",
 "hr" => "Croatian",
 "ht" => "Haitian",
 "hu" => "Hungarian",
 "hy" => "Armenian",
 "hz" => "Herero",
 "ia" => "Interlingua (International Auxiliary Language Association)",
 "id" => "Indonesian",
 "ie" => "Interlingue",
 "ig" => "Igbo",
 "ii" => "Sichuan Yi",
 "ik" => "Inupiaq",
 "io" => "Ido",
 "is" => "Icelandic",
 "it" => "Italian",
 "iu" => "Inuktitut",
 "ja" => "Japanese",
 "jv" => "Javanese",
 "ka" => "Georgian",
 "kg" => "Kongo",
 "ki" => "Kikuyu",
 "kj" => "Kwanyama",
 "kk" => "Kazakh",
 "kl" => "Kalaallisut",
 "km" => "Khmer",
 "kn" => "Kannada",
 "ko" => "Korean",
 "kr" => "Kanuri",
 "ks" => "Kashmiri",
 "ku" => "Kurdish",
 "kv" => "Komi",
 "kw" => "Cornish",
 "ky" => "Kirghiz",
 "la" => "Latin",
 "lb" => "Luxembourgish",
 "lg" => "Ganda",
 "li" => "Limburgish",
 "ln" => "Lingala",
 "lo" => "Lao",
 "lt" => "Lithuanian",
 "lu" => "Luba-Katanga",
 "lv" => "Latvian",
 "mg" => "Malagasy",
 "mh" => "Marshallese",
 "mi" => "Maori",
 "mk" => "Macedonian",
 "ml" => "Malayalam",
 "mn" => "Mongolian",
 "mr" => "Marathi",
 "ms" => "Malay",
 "mt" => "Maltese",
 "my" => "Burmese",
 "na" => "Nauru",
 "nb" => "Norwegian Bokmal",
 "nd" => "North Ndebele",
 "ne" => "Nepali",
 "ng" => "Ndonga",
 "nl" => "Dutch",
 "nn" => "Norwegian Nynorsk",
 "no" => "Norwegian",
 "nr" => "South Ndebele",
 "nv" => "Navajo",
 "ny" => "Chichewa",
 "oc" => "Occitan",
 "oj" => "Ojibwa",
 "om" => "Oromo",
 "or" => "Oriya",
 "os" => "Ossetian",
 "pa" => "Panjabi",
 "pi" => "Pali",
 "pl" => "Polish",
 "ps" => "Pashto",
 "pt" => "Portuguese",
 "qu" => "Quechua",
 "rm" => "Raeto-Romance",
 "rn" => "Kirundi",
 "ro" => "Romanian",
 "ru" => "Russian",
 "rw" => "Kinyarwanda",
 "sa" => "Sanskrit",
 "sc" => "Sardinian",
 "sd" => "Sindhi",
 "se" => "Northern Sami",
 "sg" => "Sango",
 "si" => "Sinhala",
 "sk" => "Slovak",
 "sl" => "Slovenian",
 "sm" => "Samoan",
 "sn" => "Shona",
 "so" => "Somali",
 "sq" => "Albanian",
 "sr" => "Serbian",
 "ss" => "Swati",
 "st" => "Southern Sotho",
 "su" => "Sundanese",
 "sv" => "Swedish",
 "sw" => "Swahili",
 "ta" => "Tamil",
 "te" => "Telugu",
 "tg" => "Tajik",
 "th" => "Thai",
 "ti" => "Tigrinya",
 "tk" => "Turkmen",
 "tl" => "Tagalog",
 "tn" => "Tswana",
 "to" => "Tonga",
 "tr" => "Turkish",
 "ts" => "Tsonga",
 "tt" => "Tatar",
 "tw" => "Twi",
 "ty" => "Tahitian",
 "ug" => "Uighur",
 "uk" => "Ukrainian",
 "ur" => "Urdu",
 "uz" => "Uzbek",
 "ve" => "Venda",
 "vi" => "Vietnamese",
 "vo" => "Volapuk",
 "wa" => "Walloon",
 "wo" => "Wolof",
 "xh" => "Xhosa",
 "yi" => "Yiddish",
 "yo" => "Yoruba",
 "za" => "Zhuang",
 "zh" => "Chinese",
 "zu" => "Zulu"
);
  return $languageCodes[$code];
}

?>