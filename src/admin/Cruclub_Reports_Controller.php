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
include_once ABSPATH . 'wp-admin/includes/plugin.php';
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
	//const taxonomy = array( 'product_cat', 'wine_type' );
	const taxonomy = array( 'product_cat' );
	// subscription enabled
	public static $enabled_subscription = true;
	public static $enabled_bundle = true;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since      1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct() {
		//$this->addFilters();
		
		// this allows subcription tab
		//self::$enabled_subscription = true;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 		1.0.0
	 * @return 		void
	 */
	public function enqueue_styles() {

		if( get_current_screen()->id === 'woocommerce_page_cru-reports' ){
			wp_enqueue_style( 'datepicker-jquery-ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', array(), '1.12.1', 'all' );
			//wp_enqueue_style( CRUREPORTS_PLUGIN_NAME, plugin_dir_url( CRUREPORTS_ROOT_DIR . CRUREPORTS_PLUGIN_NAME ) . 'assets/admin/css/cru-reports.css', array(), CRUREPORTS_VERSION, 'all' );
			wp_enqueue_style( CRUREPORTS_PLUGIN_NAME, plugin_dir_url( CRUREPORTS_ROOT_DIR . CRUREPORTS_PLUGIN_NAME ) . 'assets/admin/css/cru-reports-2.css', array(), CRUREPORTS_VERSION, 'all' );
		}		
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 		1.0.0
	 * @return 		void
	 */
	public function enqueue_scripts() {

		if( get_current_screen()->id === 'woocommerce_page_cru-reports' ){
			wp_enqueue_script( CRUREPORTS_PLUGIN_NAME.'-jquery-ui', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js', 'all');
			wp_enqueue_script( CRUREPORTS_PLUGIN_NAME, plugin_dir_url( CRUREPORTS_ROOT_DIR . CRUREPORTS_PLUGIN_NAME ) . 'assets/admin/js/cru-reports.js', array( 'jquery' ), CRUREPORTS_VERSION, false );
		}
	}

	/**
	 * Register the AjaxScript for the admin area.
	 *
	 * @since 		1.0.0
	 * @return 		void
	 */
	public function enqueue_ajaxScripts() {
	 	if( get_current_screen()->id === 'woocommerce_page_cru-reports' ){
		    // For either a plugin or a theme, you can then enqueue the script:
		    wp_enqueue_script( 'crureports-ajax-js', plugin_dir_url( CRUREPORTS_ROOT_DIR . CRUREPORTS_PLUGIN_NAME ) . 'assets/admin/js/cru-reports--sales.js', array( 'jquery' ), CRUREPORTS_VERSION, false );
		    wp_localize_script( 'crureports-ajax-js', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' )  ) );
		}
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
			
			$entity = isset( $_GET['entity'] ) ? $_GET['entity'] : false;

			$startDate = isset( $_GET['startDate'] ) ? $_GET['startDate'] : false;
			$endDate = isset( $_GET['endDate'] ) ? $_GET['endDate'] : false;
			$to_download = isset( $_GET['download'] ) ? $_GET['download'] : false;

			$data = array();

			switch( $entity ){
				case 'bundle':
				case 'subscription':
					$data['entity_id'] = $entity_id = isset( $_REQUEST['entity_id'] ) ? $_REQUEST['entity_id'] : false;
				break;
				case 'category':
				default:
					$category_id = isset( $_REQUEST['entity_id'] ) ? $_REQUEST['entity_id'] : false;
					$taxonomy = isset( $_REQUEST['entity_taxonomy'] ) ? $_REQUEST['entity_taxonomy'] : false;

					if ( $category_id ) {
						$data['entity_id'] = $category_id;
						$data['entity_taxonomy'] = $taxonomy;
					}
				break;
			}

			$this->crureports_get_products( $entity, $data, $startDate, $endDate, $to_download );
			
		}
	?>
	<div class="wrap cru-reports">
		<h2><?php echo __( 'CRU Reports', 'cru-reports' );?></h2>
		<div class="cru-reports-form">
			<div class="cru-reports-entities">

				<?php $taxonomies = $this->crureports_get_taxonomies();	?>
				<div class="cru-reports-categories">
					<div class="cru-reports-head"><h3><?php echo __( 'Categories', 'cru-reports' ); ?> <span>(<?php echo ! empty( $taxonomies ) ? count( $taxonomies ) : 0; ?>)</span></h3></div>
					<ul class="cru-categories cru-reports-list" data-entity="category">
						<li class="show-all-products active">
							<a href="#" class="cru-reports-all-products-tab"><?php echo __( 'Show All Products', 'cru-reports' ); ?></a>
							<div class="btn download-reports all"><button type="button" onclick="" title="Download CSV"><span class="dashicons dashicons-download"></span></button></div>
						</li>
					<?php 						
						foreach ($taxonomies as $key => $taxonomy) {
						$data = htmlspecialchars(json_encode($taxonomy), ENT_QUOTES, 'UTF-8');
					?>
						<li class="category parent-category category-<?=$taxonomy['term_id']; ?>" data-json="<?=$data?>">
						<a href="#" class="cru-reports-category-tab">
							<?=ucwords(strtolower($taxonomy['name']));?>
							<span><?=$taxonomy['count'];?></span>
							<span class="cru-reports-taxonomy"><?=$taxonomy['taxonomy'];?></span>
						</a>
						<div class="btn download-reports" data-entity="category"><button type="button" onclick="" title="Download CSV"><span class="dashicons dashicons-download"></span></button></div>
						</li>
					<?php } ?>
					</ul>
				</div> 
				<?php if( self::$enabled_bundle ){ ?>
				<?php $bundles = $this->crureports_get_products_by_type('bundle', 0); ?>
				<div class="cru-reports-bundle">
					<div class="cru-reports-head"><h3><?php echo __( 'Bundles', 'cru-reports' ); ?> <span>(<?php echo ! empty( $bundles ) ? count( $bundles ) : 0; ?>)</span></h3></div>
					<?php if( $bundles ){ ?>
						<ul class="cru-subscriptions cru-reports-list" data-entity="bundle">

							<?php foreach ($bundles as $bundle_id => $bundle_ids) {
								$bundle = $this->crureports_get_product( $bundle_id );
								$data = htmlspecialchars(json_encode($bundle), ENT_QUOTES, 'UTF-8');																
								$products = htmlspecialchars(json_encode($bundle_ids), ENT_QUOTES, 'UTF-8');

							?>
							<li class="subscription parent-bundle product-<?=$bundle['id']?>" data-json="<?=$data;?>">
								<a href="#" class="cru-reports-bundle-tab">
									<?=ucwords(strtolower($bundle['name']));?>
									<!-- <span></span> -->
									<span><?=!empty($bundle_ids) ? count($bundle_ids) : 0;?></span>
									<span class="cru-reports-taxonomy"><?=$bundle['sku'];?> - <?=get_option('woocommerce_currency').' '.number_format( ! empty( $bundle['price'] ) ? $bundle['price'] : 0, 2, '.','');?></span>
								</a>
								<div class="btn download-reports" data-entity="bundle"><button type="button" onclick="" title="Download CSV"><span class="dashicons dashicons-download"></span></button></div>
							</li>
							<?php } ?>

						</ul>
					<?php } /* endof if( $bundles ) */ ?>
				</div>
				<?php } /* endof if( self::$enabled_bundle ) */ ?>

				<?php if( self::$enabled_subscription ){ ?>
				<?php $subscriptions = $this->crureports_get_products_by_type('subscription',0); ?>
				<div class="cru-reports-subscriptions">
					<div class="cru-reports-head"><h3><?php echo __( 'Subscriptions', 'cru-reports' ); ?> <span>(<?php echo ! empty( $subscriptions ) ? count( $subscriptions ) : 0; ?>)</span></h3></div>
					<?php if( $subscriptions ){ ?>
					<ul class="cru-subscriptions cru-reports-list" data-entity="subscription">
						<li class="show-all-products active" style="display: none;">
							<a href="#" class="cru-reports-all-products-tab"><?php echo __( 'Show All Products', 'cru-reports' ); ?></a>
							<div class="btn download-reports all"><button type="button" onclick="" title="Download CSV"><span class="dashicons dashicons-download"></span></button></div>
						</li>
						<?php foreach ($subscriptions as $subscription_id => $subscription_ids) { 
								$subscription = $this->crureports_get_product( $subscription_id );
								$data = htmlspecialchars(json_encode($subscription), ENT_QUOTES, 'UTF-8');
								$products = htmlspecialchars(json_encode($subscription_ids), ENT_QUOTES, 'UTF-8');
						?>
						<li class="subscription parent-subscription product-<?=$subscription['id']?>" data-json="<?=$data;?>">
							<a href="#" class="cru-reports-subscription-tab">
								<?=ucwords(strtolower($subscription['name']));?>
								<!-- <span></span> -->
								<span><?=!empty($subscription_ids) ? count($subscription_ids) : 0;?></span>
								<span class="cru-reports-taxonomy"><?=$subscription['sku'];?> - <?=get_option('woocommerce_currency').' '.number_format( ! empty( $subscription['price'] ) ? $subscription['price'] : 0, 2, '.','');?></span>
							</a>
							<div class="btn download-reports" data-entity="subscription"><button type="button" onclick="" title="Download CSV"><span class="dashicons dashicons-download"></span></button></div>
						</li>
						<?php } ?>
					</ul>
					<?php } ?>
				</div>
				<?php } /* endof if( self::$enabled_subscription ) */ ?>

			</div>
			<div class="cru-reports-display">
				<div class="cru-reports-products-details">
					<div class="cru-reports-products-head">
						<h3><?php echo __( 'All Products', 'cru-reports' ); ?></h3>
						<p class="cru-report-label small"><em><?=__( 'Select Product Category on the left pane to know where categories they belong', 'cru-reports' );?></em></p>
					</div>
					<div class="cru-reports-filter">
						<div class="cru-reports-daterange-form">
							<!-- // <label for="cru-reports-daterange-from">From</label> -->
							<input type="text" id="cru-reports-daterange-from" name="cru-reports-daterange-from" autocomplete="off">
							<!-- // <label for="cru-reports-daterange-to">to</label> -->
							<input type="text" id="cru-reports-daterange-to" name="cru-reports-daterange-to" autocomplete="off">
							<!-- //<button type="button" onclick="">Set Date</button> -->
						</div>
						<div class="cru-reports-buttons">
							<a href="#" class="cru-reports-daterange"><span class="dashicons dashicons-calendar-alt"></span><label>
								<span class="data-crureports-from">Jan 01</span> — <span class="data-crureports-to">Dec 31</span></label></a>
						</div>
						<div class="cru-reports-options">
							<p class="cru-reports-has-sales"><span><input id="has_sales" name="has_sales" type="checkbox"></span><label for="has_sales"><?php echo __( 'Has Sales', 'cru-reports' ); ?></label></p>			
						</div>
						<div class="cru-reports-sales">
							<p class="cru-reports-quantity"><label><?php echo __( 'Total Sold Items', 'cru-reports' );?></label><span>0</span></p>
							<p class="cru-reports-total"><label><?php echo __( 'Total Amount', 'cru-reports' );?></label><span>0.00</span></p>
						</div>
					</div>			
				</div>
				<div class="cru-reports-products-lists">
				</div> 
			</div>
		</div>
	</div>
	<?php
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

		$entity = isset( $_REQUEST['entity'] ) ? $_REQUEST['entity'] : false;	
		
		$startDate = isset( $_REQUEST['startDate'] ) ? $_REQUEST['startDate'] : false;
		$endDate = isset( $_REQUEST['endDate'] ) ? $_REQUEST['endDate'] : false;
		$to_download = isset( $_REQUEST['download'] ) ? $_REQUEST['download'] : false;

		$data = array();

		switch( $entity ){
			case 'bundle':
			case 'subscription':
				$data['entity_id'] = $entity_id = isset( $_REQUEST['entity_id'] ) ? $_REQUEST['entity_id'] : false;
			break;

			//default
			case 'category':
			default:
				$category_id = isset( $_REQUEST['entity_id'] ) ? $_REQUEST['entity_id'] : false;
				$taxonomy = isset( $_REQUEST['entity_taxonomy'] ) ? $_REQUEST['entity_taxonomy'] : false;

				if ( $category_id ) {
					$data['entity_id'] = $category_id;
					$data['entity_taxonomy'] = $taxonomy;
				}
			break;
		}

		$results = $this->crureports_get_products( $entity, $data, $startDate, $endDate, $to_download );

		return $results;

		wp_reset_postdata();
		die;
	}

	/**
	 * Retrieve all Products under a {entity}
	 *
	 * @since 		v2.2.2
	 * @param 		$entity, $data, $startDate, $endDate, $to_download
	 * @return 		products
	 * @from 		crureports_get_products_by_category | 1.0.0
	 */	

	public function crureports_get_products( $entity, $data, $startDate, $endDate, $to_download ){

		$entity = ( $to_download ) ? $entity : isset( $_REQUEST['entity'] ) ? $_REQUEST['entity'] : false;
		
		$startDate = ( $to_download ) ? $startDate : isset( $_REQUEST['startDate'] ) ? $_REQUEST['startDate'] : false;
		$endDate = ( $to_download ) ? $endDate : isset( $_REQUEST['endDate'] ) ? $_REQUEST['endDate'] : false;
		$to_download = ( $to_download ) ? $to_download : isset( $_REQUEST['download'] ) ? $_REQUEST['download'] : false;

		$args = array(
			'orderby' => 'name',
	    	'order' => 'ASC',
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => '-1'
		);
		$filename = '';

		switch( $entity ){			
			case 'bundle':
			case 'subscription':
				if( ! empty( $data ) ){
					
					//$product_ids = ( $entity == 'bundle' ) ? $this->crureports_get_product_bundles( $data['entity_id'] ) : $this->crureports_get_product_subscriptions( $data['entity_id'] );
					$product_ids = $this->crureports_get_products_by_type( $entity, $data['entity_id'] );

					if( ! empty( $product_ids ) ){
						$args['post__in'] = $product_ids;
					} else {
						return false;
					}
					$entity_id = $data['entity_id'];
					$filename = wc_get_product( $entity_id )->get_name();
					$product_head = $filename;
				} else {
					
					$product_ids = $this->crureports_get_products_by_type( $entity, 0 );
					
					error_log(print_r($product_ids, true));

					$args['post__in'] = $product_ids;
				}
			break;

			// default
			case 'category':
			default:
				if( ! empty( $data ) ){

					$args['tax_query'][] = array(
						'taxonomy'	=> $data['entity_taxonomy'],
						'field'		=> 'term_id', //This is optional, as it defaults to 'term_id'
						'terms'		=> [$data['entity_id']],
						'operator'	=> 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.
					);

					if( self::$enabled_subscription ){
						$args['tax_query'][] =  array(
							'taxonomy'      => 'product_type',
							'field' 		=> 'slug',
							'terms'         => 'simple',
							//'operator'      => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.		
						);
						/*
							array(
					            'taxonomy'      => 'product_visibility',
					            'field'         => 'slug',
					            'terms'         => 'exclude-from-catalog', // Possibly 'exclude-from-search' too
					            'operator'      => 'NOT IN'
				       		)
						*/
					}

					$category_id = $data['entity_id'];
					$taxonomy = $data['entity_taxonomy'];

					$filename = get_term( $category_id )->name;
					$product_head = $filename;
				}
				break;
		}

		$filename = ! empty( $data ) ? $entity.'-'.preg_replace( "![^a-z0-9]+!i", '-', strtolower( $filename ) ) : $entity;
		
		$products = new \WP_Query( $args );	
		
		if( $products->have_posts() ){
				
			$productids = array();
			$posts = $products->get_posts();

			$sales = $this->crureports_get_product_sales( $startDate, $endDate );

			$posts = json_decode( json_encode( $posts ), true);

			if( $to_download ){

				$products_head = ucfirst( $entity ) .': '. ( $product_head ? $product_head : 'All Products');
				$csv_result = array();
				$csv_result[] = array( $products_head, 'Total Quantity', 'Total Amount' );
			}

			$sales_qty = 0;
			$sales_amt = 0;

			foreach($posts as $key => $product) {
				if( !array_key_exists('sales', $posts) ){
					$posts[$key]['sales']['quantity'] = 0;
					$posts[$key]['sales']['total'] = 0;
				}
				if( !array_key_exists('sku', $posts) ){
					$posts[$key]['sku'] = '';
				}
			}

			foreach ($sales as $key => $sale) {
				$productids[] = $key;
			}

			foreach($posts as $key => $product) {

				// error_log(print_r( $product, true ));

				$product_id = $product['ID'];
				$product_title = $product['post_title'];
				
				$posts[$key]['sku'] = $product_sku = get_post_meta( $product_id, '_sku', true );

				if ( ! in_array( $product_id, $productids ) ) {
					//continue;
				}

				$sales_qty += $posts[$key]['sales']['quantity'] = (int) $sales[ $product_id ]['quantity'];
				$sales_amt += $posts[$key]['sales']['total'] = (float) $sales[ $product_id ]['line_total'];

				if( $to_download ){
					$_sku = ( ! empty( $product_sku ) ) ? ' (' . $product_sku . ')' : '';
					$csv_result[] = array( (string) $product_title . (string) $_sku, (int) $posts[$key]['sales']['quantity'], (float) $posts[$key]['sales']['total'] );
				}
			}

			if( $to_download ){
			
				$as_of_date = $startDate. ' to '.$endDate;
				$csv_result[] = array( 'Total Sales as of '.$as_of_date, $sales_qty, $sales_amt);				

				$this->generateCsv( $csv_result, $filename );
			}

			if( ! $to_download ){
				
				echo json_encode($posts);
			}

		} else {
			
			return false;
		}
	
		wp_reset_postdata();
		die;
	}

	/**
	 * Retrieve all Products Sales
	 *
	 * @since 		1.0.0
	 * @param 		$woocommerce, $wc_report, $sold_products, $product_sales
	 * @return 		products_sales
	 */
	public function crureports_get_product_sales( $start, $end ){

		if( ! class_exists('WC_Admin_Report') ){
			
			global $woocommerce;
			include_once($woocommerce->plugin_path().'/includes/admin/reports/class-wc-admin-report.php');
		} 
		
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
	 * Download Product Sales Reports
	 *
	 * @since 		1.0.0
	 * @param 		$category_id, $product_sales
	 * @return 		generate_csv
	 */

	public function generateCsv($results, $filename, $delimiter = ',', $enclosure = '"') {
	   
		ob_end_clean();

		$filename = ( empty( $filename ) ? 'cru-reports' : $filename ) . '.csv';

		error_log(print_r('Downloading file '.$filename, true));
		error_log(json_encode($results));

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

	/**
	 * Get All/Search Subscription Products
	 * & Dependent Product Type: [Subscription, Bundle]
	 *
	 * @since 		2.2.2
	 * @version		2.3.1
	 * @param 		$wpdb
	 * @return 		product_ids 
	 */

	protected function crureports_get_products_by_type( $type, $search_id = 0 ){
		global $wpdb;
		$results = '';

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
					'terms'         => $type,
					//'operator'      => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.
				)
			)
		);

		$products = new \WP_Query( $args );
		
		if( $products->have_posts() ){

			$product_ids = array();
			$posts = $products->get_posts();

			foreach( $posts as $key => $product ){
				
				$product_id = $product->ID;

				switch( $type ){
					case 'bundle':
						$table = $wpdb->prefix.'woocommerce_bundled_items';
						$column_id = $type.'_id';
						break;
					case 'subscription':
						$table = $wpdb->prefix . 'cruclub_subscription_products';
						$column_id = $type.'_id';
						break;					
				}

				$query = "SELECT product_id FROM {$table} WHERE $column_id = {$product_id}";
				
				$results = $wpdb->get_results( $query, ARRAY_A );
				
				//error_log(print_r( $query, true ));

				if( $results ){					
					foreach( $results as $key => $result ){
						$product_ids[$product_id][] = $result['product_id'];
					}	
				} else {
					$product_ids[$product_id] = null;
				}
				
			}
			if( $search_id !== 0 ){
				if( isset( $product_ids[$search_id] ) ){
					return $product_ids[ $search_id ];
				} else {
					return false;
				}
			}

			//error_log(print_r( $product_ids, true ));

			return $product_ids;
		}
		return false;
	}

	/**
	 * Get WooCommerce Product Data
	 * & Dependent: Cru Club
	 *
	 * @since 		2.2.2
	 * @param 		$product_id
	 * @return 		product_data 
	 */

	protected function crureports_get_product( $product_id ){
		
		$product = wc_get_product( $product_id );
		$product_data = $product->get_data();
		return $product_data;		
	}

	/**
	 * Convert string date into formatted date
	 * & Dependent: Cru Club
	 *
	 * @since 		2.2.2
	 * @param 		$date
	 * @return 		date 
	 */

	protected function crureports_convert_date( $date ){
		$date = substr($date, 4, 11);
		return date('m/d/Y', strtotime($date));
	}

}// endof file