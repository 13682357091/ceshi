jQuery(document).ready(function() {
     // admin menu show hide

  jQuery("#sidebarnav .treeview").click(function() {
      jQuery("#sidebarnav .treeview .admin-sub-menu").removeClass('in');
      jQuery(this).find(".admin-sub-menu").toggle();
    });
 	/****** select2 *****/
 	jQuery('select.form-select').select2();
});
