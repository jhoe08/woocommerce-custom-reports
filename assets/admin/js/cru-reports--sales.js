
( function( $ ){
	'use strict';
	
	//console.log('Howdy!');
	var target = $('.cru-reports-products-info'),
		total_quantity_text = $('.cru-reports-quantity'),
		total_amount_text = $('.cru-reports-total'),
		total_product_quantity = 0,
		total_product_amount = 0,
		delay;

	$( document ).ready( function( ){

		//event.preventDefault();
		cru_products_info();

	});


	// Category Tab
	$( document ).on( 'click', 'a.cru-reports-category-tab', function( event ){
	
		event.preventDefault();

		var category_head = $('.cru-reports-category-head'),
			obj = $(this).parent().attr('data-json'),
			data = JSON.parse(obj);

		category_head.find('h3').text( (data.name).toLowerCase() );
		category_head.find('p > em').text( ( data.description !== '' ) ? data.description : 'No category description' );

		// set active to current category selected
		$('.parent-category').removeClass('active');
		$(this).parent().addClass('active');

		// reset
		$("#has_sales"). prop("checked", false);
    	
		cru_products_info( data );

	});
	
	// Download Category
	$( document ).on( 'click', '.download-reports > button', function( event ){
	
		event.preventDefault();

		var obj = $(this).parent().parent().attr('data-json'),
			data = JSON.parse(obj);
    	
		console.log( data );

		var args = {
    		action : 'crureports_get_products_by_category',
    		category_id : data.term_id,
    		taxonomy: data.taxonomy,
    		startDate: $('input[name="cru-reports-daterange-from"]').attr('value'),
    		endDate: $('input[name="cru-reports-daterange-to"]').attr('value'),
    		download: true
    	};

		$.ajax({
			method: 'GET', // the method (could be GET btw)
			url: ajax_object.ajax_url, // The file where my php code is
			data: args,
			success : function( response ){

				console.log( response );

				 var getUrl = window.location.href;
				 window.open( getUrl ,"_self");
			},
			error : function( error ){ console.log( error ); }
		});
	});

	// Date range-to
	$( document ).on( 'change', 'input[name="cru-reports-daterange-from"], input[name="cru-reports-daterange-to"]', function( event ){

		// reset
		$("#has_sales"). prop("checked", false);

		if( $('.parent-category').hasClass('active') ){
		
			var obj = $('.parent-category.active').attr('data-json'), 
			data = JSON.parse(obj);

			//{"term_id":61,"name":"(L) Pizza","slug":"l-pizza","term_group":0,"term_taxonomy_id":61,"taxonomy":"product_cat","description":"","parent":0,"count":1,"filter":"raw"}

			cru_products_info( data );

			clearTimeout(delay);
			delay = setTimeout(function() {
				$('input[name="cru-reports-daterange-to"], input[name="cru-reports-daterange-from"]').parent().hide();
			}, 700);
		} else {

			cru_products_info();
		}
		

	});

	$( document ).on( 'change', '#has_sales', function( event ){
	
		if( $(this).prop("checked") == true ){
			$('.cru-reports-product-info').each(function(){
				if( $(this).attr('has_sales') == 'false' ){
					$(this).hide();
				}
			});
		} else if( $(this).prop("checked") == false ) {
			$('.cru-reports-product-info').removeAttr('style');
		}
	});

	function cru_products_info( data = [] ){


		// reset content on the right side
		target.html('');
		total_product_quantity = 0;
		total_product_amount = 0;

		

		var args = {
			action : 'crureports_get_products_by_category',			
			startDate: $('input[name="cru-reports-daterange-from"]').attr('value'),
			endDate: $('input[name="cru-reports-daterange-to"]').attr('value')
		};	
		
		if ( data.length !== 0 ){
			args['category_id'] = data.term_id;
			args['taxonomy'] = data.taxonomy;
		}

		$.ajax({
			method: 'POST', // the method (could be GET btw)
			url: ajax_object.ajax_url, // The file where my php code is
			data: args,
			success : function( response ){

				var products = JSON.parse( response ), 
					html = '', 
					products_desc = '', 
					products_sales = [],
					category_sales = 0,
					total_sales = 0,
					has_sales = 'false';
				
				if( products.length > 0 ){

					for ( var i = 0; i < products.length; i++ ) {

						products_desc = ( products[i].post_excerpt !== '' ) ? products[i].post_excerpt : '<em>No product descriptions</em>';
						products_sales = ( 'sales' in products[i] ) ? cru_sales_info( products[i].sales ) : '<em>No Current Sales</em>';
						has_sales = ( ( products[i].sales['quantity'] > 0 ) && ( products[i].sales['quantity'] > 0 ) ) ? 'true' : 'false';						

						html += '<div class="cru-reports-product-info" has_sales="'+ has_sales +'" >';
						html += '<div class="cru-reports-product-head">';
						html += '<h3>'+ products[i].post_title +'</h3>';
						html += '<p>'+ products_desc +'</p>';
						html += '</div>';
						html += '<div class="cru-reports-product-sales">'+ products_sales +'</div>';
						html += '</div>';

					}


				} else {
					html = '<p class="error">No products found on this category.</p>';
				}
				total_quantity_text.find('span').text( total_product_quantity );
				total_amount_text.find('span').text( Number(total_product_amount).toFixed(2) );
				target.append( html );

			},
			error : function( error ){ console.log( error ); }
		});
	}

	function cru_sales_info( data ){
		var html = '',
			quantity = Number( data['quantity'] ),
			amount = Number( data['total'] );

		html += '<p class="cru-reports quantity"><label>Quantity</label><span>'+ quantity +'</span></p>';
		html += '<p class="cru-reports total"><label>Amount</label><span>'+ Number(amount).toFixed(2) +'</span></p>';

		total_product_quantity += quantity;
		total_product_amount += amount;

		return html;
	}


})( jQuery );
