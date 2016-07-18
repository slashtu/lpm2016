jQuery(document).ready(function () {
   //To show email form
    jQuery('#wdm_pay_amount').click(function (e) {
        e.preventDefault();
        var total_paid = jQuery('#wdm_total_amount_paid').text();
        jQuery('#wdm_total_amount_paid_price').attr('value',total_paid);
        var amount_paid = jQuery('#wdm_amount_paid').text();
        jQuery('#wdm_amount_paid_price').attr('value',amount_paid);
     //console.log(amount);
        popup('popUpDiv');
    });

    jQuery('#wdm_pay_click').click(function (e) {
        e.preventDefault();
        var update_commission = jQuery(this);
        update_commission.parent().find('.wdm_ajax_loader').show();
        var total_paid = parseFloat(jQuery('#wdm_total_amount_paid_price').val());
        var amount_paid = parseFloat(jQuery('#wdm_amount_paid_price').val());
        var enter_amount = parseFloat(jQuery('#wdm_pay_amount_price').val());
        var instructor_id = jQuery('#instructor_id').val();
        if (enter_amount == '' || enter_amount <= 0 || isNaN(enter_amount)) {
            alert(wdm_commission_data.enter_amount);
            update_commission.parent().find('.wdm_ajax_loader').hide();
            return false;
        }
        if (enter_amount > amount_paid) {
            alert(wdm_commission_data.enter_amount_less_than);
            update_commission.parent().find('.wdm_ajax_loader').hide();
            return false;
        }
        jQuery.ajax({
            method: 'post',
            url : wdm_commission_data.ajax_url,
            dataType:'JSON',
            data : {
                action : 'wdm_amount_paid_instructor',
                total_paid : total_paid,
                amount_tobe_paid : amount_paid,
                enter_amount : enter_amount,
                instructor_id : instructor_id
            },
            success :  function (response) {
                jQuery.each(response,function (i,val) {
                    switch (i) {
                        case "error":
                            alert(val);
                            update_commission.parent().find('.wdm_ajax_loader').hide();
                          break;
                        case "success":
                            jQuery('#wdm_total_amount_paid_price').attr('value',val.total_paid);
                            jQuery('#wdm_amount_paid_price').attr('value',val.amount_tobe_paid);
                            jQuery('#wdm_pay_amount_price').attr('value','');
                            jQuery('#wdm_total_amount_paid').text(val.total_paid);
                            jQuery('#wdm_amount_paid').text(val.amount_tobe_paid);
                            update_commission.parent().find('.wdm_ajax_loader').hide();
                            if (val.amount_tobe_paid == 0) {
                                jQuery('#wdm_pay_click').remove();
                            }
                            alert(wdm_commission_data.added_successfully);
                     
                          break;
                    }
                });
            }
        });
    });
    var $ftable = jQuery('.footable');
    jQuery('#change-page-size').change(function (e) {
            e.preventDefault();
            var pageSize = jQuery(this).val();
            $ftable.data('page-size', pageSize);
            $ftable.trigger('footable_initialized');
    });
});