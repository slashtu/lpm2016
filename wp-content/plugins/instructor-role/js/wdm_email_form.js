////To show email form
//function wdm_show_email_form( email_id ) {
//    
//    if ( email_id!="" ) {
//
//        jQuery( "#wdm_staff_mail_id" ).val( email_id );
//        popup('popUpDiv');
//        //jQuery( "#wdm_tbl_staff_mail" ).slideDown();
//        
//    } else {
//        
//    }
//    
//}
//Ajax call for individual email form functionality
function wdm_individual_send_email()
{
    
    var email   = "";
    var subject = "";
    var body    = "";
    
    var obj_email   = jQuery("#wdm_staff_mail_id");
    var obj_subject = jQuery("#wdm_staff_mail_subject");
    var obj_body    = jQuery("#wdm_staff_mail_body");
    var obj_btn_send= jQuery("#wdm_btn_send_mail");
    var obj_msg     = jQuery("#wdm_staff_mail_msg");
    
    email   = obj_email.val();
    subject = obj_subject.val();
    body    = obj_body.val();
    
    obj_msg.html("");
    
    if ( email ) {
        jQuery.ajax({
            url:wdm_instructor_js.ajax_url,
            data:{"action": "wdm_send_mail_to_individual_user","email":email, "subject":subject, "body":body },
            type:"post",
            dataType:"json",
            beforeSend: function ( xhr ) {
                obj_btn_send.attr("disabled", "disabled");
                obj_btn_send.css("cursor", "wait");
            }
        }).done(function (data) {
            if (data) {
                obj_msg.html(" Mail sent successfully!!! ");
                //alert("success");
            } else {
                obj_msg.html(" Mail not sent!!! ");
            }
            
            obj_btn_send.removeAttr("disabled");
            obj_btn_send.css("cursor", "pointer");
            obj_subject.val("");
            obj_body.val("");
            
        })
    }
}

