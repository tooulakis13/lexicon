<?php
/*
 *	MLS Lexicon Administrator Options Panel
 *	Modify all available plugin settings
 */
$user_id = get_current_user_id();
if(isset($_REQUEST['submit']) and $_REQUEST['submit']) {
		
}
global $wpdb;
?>

<div class="wrap">
  <h2>
    <?php _e("MLS Lexicon Data Import/Export", 'mls_lexicon'); ?>
  </h2>
  <div class="postbox-container" style="margin-right:2%;">
    <div id="poststuff">
      <div id="postdiv" class="postarea">
        <div class="postbox" style="float: right; margin-left: 20px;">
          <h3 class="hndle"><span>
            <?php _e('Statistics', 'mls_lexicon') ?>
            </span></h3>
          <div class="inside" style="padding:8px">
            <?php /* Fetch statistics */
			
			$modules = $wpdb->get_results("SELECT id, lang, level FROM {$wpdb->prefix}mls_lexicon_lang_mod");
			$modules_nr = count($modules);	// Number of modules
			$courses = $wpdb->get_results("SELECT id, lang_1, lang_2, level FROM {$wpdb->prefix}mls_lexicon_course");
			$course_nr = count($courses);	// Number of courses
			$cat_nr = $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->prefix}mls_lexicon_categories");		// Number of avialable categories
			$cat_trans = $wpdb->get_results("SELECT DISTINCT lang FROM {$wpdb->prefix}mls_lexicon_categories_trans"); // Categories translations
			$code_nr = $wpdb->get_var("SELECT COUNT(code) FROM {$wpdb->prefix}mls_lexicon_codes");		// Number of abstract codes
			$img_nr = $wpdb->get_var("SELECT COUNT(code) FROM {$wpdb->prefix}mls_lexicon_codes_img");		// Number of images
			$missing_words = $wpdb->get_results("select c.code from {$wpdb->prefix}mls_lexicon_codes c 
    where not exists (select x.code from  {$wpdb->prefix}mls_lexicon_words x WHERE x.code = c.code)");	// Number of missing words (not existing in available modules
			$missing_imgs = $wpdb->get_results("SELECT c.code FROM {$wpdb->prefix}mls_lexicon_codes c WHERE c.code NOT IN (SELECT img.code FROM {$wpdb->prefix}mls_lexicon_codes_img img)");	// Number of missing images for words
			$missing_cat_imgs  = $wpdb->get_results("SELECT c.id FROM {$wpdb->prefix}mls_lexicon_categories c WHERE c.id NOT IN (SELECT img.id FROM {$wpdb->prefix}mls_lexicon_categories_img img)"); // Number of missing category images
			$missing_cat_trans = $wpdb->get_results("SELECT t.cat_id, CONCAT_WS('-', c.notion_type, c.class, c.subclass, c.mgroup, c.subgroup) as 'cat_code' FROM {$wpdb->prefix}mls_lexicon_categories_trans t LEFT JOIN {$wpdb->prefix}mls_lexicon_categories c ON c.id=t.cat_id WHERE t.name IS NULL or t.name = ''"); // Number of missing category translations
			
			
		
		
			
		?>
            <div class="mls-data-list inside" style="cursor: pointer;">
              <?php _e('Number of language modules', 'mls_lexicon'); ?>
              : <?php echo $modules_nr; ?>
              <ul style="display:none;">
                <?php
		foreach($modules as $mod) {
			echo '<li><a href="?page=mls_lexicon_admin_lang_modules&action=edit&mod_id='.$mod->id.'">'.$mod->lang.' '.$mod->level.'</a></li>';
		}
		?>
              </ul>
            </div>
            <div class="mls-data-list inside" style="cursor: pointer;">
              <?php _e('Number of courses', 'mls_lexicon'); ?>
              : <?php echo $course_nr; ?>
              <ul style="display:none;">
                <?php
		foreach($courses as $c) {
			echo '<li><a href="?page=mls_lexicon_admin_courses&action=edit&course_id='.$c->id.'">'.$c->lang_1.'-'.$c->lang_2.' '.$c->level.'</a></li>';
		}
		?>
              </ul>
            </div>
            <div class="mls-data-list inside" style="cursor: pointer;">
              <?php _e('Number of categories', 'mls_lexicon'); ?>
              : <?php echo $cat_nr; ?></div>
            <div class="mls-data-list inside" style="cursor: pointer;">
              <?php _e('Number of word codes', 'mls_lexicon'); ?>
              : <?php echo $code_nr; ?></div>
            <div class="mls-data-list inside" style="cursor: pointer;">
              <?php _e('Number of word images', 'mls_lexicon'); ?>
              : <?php echo $img_nr; ?></div>
            <div class="mls-data-list inside" style="cursor: pointer;">
              <?php _e('Missing word codes', 'mls_lexicon'); ?>
              : <?php echo count($missing_words); ?>
              <ul style="display:none;">
                <?php
		foreach($missing_words as $ms) {
			echo '<li>Word Code: '.$ms->code.'</li>';
		}
		?>
              </ul>
            </div>
            <div class="mls-data-list inside" style="cursor: pointer;">
              <?php _e('Missing word images', 'mls_lexicon'); ?>
              : <?php echo count($missing_imgs); ?>
              <ul style="display:none;">
                <?php
		foreach($missing_imgs as $ms) {
			echo '<li>Word Code: '.$ms->code.'</li>';
		}
		?>
              </ul>
            </div>
            <div class="mls-data-list inside" style="cursor: pointer;">
              <?php _e('Missing category images', 'mls_lexicon'); ?>
              : <?php echo count($missing_cat_imgs); ?>
              <ul style="display:none;">
                <?php
		foreach($missing_cat_imgs as $ms) {
			echo '<li>Cat ID: '.$ms->id.'</li>';
		}
		?>
              </ul>
            </div>
            <div class="mls-data-list inside" style="cursor: pointer;">
              <?php _e('Missing category translations', 'mls_lexicon'); ?>
              : <?php echo count($missing_cat_trans); ?>
              <ul style="display:none;">
                <?php
		foreach($missing_cat_trans as $ms) {
			echo '<li>Cat ID: '.$ms->cat_id.', ISO: '.$ms->cat_code.'</li>';
		}
		?>
              </ul>
            </div>
          </div>
        </div>
        <div class="postbox" style="float: left; margin-left: 20px;">
          <h3 class="hndle"><span>
            <?php _e('Import Data', 'mls_lexicon') ?>
            </span></h3>
          <div class="inside" style="padding:8px">
            <form id="form-upload" action="" type="post" enctype="multipart/form-data">
            <?php wp_nonce_field('admin_data', 'admin_data_import', false, true); ?>
              <p>
                <?php _e('Chose a file to import', 'mls_lexicon'); echo '.<br> '; _e('Accepted file formats:', 'mls_lexicon'); echo ' <i>zip, csv, svg, png, gif, jpg.</i>'; ?>
              </p>
              <input type="file" id="import-files" class="" accept="image/gif, image/jpeg, image/png, image/svg+xml, application/zip, .csv" name="import-files" multiple required>
              <p>
                <input name="submit" id="button_import" type="submit" class="button button-hero button-primary" value="<?php esc_attr_e('Import', 'mls_lexicon'); ?>" />
              </p>
            </form>
          </div>
        </div>
        <div class="postbox" style="float: left; margin-left: 20px;">
          <h3 class="hndle"><span>
            <?php _e('Export Data', 'mls_lexicon') ?>
            </span></h3>
          <div class="inside" style="padding:8px"> 
          <p>
          <?php _e('Choose segments to export', 'mls_lexicon'); echo ':'; ?>
          </p>
          <form id="form-export" action="" type="post">
          <?php wp_nonce_field('admin_data', 'admin_data_export', false, true); ?>
          <ul><li>
        
		<input type='checkbox' value="1" name='ex_cat'/>
		<?php _e('Categories', 'mls_lexicon')?>&nbsp; <i><a><?php _e('Expand', 'mls_lexicon'); ?></a></i>
    
        <ul style="display: none; margin-left: 1.3em;">
        <li>
        
		<input type='checkbox' value="1" name='ex_cat_code'/>
		<?php _e('Category codes', 'mls_lexicon'); ?>
       
        </li>
        <li>
        
		<input type='checkbox' value="1" name='ex_cat_img'/>
		<?php _e('Category images', 'mls_lexicon'); ?>
       
        </li>
        <li>
        
		<input type='checkbox' value="1" name='ex_cat_trans'/>
		<?php _e('Category translations', 'mls_lexicon'); ?>&nbsp; <i><a><?php _e('Expand', 'mls_lexicon'); ?></a></i>
       <ul style="display: none; margin-left: 1.3em;">
       
       <?php
		foreach($cat_trans as $ct) {
			?>
            <li>
        <input type='checkbox' value="<?php echo $ct->lang; ?>" name='ex_cat_trans_lang[]'/>
		<?php echo $ct->lang; ?>
        </li>
            <?php
			
		}
		?>
        
        </ul>
       	
        </li>
        </ul>
        </li>
        <li>
        <input type='checkbox' value="1" name='ex_codes'/>
		<?php _e('Word codes', 'mls_lexicon')?>&nbsp; <i><a><?php _e('Expand', 'mls_lexicon'); ?></a></i>
    
        <ul style="display: none; margin-left: 1.3em;">
        <li>
        
		<input type='checkbox' value="1" name='ex_codes_word'/>
		<?php _e('Word codes', 'mls_lexicon'); ?>
       
        </li>
        <li>
        
		<input type='checkbox' value="1" name='ex_codes_img'/>
		<?php _e('Word images', 'mls_lexicon'); ?>
       
        </li>
        </ul>
        </li>
        <li>
        <input type='checkbox' value="1" name='ex_mod'/>
		<?php _e('Modules', 'mls_lexicon')?>&nbsp; <i><a><?php _e('Expand', 'mls_lexicon'); ?></a></i>
    
        <ul style="display: none; margin-left: 1.3em;">
        <?php
		foreach($modules as $mod) {
			?>
            <li>
            <input type='checkbox' value="<?php echo $mod->id; ?>" name='ex_mods[]'/>
            <?php 
			echo $mod->lang.' '.$mod->level;
			?></li>
            <?php
		}
		?>
        </ul>
        <li>
        <input type='checkbox' value="1" name='ex_course'/>
		<?php _e('Courses', 'mls_lexicon')?>&nbsp; <i><a><?php _e('Expand', 'mls_lexicon'); ?></a></i>
    
        <ul style="display: none; margin-left: 1.3em;">
        <?php
		foreach($courses as $c) {
			?>
            <li>
            <input type='checkbox' value="<?php echo $c->id; ?>" name='ex_courses[]'/>
            <?php 
			echo $c->lang_1.'-'.$c->lang_2.' '.$c->level;
			?></li>
            <?php
		}
		?>
        </ul>
        <li>
        <input type='checkbox' value="1" name='ex_studs'/>
		<?php _e('Student data', 'mls_lexicon')?>
        </li>
        
        </ul>
        	<p> <div class="button action" id="select_all" > <?php echo __('Select All', 'mls_lexicon') ?> </div>
            </p>
          <p>
                <input name="submit" id="button_export" type="submit" class="button button-hero button-primary" value="<?php esc_attr_e('Export', 'mls_lexicon'); ?>" />
              </p>
          </form>
          </div>
        </div>
        <div style="clear: both;"></div>
      </div>
    </div>
  </div>
</div>
<div style="position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);"><div class="spinner"></div>
  <div id="message" class="notice inline" style="text-align:center; display: none;">
    <p style="font-size: 1.2em; line-height: 2.1em;"><i></i></p>
  </div>
  
</div>
<script>
jQuery(document).ready(function(){
	jQuery("#select_all").click(function(e) { // Select All
					jQuery("#form-export input:checkbox").each(function(index, element) {
						jQuery(element).prop('checked', true);
					});
					jQuery("#form-export>ul").find('ul').each(function(index, element) {
						jQuery(element).show();
					});
					jQuery("#form-export").find('a').each(function(index, element) {
						jQuery(this).html((jQuery(this).html() === "<?php _e('Expand', 'mls_lexicon'); ?>" ? "<?php _e('Hide', 'mls_lexicon'); ?>" : "<?php _e('Expand', 'mls_lexicon'); ?>"));
					});
					
				});
	jQuery("#form-export i>a").click(function(e) { // Select All
						
						jQuery(this).html((jQuery(this).html() === "<?php _e('Expand', 'mls_lexicon'); ?>" ? "<?php _e('Hide', 'mls_lexicon'); ?>" : "<?php _e('Expand', 'mls_lexicon'); ?>"));
						jQuery(this).parent().parent('li').children('ul').toggle();
				});
jQuery("#form-export input:checkbox").click(function(e) { // Select All
var check = jQuery(this);
					jQuery(this).parent().children("ul").children('li').each(function(index, element) {
						jQuery(element).children('input:checkbox').prop('checked', (jQuery(check).prop('checked') === true ? true : false));
						var check2 = jQuery(this).children('input:checkbox');
						jQuery(this).children("ul").children('li').each(function(index, element) {
						jQuery(element).children('input:checkbox').prop('checked', (jQuery(check2).prop('checked') === true ? true : false));
					});
					});
				});
	jQuery(".mls-data-list").click(function(e) {
    	jQuery(this).children('ul').toggle(250);
	});
	jQuery("#message").click(function(e) {
    	jQuery(this).hide(200);
	});
	jQuery("#form-export").submit(function(e) {
		 e.preventDefault();
		jQuery("#message").html('');
		 jQuery("#button_export").attr('disabled', 'disabled');
					jQuery(".spinner").show();
					// Variables
					var files = jQuery("#import-files")[0].files;
					var nonce = jQuery("#admin_data_export").val();
					var files_data = new FormData();
					files_data.append('action', 'admin_data');
					files_data.append('act', 'admin_data_export');
					files_data.append('nonce', nonce);
					jQuery("#form-export input:checkbox").each(function(index, element) {
						if(jQuery(element).prop('checked')) {
							files_data.append(jQuery(element).attr('name'), jQuery(element).val());
						}
					});
					
			
		
					
					jQuery.ajax({
						type: 'POST',
						dataType: "json",
						url: ajaxurl,
						data: files_data,
						cache: false,
						processData: false, // Don't process the files
        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
						success: function(response) {
							jQuery(".spinner").hide();
							if(response.type === 'success') {
								jQuery("#message").html("<p style='font-size: 1.2em; line-height: 2.1em;'><i>"+response.msg+"</i></p>");
								jQuery("#message").removeClass("notice-error");
								jQuery("#message").removeClass("notice-info");
								jQuery("#message").addClass("notice-success");
								jQuery("#button_export").removeAttr('disabled');
								jQuery("#message").slideDown(200, function() {
									if(response.zip !== '') {
										file = response.zip;
										jQuery("#message").append("<div id='lex_exp_button'><p style='font-size: 1.2em; line-height: 2.1em;'><?php echo __('Your download is ready', 'mls_lexicon');?></p><a href='<?php echo plugin_dir_url( __FILE__ );?>mls_lexicon_fd.php?zip="+response.zip+"' target='_parent'><div class='button button-primary'>Download</div></a></div>");
										
									}
								});
							}
							else {
								var err_msg = response.msg;								
								jQuery("#message").html("<p style='font-size: 1.2em; line-height: 2.1em;'><i>"+err_msg+"</i></p>");
								jQuery("#message").removeClass("notice-success");
								jQuery("#message").removeClass("notice-info");
								jQuery("#message").addClass("notice-error");
								jQuery("#button_export").removeAttr('disabled');
								jQuery("#message").slideDown(200);
								
							}
							
							
						}
					});
				});
jQuery("#form-upload").submit(function(e) {
		 e.preventDefault();
		 jQuery("#message").html('');
		 jQuery("#button_upload").attr('disabled', 'disabled');
					jQuery(".spinner").show();
					// Variables
					var files = jQuery("#import-files")[0].files;
					var nonce = jQuery("#admin_data_import").val();
					var files_data = new FormData();
					files_data.append('action', 'admin_data');
					files_data.append('act', 'admin_data_import');
					files_data.append('nonce', nonce);
    jQuery.each(files, function(key, value)
    {
        files_data.append(key, value);
    });
			
		
					
					jQuery.ajax({
						type: 'POST',
						dataType: "json",
						url: ajaxurl,
						data: files_data,
						cache: false,
						processData: false, // Don't process the files
        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
		xhr: function() {
                var myXhr = jQuery.ajaxSettings.xhr();
                if(myXhr.upload){
                    myXhr.upload.addEventListener('progress',progress, false);
                }
                return myXhr;
        },
						success: function(response) {
							jQuery(".spinner").hide();
							if(response.type === 'success') {
								jQuery("#message").html("<p style='font-size: 1.2em; line-height: 2.1em;'><i>"+response.msg+"</i></p>");
								jQuery("#message").removeClass("notice-error");
								jQuery("#message").removeClass("notice-info");
								jQuery("#message").addClass("notice-success");
								jQuery("#button_import").removeAttr('disabled');
								jQuery("#message").slideDown(200).delay(1000).slideUp(200, function() {
								window.location = '?page=mls_lexicon_admin_data';
							});
							}
							else {
								var err_msg = response.msg;								
								jQuery("#message").html("<p style='font-size: 1.2em; line-height: 2.1em;'><i>"+err_msg+"</i></p>");
								jQuery("#message").removeClass("notice-success");
								jQuery("#message").removeClass("notice-info");
								jQuery("#message").addClass("notice-error");
								jQuery("#button_import").removeAttr('disabled');
								jQuery("#message").slideDown(200);
								
							}
							
							
						}
					});
				});
function progress(e){
	
    if(e.lengthComputable){
        var max = e.total;
        var current = e.loaded;

        var Percentage = (current * 100)/max;
		jQuery("#message").slideDown(200);
		jQuery("#message").removeClass("notice-error");
		jQuery("#message").removeClass("notice-success");
		jQuery("#message").addClass("notice-info");
		jQuery("#message").html('');
		jQuery("#message").html("<p style='font-size: 1.2em; line-height: 2.1em;'><i></i></p>");
		jQuery("#message>p>i").html("<?php echo __('Upload progress', 'mls_lexicon'); ?> : "+Math.round(Percentage)+"%");

        if(Percentage >= 100)
        {
			jQuery("#message").html('');
		jQuery("#message").html("<p style='font-size: 1.2em; line-height: 2.1em;'><i></i></p>");
           jQuery("#message>p>i").html("<?php echo __('Upload Completed.', 'mls_lexicon'); ?> <?php echo __('Installing data.', 'mls_lexicon'); ?>");
        }
		
    }  
 }
});
</script>