( function( $ ){
	'use strict';
	
  /*
  var selected = $(this).datepicker("getDate");
  var dateFormat = $.datepicker.formatDate("mm-dd-yyyy", selected);
  */

  // Init
	var html = '';
  var dateFrom = $('.data-crureports-from'), dateTo = $('.data-crureports-to'), dateBtn = $('.cru-reports-category-daterange');
	
  // Init Date
  var date = new Date( Date.now() );
  var getFirstDate = ( '01/01/' + date.getFullYear('Y') );
  var getLastDate = ( '12/31/' + date.getFullYear('Y') );
  var getFFormatted = '', getLFormatted = '';


  var dateFormat = "mm/dd/yy",
  from = $( "#cru-reports-daterange-from" )
    .attr( 'value', getFirstDate )
    .datepicker({
      //defaultDate: "+1w",
      dateFormat: dateFormat,
      changeMonth: true,
      numberOfMonths: 1
    })
    .on( "change", function() {
      getFirstDate = $(this).datepicker("getDate");
      getFFormatted = getDateFormat(  getFirstDate );
      to.datepicker( "option", "minDate", getFirstDate );
      dateFrom.text( setMonthDay( getFirstDate ) );
      dateBtn.attr('data-crureports-from', getFFormatted );
    }),
  to = $( "#cru-reports-daterange-to" )
    .attr( 'value', getLastDate )
    .datepicker({
      //defaultDate: "+1w",
      dateFormat: dateFormat,
      changeMonth: true,
      numberOfMonths: 1
    })
    .on( "change", function() {
      getLastDate = $(this).datepicker("getDate");
      getLFormatted = getDateFormat(  getLastDate );
      from.datepicker( "option", "maxDate", getLastDate );
      dateTo.text( setMonthDay( getLastDate ) );
      dateBtn.attr('data-crureports-to', getLFormatted );
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
