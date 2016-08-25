function shadeRGBColor(color, percent) {
    var f=color.split(","),t=percent<0?0:255,p=percent<0?percent*-1:percent,R=parseInt(f[0].slice(4)),G=parseInt(f[1]),B=parseInt(f[2]);
    return "rgb("+(Math.round((t-R)*p)+R)+","+(Math.round((t-G)*p)+G)+","+(Math.round((t-B)*p)+B)+")";
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
