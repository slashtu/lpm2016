/**
 * Use the bamPercentPageViewed plugin
 */
(function() {
    var o=onload, n=function(){
        bamPercentPageViewed.init({
            percentInterval : '10',
            cookieName      : '__ga_premium_yoast_scroll_depth'
        });
    }
    if (typeof o!='function'){onload=n} else { onload=function(){ n();o();}}
})(window);

/**
 * When the user navigates away from the page, get the custom dimension from a hidden span in the head and call send_custom_dimension_scroll_depth function
 */
window.onbeforeunload = function() {
    var custom_dimension_id = document.getElementById('custom-dimension-scroll_depth').getAttribute('data-yoast-cd-id');

    if( custom_dimension_id ) {
        send_custom_dimension_scroll_depth( custom_dimension_id );
    }
}

/**
 * Get the scroll depth data and send this to Google Analytics
 *
 * @param String custom_dimension_id
 * @param String ua_code
 */
function send_custom_dimension_scroll_depth( custom_dimension_id ) {
    var scrolldepthData = bamPercentPageViewed.callback();

    if(scrolldepthData != false) {
        scroll_depth = scrolldepthData.scrollPercent + '%';

        __gaTracker( 'set', custom_dimension_id, scroll_depth );
        __gaTracker( 'send', 'event', 'custom_dimensions', 'scroll_depth', {
            nonInteraction: 1
        } );
    }
}

