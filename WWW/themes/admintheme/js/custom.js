jQuery(document).ready(function() {
     // admin menu show hide
     //
  	  jQuery("#management-menu .menu-1.expanded ul li.expanded").each(function(event) {
        var target = jQuery( event.target );
		jQuery(this).children().attr('href','javascript:;');
		var menu_item_text = jQuery(this).children().text();
		 jQuery(this).children().append('<span class="pull-right-container "><i class="fa fa-angle-left pull-right"></i></span>');
    });

    jQuery("#management-menu .menu-1.expanded ul li.expanded ul").hide();
  	jQuery("#management-menu .menu-1.expanded ul li.expanded a").click(function() {
      if(jQuery(this).parent().find("ul").hasClass("in")){
          jQuery(this).parent().find("ul").removeClass("in");
          alert("work");
      }
      else{
        alert("wwww");
                 jQuery(this).parent().toggleClass("menu-open");
                jQuery(this).parent().find("ul").toggleClass( "in" );
      }
    });

 	/****** select2 *****/
 	jQuery('select.form-select').select2();
});
