<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://cru.io
 * @since      1.0.0
 */

namespace CruReports\admin;

//use CruScheduler\utils\Helper;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @author     CRU Team <info@cru.io>
 */
class Cruclub_Reports_Controller {

	// Additional Custom Taxonomy
	const taxonomy = array( 'product_cat', 'wine_type' );
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since      1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct() {
		//$this->addFilters();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 		1.0.0
	 * @return 		void
	 */
	public function enqueue_styles() {

		wp_enqueue_style( 'datepicker-jquery-ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', array(), '1.12.1', 'all' );
		wp_enqueue_style( CRUREPORTS_PLUGIN_NAME, plugin_dir_url( CRUREPORTS_ROOT_DIR . CRUREPORTS_PLUGIN_NAME ) . 'assets/admin/css/cru-reports.css', array(), CRUREPORTS_VERSION, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 		1.0.0
	 * @return 		void
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( CRUREPORTS_PLUGIN_NAME.'-jquery-ui', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js', 'all');
		wp_enqueue_script( CRUREPORTS_PLUGIN_NAME, plugin_dir_url( CRUREPORTS_ROOT_DIR . CRUREPORTS_PLUGIN_NAME ) . 'assets/admin/js/cru-reports.js', array( 'jquery' ), CRUREPORTS_VERSION, false );
	}

	/**
	 * Register the AjaxScript for the admin area.
	 *
	 * @since 		1.0.0
	 * @return 		void
	 */
	public function enqueue_ajaxScripts() {
	 
	    // For either a plugin or a theme, you can then enqueue the script:
	    wp_enqueue_script( 'crureports-ajax-js', plugin_dir_url( CRUREPORTS_ROOT_DIR . CRUREPORTS_PLUGIN_NAME ) . 'assets/admin/js/cru-reports--sales.js', array( 'jquery' ), CRUREPORTS_VERSION, false );
	    wp_localize_script( 'crureports-ajax-js', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' )  ) );
	}

	/**
	 * Register Submenu under WooCommerce for the admin area.
	 *
	 * @since 		1.0.0
	 * @return 		void
	 */
	public function crureports_add_menu(){
		
		add_submenu_page( 'woocommerce', 'CRU Reports', 'CRU Reports', 'manage_options', 'cru-reports', array( $this, 'crureports_html_callback' ) );
	}

	/**
	 * Display initial page
	 *
	 * @since 		1.0.0
	 * @return 		html
	 */
	public function crureports_html_callback(){

		if( isset( $_GET['download'] ) ){

			$category_id = isset( $_GET['category_id'] ) ? $_GET['category_id'] : false;
			$taxonomy = isset( $_GET['taxonomy'] ) ? $_GET['taxonomy'] : '';
			$startDate = isset( $_GET['startDate'] ) ? $_GET['startDate'] : false;
			$endDate = isset( $_GET['endDate'] ) ? $_GET['endDate'] : false;
			$to_download = isset( $_GET['download'] ) ? $_GET['download'] : false;

			//$this->crureports_get_download( $category_id, $taxonomy, $startDate, $endDate, $to_download );
			$this->crureports_get_products_by_category( $category_id, $taxonomy, $startDate, $endDate, $to_download );
		}

		echo '<div class="wrap">';
		echo '<h2>' . __( 'CRU Reports', 'cru-reports' ) . '</h2>';
		

		echo '<div class="cru-reports-categories">';
		echo '<div class="cru-reports-categories-tab">';
		//echo '<div class="btn all download-reports"><button type="button" onclick="" data-json="">Download All CSV</button></div>';
		echo '<div class="cru-reports-categories-head"><h3>'.__( 'Categories', 'cru-reports' ).'</h3><p class="cru-report-label small"><em>Included categories: (Product Category, Wine Type)</em></p></div>';
		echo '<ul class="categories">';


		$taxonomies = $this->crureports_get_taxonomies();

		echo '<li class="show-all-products active">';
			echo '<a href="#" class="cru-reports-all-products-tab">Show All Products</a>';
			echo '<div class="btn download-reports all"><button type="button" onclick="" title="Download CSV"><span class="dashicons dashicons-download"></span></button></div>';
			echo '</li>';

		foreach ($taxonomies as $key => $taxonomy) {
			$data = htmlspecialchars(json_encode($taxonomy), ENT_QUOTES, 'UTF-8');
			echo '<li class="category parent-category category-'.$taxonomy['term_id'].'" data-json="'.$data.'">';
			echo '<a href="#" class="cru-reports-category-tab">'.ucwords(strtolower($taxonomy['name'])).'<span>'.$taxonomy['count'].'</span><span class="cru-reports-taxonomy">'.$taxonomy['taxonomy'].'</span></a>';
			echo '<div class="btn download-reports"><button type="button" onclick="" title="Download CSV"><span class="dashicons dashicons-download"></span></button></div>';
			echo '</li>';
		}

		echo '</ul>';
		echo '</div>';
		echo '<div class="cru-reports-categories-info">';	
		echo '<div class="cru-reports-category-info">';
		echo '<div class="cru-reports-category-head">';
		echo '<h3>'. __( 'All Products', 'cru-reports' ).'</h3>';
		echo '<p class="cru-report-label small"><em>Select Product Category on the left pane to know where categories they belong</em></p>';
		echo '</div>';
		echo '<div class="cru-reports-category-filter">';
			echo '<div class="cru-reports-category-daterange-form">';
				// echo '<label for="cru-reports-daterange-from">From</label>';
				echo '<input type="text" id="cru-reports-daterange-from" name="cru-reports-daterange-from" autocomplete="off">';
				// echo '<label for="cru-reports-daterange-to">to</label>';
				echo '<input type="text" id="cru-reports-daterange-to" name="cru-reports-daterange-to" autocomplete="off">';
				//echo '<button type="button" onclick="">Set Date</button>';
			echo '</div>'; //endof cru-reports-category-daterange-form
			echo '<div class="cru-reports-category-buttons"><a href="#" class="cru-reports-category-daterange"><span class="dashicons dashicons-calendar-alt"></span><label><span class="data-crureports-from">Jan 01</span> — <span class="data-crureports-to">Dec 31</span></label></a></div>';
			echo '<div class="cru-reports-category-options">';
				echo '<p class="cru-reports-has-sales"><span><input id="has_sales" name="has_sales" type="checkbox"></span><label for="has_sales">Has Sales</label></p>';				
			echo '</div>'; //endof cru-reports-category-options
			echo '<div class="cru-reports-category-sales">';
				echo '<p class="cru-reports-quantity"><label>Total Quantity</label><span>0</span></p>';
				echo '<p class="cru-reports-total"><label>Total Amount</label><span>0.00</span></p>';
			echo '</div>'; //endof cru-reports-category-sales
		echo '</div>'; //endof cru-reports-category-filter
		echo '</div>'; //endof cru-reports-category-info
		echo '<div class="cru-reports-products-info">';

		//$this->crureports_get_products_by_category();

		echo '</div>'; //endof cru-reports-products-info
		echo '</div>'; //endof cru-reports-categories-info
		echo '</div>'; //endof cru-reports-categories
		echo '</div>'; //end of wrap
	}

	/**
	 * Retrieve all Products Taxonomies
	 *
	 * @since 		1.0.0
	 * @param 		$args
	 * @return 		products_categories
	 */
	public function crureports_get_taxonomies(){
		
		$args = array(
			'taxonomy'		=> self::taxonomy,
			'hide_empty'	=> false,
            'parent'		=> 0,
			'orderby' 		=> 'Name',
			'order' 		=> 'ASC',
		);

		return json_decode( json_encode( get_terms( $args ) ), true );
	}

	/**
	 * Display data needed and AJAX Callback
	 *
	 * @since 		1.0.0
	 * @param 		$args, $category_id, $products
	 * @return 		products
	 */

	public function crureports_get_sales_report(){

		$category_id = isset( $_REQUEST['category_id'] ) ? $_REQUEST['category_id'] : false;
		$taxonomy = isset( $_REQUEST['taxonomy'] ) ? $_REQUEST['taxonomy'] : false;
		$startDate = isset( $_REQUEST['startDate'] ) ? $_REQUEST['startDate'] : false;
		$endDate = isset( $_REQUEST['endDate'] ) ? $_REQUEST['endDate'] : false;
		$to_download = isset( $_REQUEST['download'] ) ? $_REQUEST['download'] : false;

		$results = $this->crureports_get_products_by_category( $category_id, $taxonomy, $startDate, $endDate, $to_download );

		return $results;

		wp_reset_postdata();
		die;
	}

	/**
	 * Retrieve all Products under a Category
	 *
	 * @since 		1.0.0
	 * @param 		$args, $category_id, $products
	 * @return 		products
	 */

	public function crureports_get_products_by_category( $category_id = false, $taxonomy = '', $startDate, $endDate, $to_download ){

		$category_id = ( $to_download ) ? $category_id : isset( $_REQUEST['category_id'] ) ? $_REQUEST['category_id'] : false;
		$taxonomy = ( $to_download ) ? $taxonomy : isset( $_REQUEST['taxonomy'] ) ? $_REQUEST['taxonomy'] : false;
		$startDate = ( $to_download ) ? $startDate : isset( $_REQUEST['startDate'] ) ? $_REQUEST['startDate'] : false;
		$endDate = ( $to_download ) ? $endDate : isset( $_REQUEST['endDate'] ) ? $_REQUEST['endDate'] : false;
		$to_download = ( $to_download ) ? $to_download : isset( $_REQUEST['download'] ) ? $_REQUEST['download'] : false;

		$args = array(
			'orderby' => 'name',
	    	'order' => 'ASC',
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => '-1',
			'tax_query' => array(
				array(
					'taxonomy'      => 'product_type',
					'field' 		=> 'slug',
					'terms'         => 'simple',
					//'operator'      => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.
				),
				array(
		            'taxonomy'      => 'product_visibility',
		            'field'         => 'slug',
		            'terms'         => 'exclude-from-catalog', // Possibly 'exclude-from-search' too
		            'operator'      => 'NOT IN'
	       		),
			)
		);

		if( $category_id ){

			$args['tax_query'][] = array(
					'taxonomy'      => $taxonomy,
					'field' 		=> 'term_id', //This is optional, as it defaults to 'term_id'
					'terms'         => [$category_id],
					'operator'      => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.
				);
		}

		$products = new \WP_Query( $args );	
		
		if( $products->have_posts() ){
				
			$productids = array();
			$posts = $products->get_posts();

			$sales = $this->crureports_get_product_sales( $startDate, $endDate );

			$posts = json_decode( json_encode( $posts ), true);

			if( $to_download ){

				$category_name = $category_id ? 'Category: '. get_term( $category_id )->name : 'All Products';
				$csv_result = array();
				$csv_result[] = array( $category_name, 'Total Quantity', 'Total Amount' );
			}

			$sales_qty = 0;
			$sales_amt = 0;

			foreach($posts as $key => $product) {
				if( !array_key_exists('sales', $posts) ){
					$posts[$key]['sales']['quantity'] = 0;
					$posts[$key]['sales']['total'] = 0;
				}
			}

			foreach ($sales as $key => $sale) {
				$productids[] = $key;
			}

			foreach($posts as $key => $product) {

				// error_log(print_r( $product, true ));

				$product_id = $product['ID'];
				$product_title = $product['post_title'];

				if ( !in_array( $product_id, $productids ) ) {
					continue;
				}

				$sales_qty += $posts[$key]['sales']['quantity'] = (int) $sales[ $product_id ]['quantity'];
				$sales_amt += $posts[$key]['sales']['total'] = (float) $sales[ $product_id ]['line_total'];

				if( $to_download ){
					
					$csv_result[] = array( (string) $product_title, (int) $posts[$key]['sales']['quantity'], (float) $posts[$key]['sales']['total'] );
				}
			}

			if( $to_download ){
			
				$as_of_date = $startDate. ' to '.$endDate;
				$csv_result[] = array( 'Total Sales as of '.$as_of_date, $sales_qty, $sales_amt);

				$filename = $taxonomy .'-'. preg_replace( "![^a-z0-9]+!i", '-', strtolower( $category_name ) );

				$this->generateCsv( $csv_result, $filename );
			}

			if( !$to_download ){
				
				echo json_encode($posts);
			}

		} else {
			
			return false;
		}

		wp_reset_postdata();
		die;

	}

	/**
	 * Retrieve all Products under a Category
	 *
	 * @since 		1.0.0
	 * @param 		$woocommerce, $wc_report, $sold_products, $product_sales
	 * @return 		products_sales
	 */
	public function crureports_get_product_sales( $start, $end ){
		
		$wc_report = new \WC_Admin_Report();
		
		$reportData = array(
			'_product_id' => array(
				'type' => 'order_item_meta',
				'order_item_type' => 'line_item',
				'function' => '',
				'name' => 'product_id'
			),
		);

		$reportData['_qty'] = array(
			'type' => 'order_item_meta',
			'order_item_type' => 'line_item',
			'function' => '',
			'name' => 'quantity'
		);
		
		$reportData['_line_total'] = array(
			'type' => 'order_item_meta',
			'order_item_type' => 'line_item',
			'function' => '',
			'name' => 'line_total'
		);

		$sold_products = $wc_report->get_order_report_data(array(
			'data' => $reportData,
			'query_type' => 'get_results',

			'where' => array(
				array(
		            'key'      => 'post_date',
		            'value'    => date( 'Y-m-d', strtotime( $start ) ), // starting date
		            'operator' => '>'
		        ),
		        array(
		            'key'      => 'post_date',
		            'value'    => date( 'Y-m-d', strtotime( '+1 day', strtotime( $end ) ) ), // end date...
		            'operator' => '<'
		        ),
			),

			'group_by' => '',
			'order_by' => '',
			'order_types' => wc_get_order_types( 'reports' ),
			'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded', 'wc-completed', 'wc-processing', 'wc-on-hold', 'wc-refunded' ),			
			'parent_order_status' => false
		));	

		$product_sales = array();

		foreach ($sold_products as $key => $items) {
			$product_id = $items->product_id;
			if( !array_key_exists('quantity', $product_sales) ) $product_sales[ $product_id ]['quantity'] = 0;
			if( !array_key_exists('line_total', $product_sales) ) $product_sales[ $product_id ]['line_total'] = 0;
		}


		foreach ($sold_products as $key => $items) {
			$product_id = $items->product_id;
			$quantity = $items->quantity;
			$line_total = $items->line_total;
			
			$product_sales[ $product_id ]['quantity'] += $quantity;
			$product_sales[ $product_id ]['line_total'] += (float) $line_total; 
		}

		return $product_sales;
	}

	/**
	 * Download product by category id
	 *
	 * @since 		1.0.0
	 * @param 		$category_id, $product_sales
	 * @return 		generate_csv
	 */

	public function generateCsv($results, $filename, $delimiter = ',', $enclosure = '"') {
	   
		ob_end_clean();

		$filename = ( empty( $filename ) ? 'exports' : $filename ) . '.csv';

		error_log(print_r('Downloading file '.$filename, true));

		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");

		// force download  
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename={$filename}");
		header("Content-Transfer-Encoding: binary");

		$fh = @fopen( 'php://output', 'w' );

		foreach ( $results as $data ) {

			// Put the data into the stream
			fputcsv($fh, $data);
		}
		// Close the file
		fclose($fh);
		// Make sure nothing else is sent, our file is done
		ob_flush();
		exit;
	}
}// endof file