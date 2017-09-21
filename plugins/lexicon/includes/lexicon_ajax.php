<?php

/*

  Funciones Ajax
  
*/

function lexicon_my_action_callback()
{
  global $wpdb;
  $tablaprefix = "{$wpdb->prefix}lexicon_course";
  $value = $_POST['func'];
  $arr = array();

  switch($value)
  {
    case 1:
  	  $cursoBase = $_POST['nombreBase'];
      $res = $wpdb->get_results("SELECT lang_1 
        FROM ".$tablaprefix." WHERE lang_2 = '".$cursoBase."'");
    
      foreach ($res as $row)
        $arr[] = "<option value=".$row->lang_1.">".$row->lang_1."</option>";
    break;
    
    case 2:
      $cursBase = $_POST['cursBase'];
      $cursDest = $_POST['cursDest']; 

      $res = $wpdb->get_results("SELECT level FROM ".$tablaprefix." WHERE lang_1 = '".$cursBase."'AND lang_2 = '".$cursDest."'");

      foreach ($res as $row)
        $arr[] = "<option value=".$row->level.">".$row->level."</option>";
    break;

    case 3:
      $cursBase = $_POST['cursBase'];
      $cursDest = $_POST['cursDest'];
      $curslvl = $_POST['curslvl'];
      $curso = $cursBase. ' - '.$cursDest.' - '.$curslvl;
      //$res = $wpdb->get_results("SELECT id FROM wp_m_subscriptions WHERE sub_name ='".$curso."'");
    
      //$curso_id = get_curso($cursBase,$cursDest,$curslvl)[0]->ID;
      $course_id = get_course($cursBase,$cursDest,$curslvl);
      $course_id = $course_id[0]->id;
      $user_id = wp_get_current_user()->ID;
      $result = set_course_user($user_id, $course_id);
?> 

<?php
      if(!$result)
        $arr[] = 0;
      if($result)
      {
        $sql = "INSERT INTO {$wpdb->prefix}lexicon_course_student_card (student_id, course_id, code, prog_level)
          SELECT {$user_id}, {$course_id}, code, 1
          FROM   {$wpdb->prefix}lexicon_course_codes
          WHERE  course_id = {$course_id}";
        $wpdb->query($sql);
        $arr[] = 1;
      }
    break;
  }

  ob_clean();
 echo json_encode($arr);
  die;
}