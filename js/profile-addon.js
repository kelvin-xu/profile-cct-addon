jQuery( document ).ready(function($) {



$("#aoresearch-funder-selection").live("change", function() {
	//alert($(this).val());
	input_element = $(this).parent().parent().find('#aoresearch-funder');
	input_element.val($(this).val()).change();
	if ($(this).val() == 'Other') {
		input_element.addClass('visible');
		input_element.val('Please enter funding source...').change();
		input_element.show();
	} else {
		input_element.removeClass('visible');
		input_element.hide();
	}
});

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

$( ".content-wrap #aopublication-image" ).each(function( index ) {
	var photo = jQuery(this).val();
	if (photo){
		if (is_portrait(photo))
  			$(this).parent().parent().parent().find('.pubrec .thumbnail').html('<img class="portrait" src=' + photo + '>');
		else
			$(this).parent().parent().parent().find('.pubrec .thumbnail').html('<img  src=' + photo + '>');
	}
});

$("#aocourse-code,#aoresearch-pi,#aoresearch-program,#aoresearch-funder,#aoresearch-end-year,#aopublication-pages,#aopublication-bookeds,#aopublication-abstract,#aopublication-website,#aopublication-authors,#aopublication-title,#aopublication-publisher,#aopublication-pagenumbers,#aopublication-status,#aopublication-chapter,#aopublication-year,#aopublication-image").live("change paste keyup", function() {

        elem_being_changed = $(this).attr('id');
	change_value = $(this).val();
	elem_worked_on = $(this).parent().parent().parent().find('.pubrec #'+ elem_being_changed);
	$(this).parent().parent().parent().find('.pubrec').removeClass('empty');

	if (elem_being_changed == 'aopublication-website'){ //check for empty .replaceWith($('<h5>' + this.innerHTML + '</h5>'));
		if (!change_value){
			elem_worked_on.replaceWith(function(){
      				return $("<span id=\"aopublication-website\" class=\"aopublication-website\">" + elem_worked_on.html() + "</span>");
  			});
		} else {
			elem_worked_on.replaceWith(function(){
      				return $("<a id=\"aopublication-website\" class=\"aopublication-website\" href=\""+ change_value +"\">" + elem_worked_on.html() + "</a>");
  			});
		}
	}
	else{
		if (elem_being_changed == 'aopublication-title')
			elem_being_changed = 'aopublication-website';

		if (elem_being_changed == 'aopublication-abstract'){
			if ( $(this).val().length > 2 && $(this).val() != "Default text" )
				$(this).parent().parent().parent().find('.pubrec .abstract-icon .dashicons').css('display','inline-block');
			else
				$(this).parent().parent().parent().find('.pubrec .abstract-icon .dashicons').css('display','none');
		}

		if (elem_being_changed == 'aopublication-image'){
			var photo = jQuery(this).val();//'http://jonathannicol.com/projects/center-and-crop-thumbnail/img/portrait-img.png';
			if (is_portrait(photo))
  				$(this).parent().parent().parent().find('.pubrec .thumbnail').html('<img class="portrait" src=' + photo + '>');
			else
				$(this).parent().parent().parent().find('.pubrec .thumbnail').html('<img  src=' + photo + '>');
		}

          	$(this).parent().parent().parent().find('.pubrec #'+ elem_being_changed).text($(this).val());

	}
	setEmptyCourses();
	setEmptyResearch();
	setEmptyPublications();
});

	function is_portrait(img_element_src) {
    		var t = new Image();
    		t.src = img_element_src;
    		return t.height > t.width;
	}

  $(function() {
	$( ".field-shell-aopublications,.field-shell-aoresearch,.field-shell-aocourses" ).sortable({
  		cursor: "move",
		handle: ".handle",
		containment: "parent",
	});
  });
  $(".handle").live("click", function (event) {
    	$(this).parent().find(".content-wrap").toggle();
  });


  $(".add-multiple").live("click", function (event) {
	window.setTimeout(ao_callback(this),0);
    	//window.setTimeout($(this).parent().children('.field').last().find('.pubrec #aopublication-year').text('2020'),0);
  });


  $( "#aopublication-pagenumbers,#aopublication-bookeds,#aopublication-publisher" ).each(function( index ) {
    	if($.trim($(this).text()) === '') {
   			$(this).html('');
		}
  });

	/*if($.trim($('#aopublication-pagenumbers').text()) === '') {
   		$("#aopublication-pagenumbers").html('');
	}
	if($.trim($('#aopublication-bookeds').text()) === '') {
   		$("#aopublication-bookeds").html('');
	}
  	if($.trim($('#aopublication-publisher').text()) === '') {
   		$("#aopublication-publisher").html('');
	}*/

	function ao_callback(obj){
		$(obj).parent().children('.field').last().find('.pubrec').addClass('empty');
		$(obj).parent().children('.field').last().find('.pubrec #aopublication-year').text(ao_script_vars.year);
		$(obj).parent().children('.field').last().find('.content-wrap #aopublication-year').val(ao_script_vars.year)
		$(obj).parent().children('.field').last().find('.pubrec #aopublication-authors').text(ao_script_vars.name);
		$(obj).parent().children('.field').last().find('.content-wrap #aopublication-authors').val(ao_script_vars.name);
		$(obj).parent().children('.field').last().find('.content-wrap').show();
	}

});
