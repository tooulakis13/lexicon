<?php
/**
 * mls_lexicon custom list class
 * 
 * @author Patryk Miroslaw <miroslaw.patryk@gmail.com>
 * @package WordPress
 * @subpackage WP_List_Table
 * @version 0.5
 * @desciption Customized WP_List_Table class for MLS Lexicon plugin.
 * Handles creation of common elements, parsing data into tables, filtering 
 * search results.
 * @access private
 */
 
 /* Make sure that WordPress List Table class is loaded */
 if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	
}
require_once(ABSPATH . 'wp-admin/includes/template.php' );

if( ! class_exists('WP_Screen') ) {
require_once( ABSPATH . 'wp-admin/includes/screen.php' );
}

class MLS_Lexicon_List_Table extends WP_List_Table {
	 /**
     * Singluar and plurar data slugs
     * @var string 
     */
	  private $records_singular;
	  private $records_plurar;
	  /**
	  * Table ajax support
	  * @var boolean
	  */
	  private $use_ajax;
	  /**
	  * Number of rows per page on displayed list
	  * @var int
	  */
	  private $per_page;
	  
	  
	/*
	 * Constructor.
	 * @access private
	 * @see WP_List_Table::__construct() for more information on default arguments.
	 * @param string $llt_Records_sing A singular data slug
	 * @param string $llt_Records_plur A plural data slug
	 * @param boolean $llt_use_ajax
	 */
	  function __construct($llt_Records_sing, $llt_Records_plur, $llt_use_ajax){
        global $status, $page; 
		/*
		 *	Assign params
		 */
        $this->records_singular = $llt_Records_sing;
		$this->records_plurar = $llt_Records_sing;
		$this->use_ajax = $llt_use_ajax;
		/*
		 * @var $user_id Currently logged in WP user ID
		 * @var $meta Current users' list length per page
		 * Fetch user page settings or install them using plugin defaults.
		 */
		$user_id = get_current_user_id();
		$meta = get_user_meta($user_id, 'mls_lexicon_custom_list_pages', true);
			if($meta) {
				$custom_list_pages = $meta;
			} else {
			$custom_list_pages = get_option('mls_lexicon_custom_list_pages_default');
			add_user_meta( $user_id, 'mls_lexicon_custom_list_pages_default', $custom_list_pages, false );
			}		
		$this->per_page = $custom_list_pages;
        /*
		 *	Set Parent Class Defaults
		 */
        parent::__construct( array(
            'singular'  => $this->records_singular,    
            'plural'    => $this->records_plurar,    
            'ajax'      => $this->use_ajax ,
			'screen'      => 'interval-list'      
        ) );
 	
  		}
     	/*
		 *	Create Checkbox
		 */
	  function column_cb($item, $id = NULL) {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'],  $item[$id]);    
    }
		/*
		 *	Checkbox provided by parent, Null to supress E_STRICT warning
		 */
	function get_columns($columns = NULL){
		$arr = array_reverse($columns, true);
		$arr ['cb'] = '<input type="checkbox" />';
		return array_reverse($arr, true);
 }		/*
 		 *	Return array of sortable columns
		 *	Null to supress E_STRICT warning
		 */
 	function get_sortable_columns($columns = NULL) {
		$arr = array();
		foreach($columns as $column) {
		$arr[$column] = array($column, false);	
		}
	  return $arr;
	}
		/*
		 *	Comparison function for usort()
		 *	@see function.usort in PHP Manual
		 */
	 function usort_reorder( $a, $b ) {  				
  		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'ID';// If no sort, default to title
  		$order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';// If no order, default to asc
  		$result = strcmp( $a[$orderby], $b[$orderby] );// Determine sort order
  		return ( $order === 'asc' ) ? $result : -$result;// Send final sort direction to usort
	}
		/*
		 * Prepare table items for display, in two phases (states)
		 * @param string $sql A query to be executed, phase 1
		 * @param integer $state Determines phase to be used
		 * @param Object::MLS_Lexicon_List_Table $obj Lexicon List Table instance reference.
		 * @param array $data Array of data to be insterted into table, phase 2
		 */
	function prepare_items($sql = NULL, $state = NULL, $isSql = NULL, $obj = NULL, $data = NULL) {
	global $wpdb;
	/*
	 *	Phase 1
	 */
	if($state == 1) {
		if($isSql) {
		$res = $wpdb->get_results($sql);
		} else {
		$res = $sql;
		}
		if(!isset($_POST['s'])){ /* There were no searching */
	return $res;
		} else {  /* modify result based on Search query */
		$filtered_res = array();
			foreach($res as $row){
				$cont = true;
				foreach ($row as $field => $value) {
					/* Explode query into keywords */
					$search_keys = explode(" ", $_POST['s']);
					foreach($search_keys as $key) {
					if(strpos(strtolower($value),strtolower($key)) !== false) {
						$filtered_res[] = $row;
						/* Found match for current keyword, break */
						$cont = false;
						break;
					}
				}
				if(!$cont) { break; } /* Found match for current row, break */
				}
			}
	return $filtered_res;
		}
	} 
	/*
	 *	Phase 2
	 */
	elseif($state == 2) {
		
if($data !== NULL) { /* Check if data was supplied */
usort( $data, array(&$obj, 'usort_reorder')); /* Sort supplied data */
$current_page = $obj->get_pagenum();
$total_items = count($data);
/*
 * Divide data into pages
 */
$data = array_slice($data,(($current_page-1)*$this->per_page),$this->per_page);
   $obj->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $this->per_page,                     
            'total_pages' => ceil($total_items/$this->per_page)  
        ) ); 
}
/* Send prepared data to MLS_Lexicon_List_Table instance */
 $obj->items = $data;
 $columns  = $obj->get_columns();
 $hidden   = array();
 $sortable = $obj->get_sortable_columns();
 $obj->_column_headers = array( $columns, $hidden, $sortable );

	
	}
			
  	
	}
	
/*
 * 	Display prepared table
 *	@param Object::MLS_Lexicon_List_Talbe $obj List table instance
 *	@param string $new_vars String to display for 'New' button
 */
public function display($obj = null, $new_vars = null) {
		?>
        <form method="post">
  <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
  <?php $obj->search_box('search', 'search_id'); ?>
  
</form>

<?php if(isset($new_vars)) {
	if(!is_array($new_vars[0])) {
	 ?>
		<p><a href="<?php echo "?page=".$_REQUEST['page'];
			if($new_vars[0] != NULL) {
				echo '&action='.$new_vars[0]; 
			}
?>" class="add-new-h2"><?php echo $new_vars[1]; ?></a></p>
<?php  
} else {
echo '<p>';
foreach($new_vars as $new_var) {
?>
<a href="<?php echo "?page=".$_REQUEST['page'];

if($new_var[0] != NULL) {
echo '&action='.$new_var[0]; 
}
?>" class="add-new-h2"><?php echo $new_var[1]; ?></a>
	
<?php
}
echo '</p>';
}
	
}


?>




  <form id="studs-filter" method="get">
    <input type="hidden" name="page" value="<?php
	echo $_REQUEST['page'];
?>" />
    <?php
	/* Call parent class display method, prints out the table */
	parent::display();
?>
  </form>
  <?php
	}
	
};