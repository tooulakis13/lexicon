<?php
/**
 * Lexicon custom list class
 * 
 * @author Patryk Miroslaw <miroslaw.patryk@gmail.com>
 * @package WP_List_Table
 * @version 1.0
 * @usage
 * Class accepts following parameters, in order:
 * @par string Singular name for listed records
 * @par string Plurar name for listed records
 * @par boolean Table ajax support
 * @par
 */
 
 if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class Lexicon_List_Table extends WP_List_Table {
	 /**
     * Variable(s) name to pass in actions
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
	  * Number of rows per page
	  * @var int
	  */
	  private $per_page;
	  
	  function __construct($llt_Records_sing, $llt_Records_plur, $llt_use_ajax){
        global $status, $page;
        $this->records_singular = $llt_Records_sing;
		$this->records_plurar = $llt_Records_sing;
		$this->use_ajax = $llt_use_ajax;
		$user_id = get_current_user_id();
		$meta = get_user_meta($user_id, 'lexicon_custom_list_pages', true);
			if($meta) {
				$custom_list_pages = $meta;
			} else {
			$custom_list_pages = get_option('lexicon_custom_list_pages_default');
			add_user_meta( $user_id, 'lexicon_custom_list_pages_default', $custom_list_pages, false );
			}		
		$this->per_page = $custom_list_pages;
        //Set Parent Class Defaults
        parent::__construct( array(
            'singular'  => $this->records_singular,    
            'plural'    => $this->records_plurar,    
            'ajax'      => $this->use_ajax        
        ) );
 	
  		}
     
	  function column_cb($item, $id) {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
			 /*$1%s*/ $this->_args['singular'],  
            /*$2%s*/ $item[$id]
        );    
    }
	
	function get_columns($columns = NULL){  // Checkbox provided by parent, Null to supress E_STRICT warning
		$arr = array_reverse($columns, true);
		$arr ['cb'] = '<input type="checkbox" />';
		return array_reverse($arr, true);
 }
 	function get_sortable_columns($columns = NULL) { // Null to supress E_STRICT warning
		$arr = array();
		foreach($columns as $column) {
		$arr[$column] = array($column, false);	
		}
	  return $arr;
	}
	 function usort_reorder( $a, $b ) {  				
  		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'ID';// If no sort, default to title
  		$order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';// If no order, default to asc
  		$result = strcmp( $a[$orderby], $b[$orderby] );// Determine sort order
  		return ( $order === 'asc' ) ? $result : -$result;// Send final sort direction to usort
	}
	function prepare_items($sql = NULL, $state = NULL, $obj = NULL, $data = NULL) {
	global $wpdb;
	if($state == 1) {
		$res = $wpdb->get_results($sql);
		if(!isset($_POST['s'])){
	return $res;
		} else {  // modify result based on Search query
		$filtered_res = array();
			foreach($res as $row){
				foreach ($row as $field => $value) {
					if(strpos(strtolower($value),strtolower($_POST['s'])) !== false) {
						$filtered_res[] = $row;
						break;
					}
				}
			}
	return $filtered_res;
		}
	} elseif($state == 2) {
	
		$columns  = $obj->get_columns();
  	$hidden   = array();
  	$sortable = $obj->get_sortable_columns();
  	$obj->_column_headers = array( $columns, $hidden, $sortable );
 
  usort( $data, array(&$obj, 'usort_reorder'));
 $current_page = $obj->get_pagenum();
 $total_items = count($data);
  $data = array_slice($data,(($current_page-1)*$this->per_page),$this->per_page);
 
  $obj->items = $data;
  
   $obj->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $this->per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$this->per_page)   //WE have to calculate the total number of pages
        ) ); 
	
	}
			
  	
	}
	
	public function display($x = null, $new_vars = null) {
		?>
        <form method="post">
  <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
  <?php $x->search_box('search', 'search_id'); ?>
  
</form><?php if(isset($new_vars)) { ?>
<p><a href="<?php echo '?page='.$_REQUEST['page'].'&action='.$new_vars[0]; ?>" class="add-new-h2"><?php echo $new_vars[1]; ?></a></p>
<?php } ?>
  <form id="studs-filter" method="get">
    <input type="hidden" name="page" value="<?php
	echo $_REQUEST['page'];
?>" />
    <?php
	parent::display();
?>
  </form>
  <?php
	}
	
};