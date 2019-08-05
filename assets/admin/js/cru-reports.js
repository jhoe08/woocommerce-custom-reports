( function( $ ){
    'use strict';
    
    //console.log('Howdy!');

    var total_product_quantity = 0;
    var total_product_amount = 0;

    $( document ).on( 'click', 'a.cru-reports-category-tab', function( event ){
    	event.preventDefault();

    	var target = $('.cru-reports-products-info');
    	var category_head = $('.cru-reports-category-info');
    	var obj = $( this ).attr('data-json');
    	var data = JSON.parse(obj);
    	var html_head = '';

    	// reset content on the right side
    	target.html('');
    	category_head.html('');
    	total_product_quantity = 0;
    	total_product_amount = 0;

    	var category_description = ( data.category_description !== '' ) ? data.category_description :  '<em>No category description</em>';
    	
    	html_head += '<div class="cru-reports-category-head">';
    	html_head += '<h1>'+ data.category_name +'</h1>';
    	html_head += '<h2>'+ category_description +'</h2>';    	
    	html_head += '</div>';

    	var args = {
    		action : 'cru_reports_get_product_by_category',
    		category_id : data.category_id
    	};

    	$.ajax({
	        method: 'POST', // the method (could be GET btw)
	        url: ajax_object.ajax_url, // The file where my php code is
	        data: args,
	        success : function( response ){ 
	        	var products = JSON.parse( response );
	        	var html = '';
	        	var products_desc = '';
	        	var products_sales = [];
	        	var category_sales = 0;
	        	var total_sales = 0;
	        	
	        	//console.log( response );

	        	if( products.length > 0 ){


		        	for (var i = 0; i < products.length; i++) {
		        		//console.log( products[i].ID + '' + products[i].post_title );
		        		products_desc = ( products[i].post_excerpt !== '' ) ? products[i].post_excerpt : '<em>No product descriptions</em>';

		        		//var product = JSON.stringify(products[i], null, 4);  data-json="'+ product +'"
		        		if ( 'sales' in products[i] ){
		        			products_sales = cru_sales_info( Object.keys( products[i].sales ).map(function(key){
			        			return [String(key), products[i].sales[key]];
			        		}) );	
		        		} else {
		        			products_sales = '<p><strong>No Sales Information</strong></p>';
		        		}

		        		//console.log(products[i].sales);

		        		html += '<div class="cru-reports-product-info">';
		        		html += '<div class="cru-reports-product-head">';
		        		html += '<h3>'+ products[i].post_title +'</h3>';
		        		html += '<p>'+ products_desc +' ASDSA</p>';
		        		html += '</div>';
		        		html += '<div class="cru-reports-product-sales">'+ products_sales +'</div>';
		        		html += '</div>';
		        	}
	        	} else {
	        		html = '<p class="error">No products found on this category.</p>';
	        	}

	        	target.append( html );

	        	total_sales = parseFloat(total_product_amount).toFixed(2);

	        	html_head += '<div class="cru-reports-category-sales">';
	        	html_head += '<p><label>Total Quantity:</label><span>'+total_product_quantity+'</span></p>';
	        	html_head += '<p><label>Total Amount:</label><span>'+total_sales+'</span></p>';
	        	html_head += '</div>';

	        	category_head.append(html_head);
	        },
	        error : function( error ){ console.log( error ); }
	    });
    });

    $( document ).on( 'click', 'div.download-reports > button', function( event ){

    	var obj = $( this ).attr('data-json');
    	var data = JSON.parse(obj);

		var urlParams = new URLSearchParams( window.location.search );
    	var getUrl = window.location.href;

    	window.open( getUrl + '&download-csv' + '&categoryid=' + data.category_id + '&categoryname=' + data.category_name ,"_self");

    });

    function cru_sales_info( data ){
    	var html = '';

    	var quantity = Number(data[0][1]);
    	var amount = Number(data[1][1]);


    	html += '<p class="cru-reports quantity"><label>Quantity</label><span>'+ quantity +'</span></p>';
		html += '<p class="cru-reports total"><label>Amount</label><span>'+ Number(amount).toFixed(2) +'</span></p>';

		total_product_quantity += quantity;
		total_product_amount += amount;

    	return html;
    }

})( jQuery );
