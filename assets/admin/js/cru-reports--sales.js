( function( $ ){
	'use strict';
	
	//console.log('Howdy!');
	var target = $('.cru-reports-products-lists'),
		total_quantity_text = $('.cru-reports-quantity'),
		total_amount_text = $('.cru-reports-total'),
		total_product_quantity = 0,
		total_product_amount = 0,
		delay;

	// init 

	$('.cru-reports-entities > *:not(.cru-reports-categories) > .cru-reports-list').hide();

	// initiate products
	$( document ).ready( function( ){

		//event.preventDefault();
		cru_products_info();	

	});		

	// mouse events
	$( document ).on( 'click', 'div.cru-reports-head', function( event ){
		$( 'div.cru-reports-head' ).siblings().hide();
		$( this ).siblings().show();		
	} );


	$( document ).on( 'click', 'a.cru-reports-daterange', function( event ){

		event.preventDefault();
		
	} );

	// Category Tab
	$( document ).on( 'click', 'ul.cru-reports-list > li > a', function( event ){
	
		event.preventDefault();

		var entity = $(this).parent().parent().attr('data-entity'),
			products_head = $('.cru-reports-products-head');

		if( ! $(this).parent().hasClass('show-all-products') ){

			var obj = $(this).parent().attr('data-json'),
			data = JSON.parse(obj);

			products_head.find('h3').text( (data.name).toLowerCase() );
			products_head.find('p > em').text( ( data.description !== '' ) ? data.description : 'No category description' );

			cru_products_info( entity, data );

		} else {

			products_head.find('h3').text( 'All Products' );
			cru_products_info( entity );

		}

		//console.log(entity);

		// set active to current category selected
		$('ul.cru-reports-list > li').removeClass('active');
		$(this).parent().addClass('active');
		// reset
		$("#has_sales"). prop("checked", false);

	});
	
	// Download Products
	$( document ).on( 'click', '.download-reports > button', function( event ){
	
		event.preventDefault();

		var _this = $(this),
			entity = $(this).closest('.cru-reports-list').attr('data-entity');

		if( ! $(this).parent().hasClass('all') ){
			var obj = $(this).parent().parent().attr('data-json'),
				data = JSON.parse(obj);
		}

		var args = {
    		action : 'crureports_get_sales_report',    		
    		startDate: $('input[name="cru-reports-daterange-from"]').attr('value'),
    		endDate: $('input[name="cru-reports-daterange-to"]').attr('value'),    		
    		download: true,
    		entity: entity
    	};


		switch( entity ){
			case 'bundle':
			case 'subscription':
				args['entity_id'] = data.id;
			break;
			case 'category':
				if( ! $(this).parent().hasClass('all') ){
					args['entity_id'] = data.term_id;
					args['entity_taxonomy'] = data.taxonomy;
				}				
			break;
		}

		console.log( args );

		$.ajax({
			method: 'GET', // the method (could be GET btw)
			url: ajax_object.ajax_url, // The file where my php code is
			data: args,
			success : function( response ){

				var getUrl = window.location.href;
				var download_link = '&download=' + args['download'] + '&startDate=' + args['startDate'] + '&endDate=' + args['endDate'] + '&entity=' + args['entity'];

				if( $(_this).parent().hasClass('all') ){
					// console.log( $(this) );
				} else {
					switch( entity ){
						case 'bundle':
						case 'subscription':
							download_link += ( ( args['entity_id'] != 'undefined' ) ? ( '&entity_id=' + args['entity_id'] ) : '' );
						break;
						case 'category':
							download_link += ( ( args['entity_id'] != 'undefined' ) ? ( '&entity_id=' + args['entity_id'] ) : '' )  + ( ( args['entity_taxonomy'] != 'undefined' ) ? ( '&entity_taxonomy=' + args['entity_taxonomy'] ) : '' );
						break;
					}
				}

				window.open( getUrl + download_link ,'_blank');
			},
			error : function( error ){ console.log( error ); }
		});
	});

	// Date Range Control
	$( document ).on( 'change', 'input[name="cru-reports-daterange-from"], input[name="cru-reports-daterange-to"]', function( event ){

		// reset
		$("#has_sales"). prop("checked", false);

		if( $('ul.cru-reports-list li:not(.show-all-products)').hasClass('active') ){
		
			var obj = $('ul.cru-reports-list li.active').attr('data-json'), 
				data = JSON.parse(obj),
				entity = $('ul.cru-reports-list li.active').parent().attr('data-entity');

			cru_products_info( entity, data );

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

	function cru_products_info( entity = 'category', data = [] ){

		// reset content on the right side
		target.html('');
		total_product_quantity = 0;
		total_product_amount = 0;

		var args = {
			action : 'crureports_get_sales_report',
			// startDate: $('input[name="cru-reports-daterange-from"]').datepicker('getDate'),
			// endDate: $('input[name="cru-reports-daterange-to"]').datepicker('getDate'),
			startDate: $('input[name="cru-reports-daterange-from"]').attr('value'),
			endDate: $('input[name="cru-reports-daterange-to"]').attr('value'),
			entity: entity
		};			
				
		if ( data.length !== 0 ){
			switch( entity ){
				case 'bundle':
				case 'subscription':
						args['entity_id'] = data.id;
				break;
				case 'category':
						args['entity_id'] = data.term_id;
						args['entity_taxonomy'] = data.taxonomy;
				break;				
			}
		}

		//console.log( data );
		//console.log( args );

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
					has_sales = 'false', 
					sku = '',
					data = '';

				if( products.length > 0 ){

					for ( var i = 0; i < products.length; i++ ) {

						products_desc = ( products[i].post_excerpt !== '' ) ? products[i].post_excerpt : '<em>No product descriptions</em>';
						products_sales = ( 'sales' in products[i] ) ? cru_sales_info( products[i].sales ) : '<em>No Current Sales</em>';
						has_sales = ( ( products[i].sales['quantity'] > 0 ) && ( products[i].sales['quantity'] > 0 ) ) ? 'true' : 'false';
						sku = ( products[i].sku !== '' ) ? '<span><small><em>(' + products[i].sku + ')</em></small></span>' : '';

						//data = products[i].sales;

						html += '<div class="cru-reports-product-info" has_sales="'+ has_sales +'" data-sales="'+products[i].sales+'">';
						html += '<div class="cru-reports-product-head">';
						html += '<h3>'+ products[i].post_title +' '+ sku +'</h3>';
						html += '<p>'+ products_desc +'</p>';
						html += '</div>';
						html += '<div class="cru-reports-product-sales">'+ products_sales +'</div>';
						html += '</div>';

					}

				} else {
					html = '<p class="error">No \'SIMPLE\' products found on this '+entity+'.</p>';
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

		html += '<p class="cru-reports-sold"><label>Sold</label><span>'+ quantity +'</span></p>';
		html += '<p class="cru-reports-revenue"><label>Amount</label><span>'+ Number(amount).toFixed(2) +'</span></p>';

		total_product_quantity += quantity;
		total_product_amount += amount;

		return html;
	}


})( jQuery );
