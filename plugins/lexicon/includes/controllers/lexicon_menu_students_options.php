<?php

if(isset($_REQUEST['submit']) and $_REQUEST['submit'])
{
  update_option("lexicon_estu_size", @$_POST['estu_size'] );
  update_option("lexicon_orden_flashcards", @$_POST['orden_flashcards'] );
  update_option("lexicon_presentacion_flashcards", @$_POST['presentacion_flashcards']);
  update_option("lexicon_realizar_curso", @$_POST['realizar_curso'] );
  update_option("lexicon_mostrar_estadisticas", @$_POST['mostrar_estadisticas'] );
  update_option("lexicon_algoritmo", @$_POST['algoritmo'] );

  print '<div id="message" class="updated fade"><p>' . __('Options updated', 'lexicon') . '</p></div>';
}
?>

<html>
<head>
<script>

function festu_size(x)
{
  if(x == 1)
  {
    document.getElementById('estu_size').value = "12";
    document.getElementById('estu_size2').checked = false;
    document.getElementById('estu_size3').checked = false;
  }
  if(x == 2)
  {
  	document.getElementById('estu_size').value = "16";
    document.getElementById('estu_size1').checked = false;
    document.getElementById('estu_size3').checked = false;
  }
  if(x == 3)
  {
  	document.getElementById('estu_size').value = "20";
    document.getElementById('estu_size1').checked = false;
    document.getElementById('estu_size2').checked = false;
  }
}

function forden_flashcards(x)
{
  if(x == 1)
  {
    document.getElementById('orden_flashcards').value = "base";
    document.getElementById('orden_flashcards2').checked = false;
    document.getElementById('orden_flashcards3').checked = false;
  }
  if(x == 2)
  {
  	document.getElementById('orden_flashcards').value = "aprender";
    document.getElementById('orden_flashcards1').checked = false;
    document.getElementById('orden_flashcards3').checked = false;
  }
  if(x == 3)
  {
  	document.getElementById('orden_flashcards').value = "aleatorio";
    document.getElementById('orden_flashcards1').checked = false;
    document.getElementById('orden_flashcards2').checked = false;
  }
}

function fpresentacion_flashcards(x)
{
  if(x == 1)
  {
    document.getElementById('presentacion_flashcards').value = "botones";
    document.getElementById('presentacion_flashcards2').checked = false;
    document.getElementById('presentacion_flashcards3').checked = false;
  }
  if(x == 2)
  {
  	document.getElementById('presentacion_flashcards').value = "escribir";
    document.getElementById('presentacion_flashcards1').checked = false;
    document.getElementById('presentacion_flashcards3').checked = false;
  }
  if(x == 3)
  {
  	document.getElementById('presentacion_flashcards').value = "aletorio";
    document.getElementById('presentacion_flashcards1').checked = false;
    document.getElementById('presentacion_flashcards2').checked = false;
  }
}

function frealizar_curso(x)
{
  if(x == 1)
  {
    document.getElementById('realizar_curso').value = "categorias";
    document.getElementById('realizar_curso2').checked = false;
  }
  if(x == 2)
  {
  	document.getElementById('realizar_curso').value = "completo";
    document.getElementById('realizar_curso1').checked = false;
  }
}

function fmostrar_estadisticas(x)
{
  if(x == 1)
  {
    document.getElementById('mostrar_estadisticas').value = "1";
    document.getElementById('mostrar_estadisticas2').checked = false;
  }
  if(x == 2)
  {
  	document.getElementById('mostrar_estadisticas').value = "0";
    document.getElementById('mostrar_estadisticas1').checked = false;
  }
}

function falgoritmo(x)
{
  if(x == 1)
  {
    document.getElementById('algoritmo').value = "1";
    document.getElementById('algoritmo2').checked = false;
  }
  if(x == 2)
  {
  	document.getElementById('algoritmo').value = "0";
    document.getElementById('algoritmo1').checked = false;
  }
}

</script>
</head>
<body>

<div class="wrap">
  <h2><?php _e("Lexicon Opciones", 'lexicon'); ?></h2>
  <div class="postbox-container" style="width:73%;margin-right:2%;">	
    <!-- <p><?php _e('Go to', 'lexicon')?> <a href="tools.php?page=lexicon_exams"><?php _e('Manage Your Exams', 'lexicon')?></a></p> -->
    <form name="post" action="" method="post" id="post">
    <div id="poststuff">
      <div id="postdiv" class="postarea">


        <!-- Modificar el tamaño de letra-->
        <div class="postbox">
          <h3 class="hndle"><span><?php _e('Tamaño de la letra', 'lexicon') ?></span></h3>
          <div class="inside" style="padding:8px">
            <?php
              $estu_size = get_option('lexicon_estu_size');
            ?>
            <input type="hidden" id="estu_size" name="estu_size" value="<?php echo $estu_size;?>">
            <label>&nbsp;<input id ="estu_size1" type='checkbox' value="1"  <?php if($estu_size == "12") echo 'checked'?>
		    onclick="festu_size(1)"/>&nbsp;<?php _e('Tamaño pequeño.', 'lexicon')?> </label><br>
		    <label>&nbsp;<input id ="estu_size2" type='checkbox' value="1" <?php if($estu_size == "16") echo 'checked'?>
		    onclick="festu_size(2)"
		    />&nbsp;<?php _e('Tamaño normal.', 'lexicon')?> </label><br>
		    <label>&nbsp;<input id ="estu_size3" type='checkbox' value="1" <?php if($estu_size == "20") echo 'checked'?>
		    onclick="festu_size(3)"
		    />&nbsp;<?php _e('Tamaño grande.', 'lexicon')?> </label>
		  </div>
	   </div>

	   <!-- Modificar el orden de las flashcards-->
        <div class="postbox">
          <h3 class="hndle"><span><?php _e('Orden de las flashcards', 'lexicon') ?></span></h3>
          <div class="inside" style="padding:8px">
            <?php
              $orden_flashcards = get_option('lexicon_orden_flashcards');
            ?>
            <input type="hidden" id="orden_flashcards" name="orden_flashcards" value="<?php echo $orden_flashcards;?>">
            <label>&nbsp;<input id ="orden_flashcards1" type='checkbox' value="1"  <?php if($orden_flashcards == "base") echo 'checked'?>
		    onclick="forden_flashcards(1)"/>&nbsp;<?php _e('Primero el idioma base.', 'lexicon')?> </label><br>
		    <label>&nbsp;<input id ="orden_flashcards2" type='checkbox' value="1" <?php if($orden_flashcards == "aprender") echo 'checked'?>
		    onclick="forden_flashcards(2)"
		    />&nbsp;<?php _e('Primero el idioma a aprender.', 'lexicon')?> </label><br>
		    <label>&nbsp;<input id ="orden_flashcards3" type='checkbox' value="1" <?php if($orden_flashcards == "aleatorio") echo 'checked'?>
		    onclick="forden_flashcards(3)"
		    />&nbsp;<?php _e('Aleatorio.', 'lexicon')?> </label>
		  </div>
	   </div>

	   <!-- Modificar la presentación de las flashcards-->
        <div class="postbox">
          <h3 class="hndle"><span><?php _e('Presentación de las flashcards', 'lexicon') ?></span></h3>
          <div class="inside" style="padding:8px">
            <?php
              $presentacion_flashcards = get_option('lexicon_presentacion_flashcards');
            ?>
            <input type="hidden" id="presentacion_flashcards" name="presentacion_flashcards" value="<?php echo $presentacion_flashcards;?>">
            <label>&nbsp;<input id ="presentacion_flashcards1" type='checkbox' value="1"  <?php if($presentacion_flashcards == "botones") echo 'checked'?>
		    onclick="fpresentacion_flashcards(1)"/>&nbsp;<?php _e('Pulsar botones.', 'lexicon')?> </label><br>
		    <label>&nbsp;<input id ="presentacion_flashcards2" type='checkbox' value="1" <?php if($presentacion_flashcards == "escribir") echo 'checked'?>
		    onclick="fpresentacion_flashcards(2)"
		    />&nbsp;<?php _e('Escribir respuesta y comprobar.', 'lexicon')?> </label><br>
		    <label>&nbsp;<input id ="presentacion_flashcards3" type='checkbox' value="1" <?php if($presentacion_flashcards == "aleatorio") echo 'checked'?>
		    onclick="fpresentacion_flashcards(3)"
		    />&nbsp;<?php _e('Aleatorio.', 'lexicon')?> </label>
		  </div>
	   </div>

	   <!-- Modificar la forma de realizar el curso-->
        <div class="postbox">
          <h3 class="hndle"><span><?php _e('Forma de realizar el curso', 'lexicon') ?></span></h3>
          <div class="inside" style="padding:8px">
            <?php
              $realizar_curso = get_option('lexicon_realizar_curso');
            ?>
            <input type="hidden" id="realizar_curso" name="realizar_curso" value="<?php echo $realizar_curso;?>">
            <label>&nbsp;<input id ="realizar_curso1" type='checkbox' value="1"  <?php if($realizar_curso == "categorias") echo 'checked'?>
		    onclick="frealizar_curso(1)"/>&nbsp;<?php _e('Realizar el curso por categorías.', 'lexicon')?> </label><br>
		    <label>&nbsp;<input id ="realizar_curso2" type='checkbox' value="1" <?php if($realizar_curso == "completo") echo 'checked'?>
		    onclick="frealizar_curso(2)"
		    />&nbsp;<?php _e('Realizar el curso completo.', 'lexicon')?> </label>
		  </div>
	   </div>

	   <!-- Mostrar estadísticas en el gráfico de curso-->
        <div class="postbox">
          <h3 class="hndle"><span><?php _e('Estadísticas en el gráfico del curso', 'lexicon') ?></span></h3>
          <div class="inside" style="padding:8px">
            <?php
              $mostrar_estadisticas = get_option('lexicon_mostrar_estadisticas');
            ?>
            <input type="hidden" id="mostrar_estadisticas" name="mostrar_estadisticas" value="<?php echo $mostrar_estadisticas;?>">
            <label>&nbsp;<input id ="mostrar_estadisticas1" type='checkbox' value="1"  <?php if($mostrar_estadisticas == "1") echo 'checked'?>
		    onclick="fmostrar_estadisticas(1)"/>&nbsp;<?php _e('Con estadísticas.', 'lexicon')?> </label><br>
		    <label>&nbsp;<input id ="mostrar_estadisticas2" type='checkbox' value="1" <?php if($mostrar_estadisticas == "0") echo 'checked'?>
		    onclick="fmostrar_estadisticas(2)"
		    />&nbsp;<?php _e('Sin estadísticas.', 'lexicon')?> </label>
		  </div>
	   </div>

	   <!-- Utilizar el algoritmo de repetición espaciada o no-->
        <div class="postbox">
          <h3 class="hndle"><span><?php _e('Algoritmo de repeteción espaciada', 'lexicon') ?></span></h3>
          <div class="inside" style="padding:8px">
            <?php
              $algoritmo = get_option('lexicon_algoritmo');
            ?>
            <input type="hidden" id="algoritmo" name="algoritmo" value="<?php echo $algoritmo;?>">
            <label>&nbsp;<input id ="algoritmo1" type='checkbox' value="1"  <?php if($algoritmo == "1") echo 'checked'?>
		    onclick="falgoritmo(1)"/>&nbsp;<?php _e('Usar el algoritmo de repectición espaciada para estudiar los cursos.', 'lexicon')?> </label><br>
		    <label>&nbsp;<input id ="algoritmo2" type='checkbox' value="1" <?php if($algoritmo == "0") echo 'checked'?>
		    onclick="falgoritmo(2)"
		    />&nbsp;<?php _e('Usar un orden fijado para estudiar el curso.', 'lexicon')?> </label>
		  </div>
	   </div>
	
	<p class="submit">
	<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
	<span id="autosave"></span>
	<input type="submit" name="submit" value="<?php _e('Guardar cambios', 'lexicon') ?>" style="font-weight: bold;" />
	</p>
	
	</div>
    </div>
	</form>
	
	</div>
	<div id="lexicon-sidebar">
			<?php include(LEXICON_DIR."/includes/views/lexicon_sidebar.php");?>
	</div>
</div>

</body>
</html>