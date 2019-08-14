<?php
/**
 * Plugin Name:       WooCommerce Custom Reports
 * Plugin URI:        http://cru.io
 * Description:       A WooCommerce Custom Reports
 * Version:           1.0.2
 * Author:            CRU Team (info@cru.io)
 * Author URI:        http://cru.io/
 * License:
 * License URI:
 * Text Domain:       woocommerce-custom-reports
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function cru_reports_scripts(){

    // Register the script like this for a plugin:
    wp_register_script( 'cru-reports-js', plugins_url( '/assets/admin/js/cru-reports.js', __FILE__ ) );    
 
    // For either a plugin or a theme, you can then enqueue the script:
    wp_enqueue_script( 'cru-reports-js' );
    wp_localize_script( 'cru-reports-js', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' )  ) );
    
}
add_action( 'admin_footer', 'cru_reports_scripts' );

function cru_reports_styles(){

	// Register the style like this for a plugin:
    wp_register_style( 'cru-reports-style', plugins_url( '/assets/admin/css/cru-reports.css', __FILE__ ), array(), '1.0.0', 'all' );
    wp_enqueue_style( 'cru-reports-style' );
}
add_action( 'admin_enqueue_scripts', 'cru_reports_styles' );

//wp_enqueue_style( 'woocommerce-custom-reports-css', plugin_dir_url( plugin_dir_path( __FILE__ ) . 'od-reports' ) . 'assets/admin/css/cru-reports.css', array(), '1.0.0', 'all' );
//wp_enqueue_script( 'woocommerce-custom-reports-js', plugin_dir_url( plugin_dir_path( __FILE__ ) . 'od-reports' ) . 'assets/admin/js/cru-reports.js', array( 'jquery' ), '1.0.0', true );

add_action( 'admin_menu', 'cru_reports_menu' );

function cru_reports_menu(){
	add_submenu_page( 'woocommerce', 'CRU Reports', 'CRU Reports', 'manage_options', 'cru-reports', 'cru_reports_callback' );
}

function cru_reports_callback(){

    if( isset( $_GET['download-csv'] ) ) {

        $categoryid = isset( $_GET['categoryid'] ) ? $_GET['categoryid'] : false;
        $categoryname = isset( $_GET['categoryname'] ) ? $_GET['categoryname'] : false;
        

        // foreach($category_detail as $cd){
        //     $categoryname = $cd->cat_name;  
        //     error_log(print_r($categoryname, true));          
        //     break;
        // }

        $productids = cru_get_productids_by_category($categoryid);
        order_items($productids, $categoryname);
        die;
    } else {
        echo '<h3>WooCommerce Custom Reports</h3>';
        //echo json_encode(cru_reports_get_all_categories());
        cru_reports_display_product_catergories();
    }
}


function cru_reports_display_product_catergories(){

	global $wp;	

	$current_url = admin_url( "admin.php?page=".$_GET["page"] );

	$categories = cru_reports_get_all_categories(); //

	$data = '';


	$products = htmlspecialchars(json_encode(crureports_get_all_sales()), ENT_QUOTES, 'UTF-8') ;

	//$all_data = json_encode($product_sales);	

	echo '<div class="cru-reports-categories">';
	echo '<div class="cru-reports-categories-tab">';
	echo '<div class="btn all download-reports"><button type="button" onclick="" data-json="'.$products.'">Download All CSV</button></div>';
	echo '<ul class="categories">';
	foreach ($categories as $key => $category) {

		$data = htmlspecialchars(json_encode($category), ENT_QUOTES, 'UTF-8');
		$id = $category['category_id'];
		$name = $category['category_name'];
		$description = $category['category_description'];
		$countItems = $category['category_count'];

		//error_log(print_r($category, true));

		echo '<li class="parent category category-'.$id.'" ><a href="#" class="cru-reports-category-tab" data-json="'.$data.'" >'. $category['category_name'] .'<span>'.$countItems.'</span></a>';

		if( array_key_exists('sub_category', $category) ){
			echo '<ul class="cru-reports-sub-categories-tab">';

			foreach ($category['sub_category'] as $key => $sub_category) {
				$sub_data = htmlspecialchars(json_encode($sub_category), ENT_QUOTES, 'UTF-8');
				$id = $sub_category['category_id'];				
				$name = $sub_category['category_name'];
				$description = $sub_category['category_description'];

				echo '<li class="sub category category-'.$id.'"><a href="#" class="cru-reports-category-tab cru-reports-sub-category-tab" data-json="'.$sub_data.'">'. $sub_category['category_name'] .'</a><div class="btn download-reports"><button type="button" onclick="" data-json="'.$sub_data.'">Download CSV</button></div></li>';
			}
			echo '</ul>';
		}
		echo '<div class="btn download-reports"><button type="button" onclick="" data-json="'.$data.'">Download CSV</button></div>';
		echo '</li>';
	}

	echo '</ul>';
	echo '</div>';
	echo '<div class="cru-reports-categories-info">';	
	echo '<div class="cru-reports-category-info">';
	echo '</div>';
	echo '<div class="cru-reports-products-info">';
	
	$product_sales = crureports_get_all_sales();
	$total_sales = 0;
	$total_quantity = 0;

	error_log(print_r($product_sales, true));

	foreach( $product_sales as $product_id => $sale ) {	

		if( !empty( $sale['line_total'] ) ){ // trap product has no total_sales
			$total_quantity += (int) $sale['quantity'];
			$total_sales += (float) $sale['line_total'];
		}
		
	}

	if( !empty( $product_sales ) ){

		echo '<div class="cru-reports-product-info head">';
			echo '<div class="cru-reports-product-head">';
			echo '<h3>All Products</h3>';
			echo '<p><em>Select Product Category on the left pane to know where categories they belong</em></p>';
			echo '</div>';
			echo '<div class="cru-reports-product-sales">';
			// echo '<p class="cru-reports total-orders">';
			// echo '<label>Total Orders</label>';
			// echo '<span>'.count($products['order_id']).'<span>';
			// echo '</p>';
			echo '<p class="cru-reports quantity">';
			echo '<label>Total Quantity</label>';
			echo '<span>'.$total_quantity.'</span>';
			echo '</p>';
			echo '<p class="cru-reports total">';
			echo '<label>Total Amount</label>';
			echo '<span>'.number_format($total_sales, 2).'</span>';
			echo '</p>';
			echo '</div>';
		echo '</div>';

		foreach ($product_sales as $product_id => $sales) {

			if( !empty( $product_id ) ){

				$product = wc_get_product( $product_id );
				$product_title = (string) $product->get_name() ? $product->get_name() : $product->get_title();
				$product_desc = $product->get_description();

				echo '<div class="cru-reports-product-info">';
					echo '<div class="cru-reports-product-head">';
					echo '<h3>'.$product_title.'</h3>';
					echo '<p>'.$product_desc.'</p>';
					echo '</div>';
					echo '<div class="cru-reports-product-sales">';
					// echo '<p class="cru-reports total-orders">';
					// echo '<label>Total Orders</label>';
					// echo '<span>'.count($products['order_id']).'<span>';
					// echo '</p>';
					echo '<p class="cru-reports quantity">';
					echo '<label>Quantity</label>';
					echo '<span>'.$sales['quantity'].'</span>';
					echo '</p>';
					echo '<p class="cru-reports total">';
					echo '<label>Amount</label>';
					echo '<span>'.number_format($sales['line_total'], 2).'</span>';
					echo '</p>';
					echo '</div>';
				echo '</div>';

			}
		}
	}

	echo '</div>';
	echo '</div>';
	echo '</div>';
}

add_action( 'wp_ajax_cru_reports_get_product_by_category', 'cru_reports_get_product_by_category' );
add_action( 'wp_ajax_nopriv_cru_reports_get_product_by_category', 'cru_reports_get_product_by_category' );

function crureports_get_all_sales(){
	global $woocommerce;

	include_once($woocommerce->plugin_path().'/includes/admin/reports/class-wc-admin-report.php');

	$wc_report = new WC_Admin_Report();
	$reportData = array(
		'_product_id' => array(
			'type' => 'order_item_meta',
			'order_item_type' => 'line_item',
			'function' => '',
			'name' => 'product_id'
		),
		// 'order_id' => array(
		// 	'type' => 'order_item',
		// 	'order_item_type' => 'line_item',
		// 	'function' => '',
		// 	'name' => 'order_id'
		// )
	);

	$reportData['_qty'] = array(
		'type' => 'order_item_meta',
		'order_item_type' => 'line_item',
		'function' => '',
		'name' => 'quantity'
	);

	// $reportData['_line_subtotal'] = array(
	// 	'type' => 'order_item_meta',
	// 	'order_item_type' => 'line_item',
	// 	'function' => '',
	// 	'name' => 'line_subtotal'
	// );
	
	$reportData['_line_total'] = array(
		'type' => 'order_item_meta',
		'order_item_type' => 'line_item',
		'function' => '',
		'name' => 'line_total'
	);
	

	$sold_products = $wc_report->get_order_report_data(array(
		'data' => $reportData,
		'query_type' => 'get_results',
		//'query_type' => 'get_row',
		'group_by' => '',
		'order_by' => '',
		//'limit' => (!empty($_POST['limit_on']) && is_numeric($_POST['limit']) ? $_POST['limit'] : ''),
		//'filter_range' => ($_POST['report_time'] != 'all'),
		//'order_types' => wc_get_order_types('reports'),
		'order_types' => wc_get_order_types( 'sales-reports' ),
		//'order_types' => wc_get_order_types('order_count'),
		//'order_status' => array( 'completed', 'processing', 'on-hold', 'wc-completed', 'wc-processing', 'wc-on-hold' ),
		//'order_status' => array( 'completed', 'wc-completed' ),
		'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded', 'wc-completed', 'wc-processing', 'wc-on-hold', 'wc-refunded' ),	
		//'order_status' => wc_get_order_statuses(),
		'parent_order_status' => false
	));	

	//$temp_key = 1;
	$product_sales = array();

	foreach ($sold_products as $key => $items) {
		$product_id = $items->product_id;

		// initiate index variable
		if( !array_key_exists('quantity', $product_sales) ) $product_sales[ $product_id ]['quantity'] = 0;
		//if( !array_key_exists('line_subtotal', $product_sales) ) $product_sales[ $product_id ]['line_subtotal'] = 0;
		if( !array_key_exists('line_total', $product_sales) ) $product_sales[ $product_id ]['line_total'] = 0;
	}


	foreach ($sold_products as $key => $items) {

		//$product_sales[] = array_merge_recursive( (array) $items[$key], (array) $items[++$key] );

		$product_id = $items->product_id;
		//$order_id = $items->order_id;
		$quantity = $items->quantity;
		//$line_subtotal = $items->line_subtotal;
		$line_total = $items->line_total;
		
		// store to new array		
		$product_sales[ $product_id ]['quantity'] += $quantity;
		//$product_sales[ $product_id ]['line_subtotal'] += $line_subtotal;
		$product_sales[ $product_id ]['line_total'] += (float) $line_total; 
		//$product_sales[ $product_id ]['order_id'][] = $order_id;

		//sleep(1);

	}

	return $product_sales;
}

function cru_reports_get_all_categories(){

	$taxonomy = 'product_cat';
	$orderby = 'name';
	$order = 'ASC';
	$show_count = 0;
	$pad_counts = 0;
	$hierarchical = 1;
	$title = '';
	$empty = 1;

	$args = array(
		'taxonomy' => $taxonomy,
		'orderby' => $orderby,
		'order' => $order,
		'show_count' => $show_count,
		'pad_counts' => $pad_counts,
		'hierarchical' => $hierarchical,
		'title_li' => $title,
		'hide_empty' => $empty
	);

	$categories = array();

	$all_categories = get_categories( $args );

		foreach ($all_categories as $key_1 => $cat) {
		if( $cat->category_parent == 0 ){
			
			$categories[ $key_1 ]['category_id'] = $cat->term_id;
			$categories[ $key_1 ]['category_link']  = get_term_link($cat->slug, 'product_cat');
			$categories[ $key_1 ]['category_name']  = $cat->name;
			$categories[ $key_1 ]['category_description']  = $cat->description;
			$categories[ $key_1 ]['category_count']  = $cat->category_count;

			$args2 = array(
				'taxonomy' => $taxonomy,
				'child_of' => 0,
				'parent' => $cat->term_id,
				'orderby' => $orderby,
				'order' => $order,
				'show_count' => $show_count,
				'pad_counts' => $pad_counts,
				'hierarchical' => $hierarchical,
				'title_li' => $title,
				'hide_empty' => $empty
			);
			$sub_cats = get_categories( $args2 );
			if( $sub_cats ){
				foreach ($sub_cats as $key_2 => $sub_category) {
					
					$categories[ $key_1 ]['sub_category'][ $key_2 ]['category_id']  = $sub_category->term_id;
					$categories[ $key_1 ]['sub_category'][ $key_2 ]['category_link']  = get_term_link($sub_category->slug, 'product_cat');
					$categories[ $key_1 ]['sub_category'][ $key_2 ]['category_name']  = $sub_category->name;
					$categories[ $key_1 ]['sub_category'][ $key_2 ]['category_description']  = $sub_category->description;
					$categories[ $key_1 ]['sub_category'][ $key_2 ]['category_count']  = $sub_category->category_count;
					

				}
			}
			
		}
	}

	return $categories;
}

function cru_get_productids_by_category( $category_id = 0  ){

    $args = array(
    	'orderby' => 'name',
    	'order' => 'ASC',
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => '-1',
        'tax_query' => array(
            array(
                'taxonomy'      => 'product_cat',
                'field' 		=> 'term_id', //This is optional, as it defaults to 'term_id'
                'terms'         => [$category_id],
                'operator'      => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.
            ),
            array(
                'taxonomy'      => 'product_visibility',
                'field'         => 'slug',
                'terms'         => 'exclude-from-catalog', // Possibly 'exclude-from-search' too
                'operator'      => 'NOT IN'
            )
        )
    );

    $products = new WP_Query( $args );

    $result = array();

    if ( $products->posts ) {
        foreach ( $products->posts as $product ) {
            $result[] = $product->ID;
        }
    }

    //error_log(print_r($result, true));
    return $result;
}

function __ORIGINAL__cru_reports_get_product_by_category(){

	$category_id = isset( $_REQUEST['category_id'] ) ? $_REQUEST['category_id'] : false;

	if ( ! $category_id ) {
	    return;
	}
	
	$args = array(
		'orderby' => 'name',
    	'order' => 'ASC',
		'post_type' => 'product',
		'post_status' => 'publish',
		'posts_per_page' => '-1',
		'tax_query' => array(
			array(
				'taxonomy'      => 'product_cat',
				'field' 		=> 'term_id', //This is optional, as it defaults to 'term_id'
				'terms'         => [$category_id],
				'operator'      => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.
			),
			array(
	            'taxonomy'      => 'product_visibility',
	            'field'         => 'slug',
	            'terms'         => 'exclude-from-catalog', // Possibly 'exclude-from-search' too
	            'operator'      => 'NOT IN'
       		)
		)
	);

	$products = new WP_Query( $args );	
	//$total_per_categories = 0;


	if( $products->have_posts() ){
		
		$productids = array();		
		$posts = $products->get_posts();

		$product_sales = array();	

		foreach ($posts as $key => $product) {
			$product_id = $product->ID;
			$productids[] = $product_id;
			
			if(!array_key_exists('quantity', $product_sales)) $product_sales[ $product_id ]['sales']['quantity'] = 0;
			if(!array_key_exists('total', $product_sales)) $product_sales[ $product_id ]['sales']['total'] = 0;
		}	

		$orders_id = cru_reports_all_orders();	
		
		$count = 0;

		foreach ($orders_id as $order_key => $order_id) {
			
			$order = new WC_Order( $order_id );
			$products_items = $order->get_items();
			$total = $order->get_total();

			foreach ($products_items as $product_key => $product) {

	            $product_id = $product->get_product_id();

	            if (empty($product_id)) continue;

	            if (!in_array($product_id, $productids)) {
	                continue;
	            }

				$product_sales[ $product_id ]['sales']['quantity'] += (int) $product->get_quantity();
				$product_sales[ $product_id ]['sales']['total'] += (float) $product->get_total();
			}			
		}

		$posts = json_decode(json_encode($posts), true);

		foreach ($posts as $key => $product) {
			if ( array_key_exists($product['ID'], $product_sales) ){
				$posts[$key]['sales'] = $product_sales[$product['ID']]['sales'];
			}
		}
		echo 'adadasdasd';
		echo json_encode($posts);
	} else {
		
		return false;
	}

	wp_reset_postdata();
	
	die;
}

function cru_reports_get_product_by_category(){

	$category_id = isset( $_REQUEST['category_id'] ) ? $_REQUEST['category_id'] : false;

	if ( ! $category_id ) {
	    return;
	}
	
	$args = array(
		'orderby' => 'name',
    	'order' => 'ASC',
		'post_type' => 'product',
		'post_status' => 'publish',
		'posts_per_page' => '-1',
		'tax_query' => array(
			array(
				'taxonomy'      => 'product_cat',
				'field' 		=> 'term_id', //This is optional, as it defaults to 'term_id'
				'terms'         => [$category_id],
				'operator'      => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.
			),
			array(
	            'taxonomy'      => 'product_visibility',
	            'field'         => 'slug',
	            'terms'         => 'exclude-from-catalog', // Possibly 'exclude-from-search' too
	            'operator'      => 'NOT IN'
       		)
		)
	);

	$products = new WP_Query( $args );	
	//$total_per_categories = 0;


	if( $products->have_posts() ){
			
			$productids = array();		
			$posts = $products->get_posts();

			$sales = crureports_get_all_sales();

			$posts = json_decode(json_encode($posts), true);

			error_log(json_encode($sales));

			foreach($posts as $key => $product) {
				$product_id = $product['ID'];

				//if( in_array($product_id, $sales) ){

					$posts[$key]['sales']['quantity'] = $sales[ $product_id ]['quantity'];
					$posts[$key]['sales']['total'] = $sales[ $product_id ]['line_total'];				


				//}
			}

		//error_log(print_r($posts, true));

		echo json_encode($posts);
	} else {
		
		return false;
	}

	wp_reset_postdata();
	die;
}

add_action( 'wp_ajax_order_items', 'order_items' );
add_action( 'wp_ajax_nopriv_order_items', 'order_items' );

function __order_items($productids = array(), $category_name){

	// Get all Orders ID
	$orders_id = cru_reports_all_orders();	

	$data = array();
    foreach ($productids as $product_id) {

        if (!isset( $data[ $product_id ])) {
            $data[ $product_id ] = array();
        }

        //if(!array_key_exists('quantity', $data))

        if(!array_key_exists('quantity', $data))  $data[ $product_id ]['quantity'] = 0;
        if(!array_key_exists('total', $data))  $data[ $product_id ]['total'] = 0;

    }

	foreach ($orders_id as $key => $order_id) {
		
		$order = new WC_Order( $order_id );
		$products_items = $order->get_items();
		$total = $order->get_total();

		foreach ($products_items as $key => $product) {

            $product_id = $product->get_product_id();

            if (empty($product_id)) continue;

            if (!in_array($product_id, $productids)) {
                continue;
            }

            $data[ $product_id ]['quantity'] += (int) $product->get_quantity();
            $data[ $product_id ]['total'] += (float) $product->get_total();

		}	
		
	}

	$csv_result = array();
	// header of the CSV

	$category_name = !empty($category_name) ? $category_name : 'Products';

	$csv_result[] = array($category_name, 'Quantity',	'Amount');

	$total_qty = 0;
	$total_amount = 0;

    if (!empty($data)) {
        foreach ($data as $product_id => $item) {
            //error_log(print_r($item, true));
            $product = wc_get_product( $product_id );
            $product_name = $product->get_title();

            $total_qty += $item['quantity'];
            $total_amount += $item['total'];

            $csv_result[] = array( $product_name, $item['quantity'],$item['total']);
        }
        $csv_result[] = array( 'Total', $total_qty, $total_amount);
    }

    //error_log( print_r($csv_result, true));

	generateCsv( $csv_result );
}

function order_items($productids = array(), $category_name){

	$sales = crureports_get_all_sales();
	$data = array();


	foreach($productids as $key => $product_id) {
 		if (!isset( $data[ $product_id ])) {
            $data[ $product_id ] = array();
        }

        if(!array_key_exists('quantity', $data))  $data[ $product_id ]['quantity'] = 0;
        if(!array_key_exists('total', $data))  $data[ $product_id ]['total'] = 0;

	}

	$csv_result = array();
	// header of the CSV

	$category_name = !empty($category_name) ? $category_name : 'Products';

	$csv_result[] = array($category_name, 'Quantity',	'Amount');

	$total_qty = 0;
	$total_amount = 0;

	foreach ($productids as $key => $product_id) {
		$data[ $product_id ]['quantity'] = (int) $sales[ $product_id ]['quantity'];
		$data[ $product_id ]['total'] = (float) $sales[ $product_id ]['line_total'];
	}

    if (!empty($data)) {

    	 

        foreach ($data as $product_id => $item) {

            if( !empty( $product_id ) ){				

            	$product = wc_get_product( $product_id );
		        $product_title = (string) $product->get_name() ? $product->get_name() : $product->get_title();

		        $total_qty += $item['quantity'];
		        $total_amount += $item['total'];

		        $csv_result[] = array( $product_title, $item['quantity'], $item['total']);
            }
            
        }
        $csv_result[] = array( 'Total', $total_qty, $total_amount);

        generateCsv( $csv_result );
    }
    //error_log( print_r($csv_result, true));	
}


function __ORIGINAL__cru_reports_all_orders(){
	// Get 10 most recent order ids in date descending order.
	$query = new WC_Order_Query( array(
		'status' => array('processing', 'completed','wc-processing', 'wc-completed'),
		'limit' => 1000,
		'orderby' => 'date',
		'order' => 'DESC',
		'return' => 'ids',
	) );
	$orders_id = $query->get_orders();
	
	//error_log(print_r($orders_id, true));

	return $orders_id;	

	wp_reset_postdata();
}

function  cru_reports_all_orders(){ //__ORIGINAL__V2__
	// Get 10 most recent order ids in date descending order.
	
	$args = array(
		'limit' => 1000,		
		'paginate' => true,
		'status' => array('processing', 'completed','wc-processing', 'wc-completed'),
		'orderby' => 'date',
		'order' => 'DESC',
		'return' => 'ids',
	);

	$results = wc_get_orders( $args );
	$totalOrders = $results->total;
	$totalPages = $results->max_num_pages;
	$currentPage = 1;

	$orders_id = array();

	while( $currentPage <= $totalPages ){

		$query = array(
			'limit' => 1000,
			'paged' => $currentPage,
			'paginate' => true,
			'status' => array('processing', 'completed','wc-processing', 'wc-completed'),
			'orderby' => 'date',
			'order' => 'ASC',
			'return' => 'ids',
		);

		$orders_id[] = wc_get_orders( $query );

		++$currentPage;
	}

	return $orders_id;
}


function cru_reports_product_details( $product_id = 0 ){
	return wc_get_product( $product_id );
}

function __cru_reports_all_products_sales(){

    $args = array(
    	'orderby' => 'name',
    	'order' => 'ASC',
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => '-1',
        'tax_query' => array(
            array(
                'taxonomy'      => 'product_visibility',
                'field'         => 'slug',
                'terms'         => 'exclude-from-catalog', // Possibly 'exclude-from-search' too
                'operator'      => 'NOT IN'
            )
        )
    );

    $products = new WP_Query( $args );

	if( $products->have_posts() ){
		
		$posts = $products->get_posts();
		
		$productids = array();
		$product_sales = array();	

		foreach ($posts as $key => $product) {
			$product_id = $product->ID;
			$productids[] = $product_id;

			//if( !$product_sales[ $product_id ]['sales']['quantity'] ) $product_sales[ $product_id ]['sales']['quantity'] = 0;
			//if( !$product_sales[ $product_id ]['sales']['total'] ) $product_sales[ $product_id ]['sales']['total'] = 0;

			if(!array_key_exists('quantity', $product_sales)) $product_sales[ $product_id ]['sales']['quantity'] = 0;
			if(!array_key_exists('total', $product_sales)) $product_sales[ $product_id ]['sales']['total'] = 0;
		}	

		// Get all Orders ID
		$orders_id = cru_reports_all_orders();	
		
		$count = 0;

		//error_log(print_r($orders_id, true));

		foreach ($orders_id as $order_key => $order_id) {
			
			$order = new WC_Order( $order_id );
			$products_items = $order->get_items();
			$total = $order->get_total();

			foreach ($products_items as $product_key => $product) {

	            $product_id = $product->get_product_id();

	            if (empty($product_id)) continue;

	            if (!in_array($product_id, $productids)) {
	                continue;
	            }            

	            //error_log(print_r( $product->get_quantity(), true ));
	            //error_log(print_r( $product->get_total(), true ));

				$product_sales[ $product_id ]['sales']['quantity'] += (int) $product->get_quantity();
				$product_sales[ $product_id ]['sales']['total'] += (float) $product->get_total();
		  		
		  		//$total_per_categories += (float) $product_sales[ $product_id ]['sales']['total'];
			}			
		}

		$posts = json_decode(json_encode($posts), true);

		foreach ($posts as $key => $product) {
			if ( array_key_exists($product['ID'], $product_sales) ){
				$posts[$key]['sales'] = $product_sales[$product['ID']]['sales'];
			}
		}
		echo 'asdfasdfsdfsdfdf';
		echo json_encode($posts);
	} else {
		
		return false;
	}

	wp_reset_postdata();
	die;
}

/*
 **/
function generateCsv($results, $delimiter = ',', $enclosure = '"') {
    ob_end_clean();

    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header('Content-Description: File Transfer');
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=exports.csv");
    header("Expires: 0");
    header("Pragma: public");

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
