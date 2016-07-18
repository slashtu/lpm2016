/**
 * to show instructor reports
 * created by uday
 * */
var paged_users='';
jQuery(document).ready(function ( ) {

    jQuery('#wdm_report_tbl').footable({
        breakpoints: {
            'tablet': 768,
            'phone': 480
        }
    });

    //Validation on Message send form
    // jQuery( '#instructor_message_form' ).on( 'submit',
    jQuery("#wdm_main_report_div").delegate(
        "#instructor_message_form",
        "submit",
        function ( event ) {
            var $subject = jQuery('#learndash_instructor_subject');
            var $message = jQuery('#learndash_instructor_message');
            if ( $subject.val().length == 0 || jQuery.trim($subject.val()) == '' ) {
                jQuery('#learndash_instructor_subject_err').css('color', 'red');
                jQuery('#learndash_instructor_subject_err').html('Please enter email subject');
                return false;
            } else {
                jQuery('#learndash_instructor_subject_err').html('');
            }
            if ( $message.val().length == 0 || jQuery.trim($message.val()) == '' ) {
                jQuery('#learndash_instructor_message_err').css('color', 'red');
                jQuery('#learndash_instructor_message_err').html('Please enter email body');
                return false;
            } else {
                jQuery('#learndash_instructor_message_err').html('');
            }

        }
    );

    /* To remove bulk select button from the media tab */
    jQuery("#wp-media-grid .select-mode-toggle-button, #wp-media-grid .delete-selected-button").remove();


});

jQuery(document).ready(function ( ) {

    if ( typeof ( wdm_reports_obj ) != 'undefined' && wdm_reports_obj != 'undefined' && wdm_reports_obj != '' ) {
        var not_started_per = wdm_reports_obj.not_started_per;
        var in_progress_per = wdm_reports_obj.in_progress_per;
        var completed_per = wdm_reports_obj.completed_per;
        var graph_heading = wdm_reports_obj.graph_heading;
        paged_users = wdm_reports_obj.paged_users;

        //console.log("paged_users when page loads="+paged_users);

        wdm_create_pie_chart(not_started_per, in_progress_per, completed_per, graph_heading);

    }
});

/**
 * to create pie chart graph
 * */

function wdm_create_pie_chart( not_started_per, in_progress_per, completed_per, graph_heading )
{
    // Build the chart
    jQuery('#wdm_report_div').highcharts({
        credits: { enabled: false },
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
        },
        title: {
            //text: 'Browser market shares at a specific website, 2014'
            text: graph_heading
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: false
                },
                showInLegend: true
            }
        },
        colors: [ '#A5A5A5', '#C3DD5A', '#5CB85C' ],
        series: [ {
            type: 'pie',
                //name: 'Browser share',
            name: wdm_reports_obj.piece_title,
            data: [
                    {
                        name: wdm_reports_obj.not_started_text,
                        y: parseFloat(not_started_per),
                        sliced: false,
                        selected: true
            },
                    [ wdm_reports_obj.in_progress_text, parseFloat(in_progress_per) ],
                    [ wdm_reports_obj.completed_text, parseFloat(completed_per) ]
                ]
        } ]
    });
}


function wdm_change_report( sel )
{
    var course_id = sel.value;
    if ( typeof ( course_id ) != 'undefined' && course_id != '' ) {
        jQuery.ajax({
            url: wdm_reports_obj.admin_ajax_path,
            type: "POST",
            dataType: "json",
            data: {
                action: 'wdm_get_report_html',
                course_id: course_id,
                request_type: 'ajax', // added in v1.3
            },
            success: function ( response ) {

                //console.log( response.html );

                if ( response ) {
                    paged_users = response.paged_users; // user id list
                    wdm_reports_obj.paged_index = 0; //setting pagination index to zero after chaning report
                    //console.log("paged_users when ajax="+paged_users);

                    jQuery("#wdm_main_report_div").empty().html(response.html);
                    // to create pie chart
                    wdm_create_pie_chart(response.not_started_per, response.in_progress_per, response.completed_per, response.graph_heading);
                    // to create datatable
                    jQuery('#wdm_report_tbl').footable({
                        breakpoints: {
                            'tablet': 768,
                            'phone': 480
                        }
                    });
                    //jQuery( '#wdm_report_tbl' ).dataTable();
                    // jQuery( '#wdm_report_tbl th' ).css( "width", "auto" );
                    //jQuery( "#wdm_report_tbl_wrapper" ).width( jQuery( "#wdm_report_tbl" ).width() );
                }

            }

        });//AJAX call ends
    }
}

/*
 *because of tinymce editor making alert of leave page/ stay on page when publishing new post to instructor only.
 */
if ( typeof ( autosaveL10n ) != 'undefined' ) {
    //console.log( autosaveL10n.autosaveInterval );
    autosaveL10n.autosaveInterval = 6000; // 60 = 1 min
    //console.log( autosaveL10n.autosaveInterval );
}



/* EMAIL JS STARTS */
//To show email form
function wdm_show_email_form( email_id )
{

    if ( email_id != "" ) {
        jQuery("#wdm_staff_mail_id").val(email_id);
        popup('popUpDiv');
        //jQuery( "#wdm_tbl_staff_mail" ).slideDown();
        jQuery("#wdm_staff_mail_msg").html("");

    } else {
    }

}
//Ajax call for individual email form functionality
function wdm_individual_send_email()
{

    var email = "";
    var subject = "";
    var body = "";

    var obj_email = jQuery("#wdm_staff_mail_id");
    var obj_subject = jQuery("#wdm_staff_mail_subject");
    var obj_body = jQuery("#wdm_staff_mail_body");
    var obj_btn_send = jQuery("#wdm_btn_send_mail");
    var obj_msg = jQuery("#wdm_staff_mail_msg");

    email = obj_email.val();
    subject = obj_subject.val();
    body = obj_body.val();

    obj_msg.html("");
    if ( subject.trim().length == 0 ) {
        obj_msg.html(" Please fill subject field");
        return false;
    }
    if ( body.trim().length == 0 ) {
        obj_msg.html(" Please fill mail body");
        return false;
    }
    if ( email ) {
        jQuery.ajax({
            url: wdm_reports_obj.admin_ajax_path,
            data: { "action": "wdm_send_mail_to_individual_user", "email": email, "subject": subject, "body": body },
            type: "post",
            dataType: "json",
            beforeSend: function ( xhr ) {
                obj_btn_send.attr("disabled", "disabled");
                obj_btn_send.css("cursor", "wait");
            }
        }).done(function ( data ) {
            if ( data ) {
                obj_msg.html(" Mail sent successfully!!! ");
                //alert("success");
            } else {
                obj_msg.html(" Mail not sent!!! ");
            }

            obj_btn_send.removeAttr("disabled");
            obj_btn_send.css("cursor", "pointer");
            obj_subject.val("");
            obj_body.val("");

        });
    }
}
/* EMAIL JS ENDS */


/* POPUP JS STARTS */
function toggle( div_id )
{
    var el = document.getElementById(div_id);
    if ( el.style.display == 'none' ) {
        el.style.display = 'block';
    } else {
        el.style.display = 'none';
    }
}
function blanket_size( popUpDivVar )
{
    if ( typeof window.innerWidth != 'undefined' ) {
        viewportheight = window.innerHeight;
    } else {
        viewportheight = document.documentElement.clientHeight;
    }
    if ( ( viewportheight > document.body.parentNode.scrollHeight ) && ( viewportheight > document.body.parentNode.clientHeight ) ) {
        blanket_height = viewportheight;
    } else {
        if ( document.body.parentNode.clientHeight > document.body.parentNode.scrollHeight ) {
            blanket_height = document.body.parentNode.clientHeight;
        } else {
            blanket_height = document.body.parentNode.scrollHeight;
        }
    }
    var blanket = document.getElementById('blanket');
    blanket.style.height = blanket_height + 'px';
    var popUpDiv = document.getElementById(popUpDivVar);
    popUpDiv_height = blanket_height / 2 - 200;//200 is half popup's height
    //popUpDiv.style.top = popUpDiv_height + 'px';
    //popUpDiv.style.top = '12%';
    var win_height = jQuery(window).height();
    var per = ( 25 / 100 ) * win_height;
    popUpDiv.style.top = ( jQuery(window).scrollTop() + per ) + 'px';

    //alert( jQuery(window).scrollTop() );

}

function window_pos( popUpDivVar )
{
    if ( typeof window.innerWidth != 'undefined' ) {
        viewportwidth = window.innerHeight;
    } else {
        viewportwidth = document.documentElement.clientHeight;
    }
    if ( ( viewportwidth > document.body.parentNode.scrollWidth ) && ( viewportwidth > document.body.parentNode.clientWidth ) ) {
        window_width = viewportwidth;
    } else {
        if ( document.body.parentNode.clientWidth > document.body.parentNode.scrollWidth ) {
            window_width = document.body.parentNode.clientWidth;
        } else {
            window_width = document.body.parentNode.scrollWidth;
        }
    }
    var popUpDiv = document.getElementById(popUpDivVar);
    window_width = window_width / 2 - 200;//200 is half popup's width
    //window_width=window_width/2 - 500;
    //popUpDiv.style.left = window_width + 'px';
    popUpDiv.style.left = '17%';
}
function popup( windowname )
{
    blanket_size(windowname);
    window_pos(windowname);
    toggle('blanket');
    toggle(windowname);
}

/* POPUP JS ENDS */




jQuery("document").ready(function () {

    /* to enable categories.
     * after adding "manage_categories" capability to instructor, wp disabling categories by default.
     *  */
    jQuery("#taxonomy-category input").removeAttr("disabled");

    /**
     * when new category adds then by default it becomes disabled, so to make it enable.
     * */
    jQuery("#category-add-submit").click(function () {
        setTimeout(function () {
            jQuery("#taxonomy-category input").removeAttr("disabled");
        }, 5000);
    });

});


/* -- for report users pagination - starts */
function wdm_js_ajax_pagination( index )
{

    index = parseInt(index);

    if ( typeof paged_users[ index ] != 'undefined' ) {
        if ( wdm_reports_obj.paged_index == index ) {
            return;
        }

        jQuery(".wdm-paged").css("color","#0074a2");
        jQuery(".wdm-paged").css("cursor","pointer");

        var wdm_users = paged_users[ index ];
        var current_post = jQuery("#post_id_report").val();

        var wdm_prev_index = 0;
        var wdm_next_index = 0;

        var wdm_paged_length = paged_users.length;

        if ( index > 0 && index < (wdm_paged_length-1) ) {
            wdm_prev_index = index-1;
            wdm_next_index = index+1;
        } else if ( index == 0 ) {
            wdm_prev_index = 0;
            wdm_next_index = 1;

            jQuery("#wdm_first_page, #wdm_prev_page").css("color","#A5A4A4");
            jQuery("#wdm_first_page, #wdm_prev_page").css("cursor","default");

        } else if ( index == (wdm_paged_length-1) ) {
            wdm_prev_index = index-1;
            wdm_next_index = index;
            jQuery("#wdm_next_page, #wdm_last_page").css("color","#A5A4A4");
            jQuery("#wdm_next_page, #wdm_last_page").css("cursor","default");
        }

        var href_str = "javascript:wdm_js_ajax_pagination(replce_index);";

        //console.log( wdm_users );
        //console.log('current_post='+current_post);

        jQuery.ajax({
            url: wdm_reports_obj.admin_ajax_path,
            type: "POST",
            dataType: "html",
            data: {
                action: 'wdm_get_user_html',
                users: wdm_users,
                current_post: current_post
            },
            success: function ( response ) {

                //console.log( response );

                if ( response ) {
                    wdm_reports_obj.paged_index = index;

                    jQuery("#wdm_paged_start_num").html((index+1));
                    jQuery("#wdm_prev_page").attr("href", href_str.replace("replce_index",wdm_prev_index));
                    jQuery("#wdm_next_page").attr("href", href_str.replace("replce_index",wdm_next_index));

                    jQuery("#wdm_report_tbl tbody").hide('fadeOut');
                    jQuery("#wdm_report_tbl tbody").html(response);
                    jQuery("#wdm_report_tbl tbody").show('fadeIn');
                }

            }

        });//AJAX call ends
    }
}
/* -- for report users pagination - ends */

/*
@since 2.1
 --- For other user */
jQuery("document").ready(function () {


    jQuery(".wdmir-email-heading").each(function () {
        jQuery(this).find(".wdmir-shortcode-callback").css("left", jQuery(this).find(".heading").css("width"));
    });

    jQuery(".wdmir-shortcode-close").click(function () {
        jQuery(this).closest(".wdmir-shortcode-callback").slideUp();

    });


    jQuery(".wdmir-shortcodes").click(function () {
        jQuery(this).next(".wdmir-shortcode-callback").slideDown();
        // var wid = jQuery( this ).prev(".heading").css( "width" );
        // console.log( wid );
    });

});
