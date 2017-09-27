<?php

//  Cargar curso mediante .csv
if(isset($_REQUEST['csv_aceptar']) and $_REQUEST['csv_aceptar'])
{
  if(isset($_FILES['csv-file']))
  {
    //$file = wp_upload_bits($_FILES['csv-file']['name'], null, @file_get_contents($_FILES['csv-file']['tmp_name']));
    /*if (FALSE === $file['error']){}*/
    if(strpos($_FILES['csv-file']['name'], ".csv") === FALSE)
      echo "ERROR!!! DEBE CARGARSE UN FICHERO CSV";
    else
    {
      move_uploaded_file($_FILES['csv-file']['tmp_name'],  ABSPATH. '/wp-content/plugins/lexicon/uploads/' . $_FILES['csv-file']['name']);
      $dir = str_replace("\\","/",LEXICON_DIR) . '/uploads';
      lexicon_load_course($dir, $_FILES['csv-file']['name']);
    }
    
  }
}

if(isset($_REQUEST['nuevo_curso']) and $_REQUEST['nuevo_curso'])
{
  //Creamos cada curso, el idioma base con todos los demás de la base de datos
  $curso_id = set_cursos($_POST['idioma_1'],$_POST['idioma_2'],$_POST['nivel'],$_POST['descripcion']);

  //Cargamos los códigos en la tabla curso_codigo, es decir, cargamos las flashcards(códigos por curso).
  if(isset($_REQUEST['nuevo_curso']) and $_REQUEST['nuevo_curso'])
  {
    $sql = "SELECT * FROM {$wpdb->prefix}_lexicon_curso_codigo WHERE curso_id = {$_POST['curso_id']}";
    $wpdb->query($sql);
  }

  //Asignamos a ese usuario como profesor en la bd
  set_cursos_profe($user->ID, $curso_id);
}

?>
<html>
<head>
<script>
var check = 0;
jQuery(document).ready(function()
{
/*jQuery('#CrearCurso').validate({
rules: {
	idiomaBase: { required: true, minlength: 4,maxlength:7},
	idiomaDestino: { required:true,minlength:4,maxlength:7},
	nivel: {required:true, minlength: 2, maxlength: 3},			
},
messages: {
	idiomaBase: "El campo es obligatorio.",
	idiomaDestino : "El campo es obligatorio.",
	nivel : "Debe introducir un nivel correcto",			
}
});
/*jQuery('#CrearCurso').submit(function(){
	var base = jQuery('#idiomaBase').val();
	var dest = jQuery('#idiomaDestino').val();
	var nivel = jQuery('#nivel').val();
});*/

  jQuery("#cursoBase0").change(function(event)
  {
    var cursobasesel = jQuery("#cursoBase0").find(':selected').val();
    jQuery("#cursoBase1").empty();
    jQuery("#cursoBase1").append("<option>Selecciona idioma destino</option>");
    jQuery('#cursoBase0').find('option').each(function()
    {
      if(jQuery(this).val() != cursobasesel && jQuery(this).val() != ""/*jQuery(this).val() != jQuery('#cursoBase0').first().val()*/)
        jQuery("#cursoBase1").append("<option>" + jQuery(this).val() + "</option>");
    });

  });

  jQuery('#cargar').live('click', function(event)
  {
    jQuery('#cargar_csv').toggle('show');
    jQuery('#tabla_crear_curso').hide();
    jQuery('#añadir_palabras_curso').hide();
    jQuery('#crear').hide();
    if(check == 0)
    {
      jQuery('#curso_por_pasos').hide();
      jQuery('#modificar_curso').hide();
      check = 1;
    }
    else
    {
      jQuery('#curso_por_pasos').show();
      jQuery('#modificar_curso').show();
      check = 0;
    }
    
  });

  jQuery('#curso_por_pasos').live('click', function(event)
  {
    jQuery('#cargar_csv').hide();
    jQuery('#tabla_crear_curso').toggle('show');
    jQuery('#añadir_palabras_curso').toggle('show');
    jQuery('#crear').toggle('show');
    if(check == 0)
    {
      jQuery('#cargar').hide();
      jQuery('#modificar_curso').hide();
      check = 1;
    }
    else
    {
      jQuery('#cargar').show();
      jQuery('#modificar_curso').show();
      check = 0;
    }
  });

  jQuery('#modificar_curso').live('click', function(event)
  {
    jQuery('#cargar_csv').hide();
    jQuery('#tabla_crear_curso').hide();
    jQuery('#añadir_palabras_curso').hide();
    jQuery('#crear').hide();
    if(check == 0)
    {
      jQuery('#curso_por_pasos').hide();
      jQuery('#cargar').hide();
      check = 1;
    }
    else
    {
      jQuery('#curso_por_pasos').show();
      jQuery('#cargar').show();
      check = 0;
    }
  });

});
</script>
</head>
<body>
<div>
  <div class="head">
    <p >LEXICON -
      <?php _e('Cursos', 'lexicon')?>
    </p>
  </div>
</div>
<?php
	if ( isset( $_GET['m'] ))
	 	{
			if( $_GET['m'] == '1' ){?>
<div id='message' class='updated fade'>
  <p><strong>
    <?php _e('Creado correctamente.','lexicon');?>
    </strong></p>
</div>
<?php }
			if( $_GET['m'] == '2' ){?>
<div id='message' class='error fade'>
  <p><strong>
    <?php _e('Ya existe el curso.','lexicon');?>
    </strong></p>
</div>
<?php }
			if( $_GET['m'] == '3' ){?>
<div id='message' class='error fade'>
  <p><strong>
    <?php _e('No puede haber ningun campo en blanco','lexicon');?>
    </strong></p>
</div>
<?php }
 		}
			?>
<div> 
  
  <!-- CARGAR CSV --> 
  <!-- //////////////////////////////////////////////////////// -->
  <input id="cargar" type="submit" class="button-primary" value="<?php _e('CARGAR CSV','lexicon');?>" style="width:100%; margin-bottom:5px;">
  <div id="cargar_csv" style="margin-bottom:10px; margin-top:10px; text-align:center; display:none;">
    <form action="" method="post" enctype="multipart/form-data">
      <p>
        <?php _e('Selecciona un archivo para subir:','lexicon');?>
        <input type="file" id="csv-file" name="csv-file" value="" style="margin-top:5px;"/>
      </p>
      <input type="submit" name="csv_aceptar" class="button-primary" value="<?php _e('ACEPTAR','lexicon');?>" style="margin-top:-10px;">
    </form>
  </div>
  <!-- //////////////////////////////////////////////////////// -->
  
  <input id="curso_por_pasos" type="submit" name="nuevo_curso" class="button-primary" value="CREAR CURSO POR PASOS" style="width:100%;">
  <div class="metabox-holder m-settings" style="width:100%; margin-top:10px;">
    <div id="tabla_crear_curso"class="postbox" style="display:none;">
      <input type="hidden" name="action" value="crearCurso">
      <?php wp_nonce_field( 'crearCurso_Verificar' ); ?>
      <div class="inside">
        <table class="form-table">
          <tbody>
            <tr valign="top">
              <th scope="row" style="vertical-align: top;"> <?php _e('Base:','lexicon');?></th>
              <td><div id="base0">
                  <select id="cursoBase0" name="cursoBase0" class="idiomas" style="width:100%">
                    <option value="">Selecciona idioma base</option>
                    <?php 
                                    foreach ($cursosbase as $curso){?>
                    <option value="<?php echo $curso->idioma_1?>"><?php echo $curso->idioma_1?></option>
                    <?php }?>
                  </select>
                </div></td>
            </tr>
            <!--<tr valign="top">
                    	<th scope="row" style="vertical-align: top;"> <?php _e('Destino:','lexicon');?></th>
                        	<td>
                            	<div id="base1">                       
                                    <select id="cursoBase1" name="cursoBase1" class="idiomas" style="width:100%">   
                                    <option value="">Selecciona idioma destino</option>                                 
                                    </select>
                                </div>
                            </td>
                     </tr>-->
            <tr valign="top">
              <th scope="row" style="vertical-align: top;"> <?php _e('Nivel:','lexicon');?></th>
              <td><input type="text" placeholder="Ej. A1" id="nivel" name="nivel" style="width:100%"/></td>
            </tr>
            <tr valign="top">
              <th scope="row" style="vertical-align: top;"><?php _e('Descripción:','lexicon');?>
                <div style="visibility:hidden;"> -------------------------</div>
              </th>
              <td><textarea rows="6" cols="45" id="descripcion" name="descripcion"/>
                </textarea>
                <div class="description">
                  <?php _e('Insertar una breve descripción del Curso. Máximo 255 letras.','lexicon');?>
                </div></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="submit"> 
      <!--<input id="seleccionar_palabras" type="submit" name="Submit" class="button-primary" value="<?php _e('SELECCIONAR PALABRAS','lexicon');?>" style="width:100%; margin-bottom:5px; margin-top:-15px; display:none;">-->
      
      <input id="añadir_palabras_curso" type="submit" name="Submit" class="button-primary" value="<?php _e('AÑADIR LAS PALABRAS DE UN CURSO','lexicon');?>" style="width:100%; margin-bottom:5px; display:none;">
      <input id="crear" type="submit" name="Submit" class="button-primary" value="<?php _e('CREAR','lexicon');?>" style="width:100%; margin-bottom:15px; display:none;">
    </div>
    </form>
  </div>
</div>
<input id="modificar_curso" type="submit" name="Submit" class="button-primary" value="<?php _e('MODIFICAR CURSO','lexicon');?>" style="width:100%; margin-top:-5px;">
</body>
</html>