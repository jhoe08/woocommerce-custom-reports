( function( $ ){
	'use strict';
	
  // Init
	var html = '';
  var dateFrom = $('.data-crureports-from'), dateTo = $('.data-crureports-to'), dateBtn = $('.cru-reports-category-daterange');
	
  // Init Date
  var date = new Date( Date.now() );
  var getFirstDate = ( '01/01/' + date.getFullYear('Y') );
  var getLastDate = ( '12/31/' + date.getFullYear('Y') );

  var dateFormat = "mm/dd/yy",
  from = $( "#cru-reports-daterange-from" )
    .attr( 'value', getFirstDate )
    .datepicker({
      //defaultDate: "+1w",
      changeMonth: true,
      numberOfMonths: 1
    })
    .on( "change", function() {
      from.attr( 'value', getDateFormat( getDate( this ) ) );
      to.datepicker( "option", "minDate", getDate( this ) );
      dateFrom.text( setMonthDay( getDate( this ) ) );
      dateBtn.attr('data-crureports-from', getDateFormat( getDate( this ) ) );
    }),
  to = $( "#cru-reports-daterange-to" )
    .attr( 'value', getLastDate )
    .datepicker({
      //defaultDate: "+1w",
      changeMonth: true,
      numberOfMonths: 1
    })
    .on( "change", function() {
      to.attr( 'value', getDateFormat( getDate( this ) ) );
      from.datepicker( "option", "maxDate", getDate( this ) );
      dateTo.text( setMonthDay( getDate( this ) ) );
      dateBtn.attr('data-crureports-to', getDateFormat( getDate( this ) ) );
  });

  var daterange = $('.cru-reports-category-daterange'), daterangeForm = $('.cru-reports-category-daterange-form');
  daterange.attr({
    'data-crureports-from' : getFirstDate,
    'data-crureports-to' : getLastDate
  });
  daterangeForm.hide().addClass('hide');

  $( document ).on( 'click', 'a.cru-reports-category-daterange', function( event ){
    return daterangeForm.hasClass('show') ? daterangeForm.hide().addClass('hide').removeClass('show') : daterangeForm.show().addClass('show').removeClass('hide');
	});

  function setMonthDay( str ){
    var str = String( str ), d = str.split(" "), md = d[1]+' '+d[2];
    return md;
  }

  function getDateFormat( date ){
    var date = new Date( date ), day = date.getDate(), month = date.getMonth();

    return (( month.toString().length == 1 ) ? ( ( month === 0 ) ? ( month.toString() + '1' ) : '0' + ( month + 1 ) ) : ( month + 1 )) + '/' + ( day.toString().length == 1 ? '0' + day : day ) + '/' + date.getFullYear();
  }

	function getDate( element ) {
    var date;
    try {
      date = $.datepicker.parseDate( dateFormat, element.value );
    } catch( error ) {
      date = null;
    }
    return date;   //Tue Jan 01 2019 00:00:00 GMT+0800 (Philippine Standard Time)
  }
})( jQuery );
