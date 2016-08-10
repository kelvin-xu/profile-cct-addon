function get_darker(bgcolor, d){
	var res = bgcolor.match(/^rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)$/i); 
	dcolor = 'rgb(';
	for (i = 1; i < 3; i++) { 
		if ((res[i]*1 + d) < 0) {
          	c = 0;
		} else {
			if ((res[i]*1 + d) > 255)
              	c = 255;
			else
				c = res[i]*1 + d;
		}
		dcolor += c + ',';
	}
	dcolor.slice(0, -1);
	dcolor += c + ')';
	return dcolor;
}


jQuery( document ).ready(function($) {

function setEmptyCourses(){
	$('.pubrec #aocourse-code').each(function(index){
    		//pubrec has no inputs!!!!
		if ( ! $.trim( $(this).html() ) && ! $.trim( $(this).parent().parent().find('#aopublication-website').html() ) ) {
          		$(this).parent().parent().addClass('empty');
        	} else {
			$(this).parent().parent().removeClass('empty');
		}
	});
}

function setEmptyResearch(){
	$('.pubrec #aoresearch-pi').each(function(index){
    		//pubrec has no inputs!!!!
		if ( ! $.trim( $(this).parent().parent().find('#aopublication-website').html() ) ) {
          		$(this).parent().parent().addClass('empty');
        	} else {
			$(this).parent().parent().removeClass('empty');
		}
	});
}

function setEmptyPublications(){
	$('.pubrec #aopublication-chapter').each(function(index){
    		//pubrec has no inputs!!!!
		if ( ! $.trim( $(this).parent().parent().find('#aopublication-website').html() ) ) {
          		$(this).parent().parent().addClass('empty');
        	} else {
			$(this).parent().parent().removeClass('empty');
		}
	});
}

setEmptyCourses();
setEmptyResearch();
setEmptyPublications();

  	$(".abstract-icon .dashicons-plus-alt").live("click", function (event) {
    		$(this).parent().parent().parent().parent().find(".abstract").slideToggle( 'slow' );
  	});

  $( "#aopublication-pagenumbers,#aopublication-bookeds,#aopublication-publisher" ).each(function( index ) {
    	if($.trim($(this).text()) === '') {
   			$(this).html('');
		}
  });
});