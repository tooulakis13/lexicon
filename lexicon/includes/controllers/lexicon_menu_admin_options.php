<?php
$user_id = get_current_user_id();
if(isset($_REQUEST['submit']) and $_REQUEST['submit']) {

	update_option( "lexicon_cleanup_db", @$_POST['cleanup_db'] );
	update_option("lexicon_clear_data_deactive", @$_POST['deactive_clear_data']);
	update_option("lexicon_custom_list_pages_default", @$_POST['custom_list_pages_default']);
	update_user_meta( $user_id, "lexicon_custom_list_pages", @$_POST['custom_list_pages']);
	
	print '<div id="message" class="updated fade"><p>' . __('Options updated', 'lexicon') . '</p></div>';
}
?>
<div class="wrap">

	<h2><?php _e("Lexicon Options", 'lexicon'); ?></h2>

	<div class="postbox-container" style="width:73%;margin-right:2%;">	

	<form name="post" action="" method="post" id="post">
	<div id="poststuff">
	<div id="postdiv" class="postarea">
	
	<div class="postbox">
		<h3 class="hndle"><span><?php _e('Database', 'lexicon') ?></span></h3>
		<div class="inside" style="padding:8px">
		<?php 
			$cleanup_db = get_option('lexicon_cleanup_db');
			$clear_data_db = get_option('lexicon_clear_data_deactive');
			$meta = get_user_meta($user_id, 'lexicon_custom_list_pages', true);
			if($meta) {
				$custom_list_pages = $meta;
			} else {
			$custom_list_pages = get_option('lexicon_custom_list_pages_default');
			add_user_meta( $user_id, 'lexicon_custom_list_pages_default', $custom_list_pages, false );
			}
			$custom_list_pages_default = get_option('lexicon_custom_list_pages_default');
		?>
        <p>
		<input type='checkbox' value="1" name='cleanup_db' <?php if($cleanup_db) echo 'checked'?> 
		onclick="jQuery(this).attr('value', this.checked ? 1 <?php $cleanup_db='1'?>: 0 <?php $cleanup_db='0'?>)"/>
		<?php _e('Remove all lexicon database entries upon uninstall (Including Courses).', 'lexicon')?>
        </p>
        <p>
        <input type='checkbox' value="1" name='deactive_clear_data' <?php if($clear_data_db) echo 'checked'?> 
		onclick="jQuery(this).attr('value', this.checked ? 1 <?php $clear_data_db='1'?>: 0 <?php $clear_data_db='0'?>)"/>
		<?php _e('Remove all lexicon data upon deactivation (Including Courses).', 'lexicon')?>
        </p>
        <p>
        <input type='number' name='custom_list_pages_default' value='<?php echo $custom_list_pages_default; ?>'/>
		<?php _e('Default number of elements on lists per page', 'lexicon')?>
        </p>
        <p>
        <input type='number' name='custom_list_pages' value='<?php echo $custom_list_pages; ?>'/>
		<?php _e('Personal number of elements on lists per page', 'lexicon')?>
        </p>
		</div>
	</div>

	<p class="submit">
	<input type="hidden" id="user-id" name="user_ID" value="<?php echo get_current_user_id(); ?>" />
	<span id="autosave"></span>
	<input type="submit" name="submit" value="<?php _e('Save Changes', 'lexicon') ?>" style="font-weight: bold;" />
	</p>
	
	</div>
    </div>
	</form>
	</div>
</div>