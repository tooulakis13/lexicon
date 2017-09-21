<html>
<head>
<script type="text/javascript">
var ajaxurl= '<?php echo admin_url("admin-ajax.php"); ?>';
var value = 0;

//This function is for showing the dialog window in front with opacity
/*function modal()
{
  var alturaContenedor = document.body.scrollHeight;
  document.getElementById('fade').style.height = alturaContenedor + 'px';
  document.getElementById('fade').style.display='block';
  document.getElementById('light').style.display='block';
}*/

jQuery(document).ready(function()
{
  jQuery("#cursoDestino").change(function(event) // Course selection handel. If first option is selected...
  {
    jQuery("#ingresar").hide('slow');
    var cursDes = jQuery("#cursoDestino").find(':selected').val();
    value = 1;
    jQuery("#cursoBase").empty();

    //...find available courses....
    var data = {action: 'my_action_one',  nombreBase: cursDes, func: value};
    
    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data, function(response) //...insert available courses as selection
    {
      var obj = jQuery.parseJSON(response);
      var i;
      jQuery("#cursoBase").append("<option>Select language</option>");
      for(i=0; i<obj.length; i++)
	    jQuery("#cursoBase").append(obj[i]);
    });

  });

  jQuery("#cursoBase").change(function(event)// When course languages are selected...
  {
    jQuery("#ingresar").hide('slow');
    var cursBase = jQuery("#cursoBase").find(':selected').val();
    var cursDest = jQuery("#cursoDestino").find(':selected').val();
    value = 2;
    
    jQuery("#cursoNivel").empty();

    // Set up data
    var data = {action: 'my_action_one',  cursBase: cursBase, cursDest: cursDest, func: value};
    
    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data, function(response)//...show course levels available
    {
      var obj = jQuery.parseJSON(response);
      var i;
      jQuery("#cursoNivel").append("<option>Select level</option>");
      for(i=0; i<obj.length; i++)
	    jQuery("#cursoNivel").append(obj[i]);
    });
    
  });

  jQuery("#cursoNivel").change(function(event) // When course level is selected...
  {
    if(jQuery("#cursoNivel").find(':selected').val()=="Select level") 
      jQuery("#ingresar").hide('slow');
    else
      jQuery("#ingresar").show('slow'); //...show subscription button
  });
  
  jQuery("#Curso").click(function() // Sing up for course
  {
    var cursBase = jQuery("#cursoBase").find(':selected').val();
    var cursDest = jQuery("#cursoDestino").find(':selected').val();
    var curslvl = jQuery("#cursoNivel").find(':selected').val();
    value = 3;

    /*jQuery("#Cursos").load(url+'?cursBase='+cursBase+'&cursDest='+cursDest+'&curslvl='+curslvl+'&func='+value+'');*/

    //The name for the action is the first parameter
    var data = {action: 'my_action_one',  cursBase: cursBase, cursDest: cursDest, curslvl: curslvl, func: value};
    
    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data, function(response)
    {
		console.log(response);
      var obj = jQuery.parseJSON(response);
      if(obj[0] == 1)       
        window.location = window.location.href.split("?")[0];
      if(obj[0] == 0)
      	alert("Already enrolled in this course.");
    });

  });					 
	
});
</script>

<link href='http://fonts.googleapis.com/css?family=Open+Sans:600' rel='stylesheet' type='text/css'>

</head>
<body>

 <!-- Divs utilizados para hacer la capa modal-->
 <!--	  
		<div id="fade" class="overlay" onclick = "document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none'">
		</div>

		<div id="light" class="modal">
		<img class="imag_modal" src="<?php /*?><?php echo $PlugDir?><?php */?>/lexicon/img/ico/close15.png" onclick = "document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none'"/>
			
			<div style="display:inline;">
				<h3> Añadir Deck </h3>                    
			</div>
				<form name="form_deck" method='post'  >
				
				<div style="margin-right:15px">
					<input type="text"  id="nombreDeck" name="nombreDeck" style="width:100%;" placeholder=" Nombre del deck"/>
				</div>
				
				<div style="margin-right:15px;">
					<input style="float:right;" type="button" value="Cancelar" class="button-secondary delete" onClick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none'">
					
										
					<input style="float:right;" type="submit" id="Guardar" name="Guardar" value="Guardar" class="button button-primary"  >
				</div>
				</form>                    
			
		</div>
	  -->
	  <!--****************************************-->

<div class="head"><p >LEXICON - <?php _e('Enroll', 'lexicon')?></p></div>				 



<div id="destino">
  <p  class="pCurso"><?php _e('Course to learn:', 'lexicon')?></p>
    <select id="cursoDestino" name="cursoDestino" class="idiomas"> 
    <option value=""><?php _e('Select language', 'lexicon')?></option>              
      <?php 
        foreach ($course_base as $curso){?>							
          <option value="<?php echo $curso->lang_2?>"><?php echo $curso->lang_2?></option>
      <?php }?>		               
    </select>    	
</div>
<div id="base">
  <p  class="pCurso"><?php _e('Course Language:', 'lexicon')?></p>							
    <select id="cursoBase" name="cursoBase" class="idiomas">   
							    
    </select>  
</div>

<div id="nivel">        	
  <p class="pCurso"><?php _e('Level:', 'lexicon')?></p>
    <select id="cursoNivel" name="cursoNivel" class="idiomas">
    </select>
</div>

<div id="ingresar" style="display:none;">
  <input id="Curso" type="button" style="width:20%; font-size:14px;" value="<?php _e('Enroll', 'lexicon')?>"></input> 
</div>
<div id="Cursos"></div>

</body>
</html>