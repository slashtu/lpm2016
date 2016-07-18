// change color of apply to all check box - for multi-site setup only - overrides the same function from the standard URE
function ure_applyToAllOnClick(cb) {
  el = document.getElementById('ure_apply_to_all_div');
  el_1 = document.getElementById('ure_import_to_all_div');
  if (cb.checked) {
    el.style.color = '#FF0000';
    el_1.style.color = '#FF0000';
    document.getElementById('ure_import_to_all').checked = true;
  } else {
    el.style.color = '#000000';
    el_1.style.color = '#000000';
    document.getElementById('ure_import_to_all').checked = false;
  }
}


// change color of apply to all check box - for multi-site setup only - overrides the same function from the standard URE
function ure_importToAllOnClick(cb) {
  el = document.getElementById('ure_import_to_all_div');
  if (cb.checked) {
    el.style.color = '#FF0000';
  } else {
    el.style.color = '#000000';
  }
}


function ure_import_roles_dialog() {
    jQuery(function ($) {
        $info = $('#ure_import_roles_dialog');
        $info.dialog({
            dialogClass: 'wp-dialog',
            modal: true,
            autoOpen: true,
            closeOnEscape: true,
            width: 550,
            height: 190,
            resizable: false,
            title: ure_data_exp_imp.import_roles_title,
            'buttons': {
                'Import': function () {
                    var file_name = $('#roles_file').val();
                    if (file_name == '') {
                        alert(ure_data_exp_imp.select_file_with_roles);
                        return false;
                    }
                    var form = $('#ure_import_roles_form');
                    form.attr('action', ure_data.page_url);
                    $("<input type='hidden'>")
                            .attr("name", 'ure_nonce')
                            .attr("value", ure_data.wp_nonce)
                            .appendTo(form);
                    form.submit();
                    $(this).dialog('close');
                },
                'Cancel': function () {
                    $(this).dialog('close');
                    return false;
                }
            }
        });
        $('.ui-dialog-buttonpane button:contains("Import")').attr("id", "dialog-import-roles-button");
        $('#dialog-import-roles-button').html(ure_data_exp_imp.import_roles);
        $('.ui-dialog-buttonpane button:contains("Cancel")').attr("id", "dialog-cancel-button");
        $('#dialog-cancel-button').html(ure_data.cancel);
    });                                    
}

jQuery(function() {

    jQuery("#ure_export_roles_button").button({
        label: ure_data_exp_imp.export_roles
    }).click(function(event) {
        event.preventDefault();
        jQuery.ure_postGo( ure_data.page_url, 
                      { action: 'export-roles', 
                        ure_nonce: ure_data.wp_nonce} );
    });

    jQuery("#ure_import_roles_button").button({
        label: ure_data_exp_imp.import_roles
    }).click(function(event) {
        event.preventDefault();
        ure_import_roles_dialog();
    });


});
