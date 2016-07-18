/* 
 * User Role Editor WordPress plugin Pro
 * Author: Vladimir Garagulya
 * email: vladimir@shinephp.com
 * 
 */

jQuery(function() {
    jQuery("#ure_update_all_network").button({
        label: ure_data_pro.update_network
    }).click(function(event) {
        event.preventDefault();
        show_update_network_dialog();
                
    });
});


function show_update_network_dialog() {
    jQuery('#ure_network_update_dialog').dialog({                   
        dialogClass: 'wp-dialog',           
        modal: true,
        autoOpen: true, 
        closeOnEscape: true,      
        width: 400,
        height: 230,
        resizable: false,
        title: ure_data_pro.update_network,
        'buttons'       : {
            'Update': function (event) {
                event.preventDefault();
                
                var apply_to_all = document.createElement("input");
                apply_to_all.setAttribute("type", "hidden");
                apply_to_all.setAttribute("id", "ure_apply_to_all");
                apply_to_all.setAttribute("name", "ure_apply_to_all");
                apply_to_all.setAttribute("value", '1');
                document.getElementById("ure_form").appendChild(apply_to_all);
                
                if (jQuery('#ure_replicate_widgets_access_restrictions0').length>0) {
                    var checked = jQuery('#ure_replicate_widgets_access_restrictions0').is(':checked');                
                    if (checked) {
                        var rwar = document.createElement("input");
                        rwar.setAttribute("type", "hidden");
                        rwar.setAttribute("id", "ure_replicate_widgets_access_restrictions");
                        rwar.setAttribute("name", "ure_replicate_widgets_access_restrictions");
                        rwar.setAttribute('value', 1);
                        document.getElementById("ure_form").appendChild(rwar);
                    }
                }
                                
                jQuery('#ure_form').submit();
                jQuery(this).dialog('close');
            },
            Cancel: function() {
                jQuery(this).dialog('close');
                return false;
            }
          }
      });
}

