if (typeof flip_expand_collapse == 'undefined') {
	function flip_expand_collapse(what, id) {
	    //console.log(id + ':' + document.getElementById( 'list_arrow.flippable-'+id).className);
	    if (jQuery( what + '-' + id + ' .list_arrow.flippable' ).hasClass( 'expand' ) ) {
	        jQuery( what + '-' + id + ' .list_arrow.flippable' ).removeClass( 'expand' );
	        jQuery( what + '-' + id + ' .list_arrow.flippable' ).addClass( 'collapse' );
	        jQuery( what + '-' + id + ' .flip' ).slideUp();
	    } else {
	        jQuery( what + '-' + id + ' .list_arrow.flippable' ).removeClass( 'collapse' );
	        jQuery( what + '-' + id + ' .list_arrow.flippable' ).addClass( 'expand' );
	        jQuery( what + '-' + id + ' .flip' ).slideDown();
	    }
	    return false;
	}
}

if (typeof flip_expand_all == 'undefined') {
	function flip_expand_all(what) {
	    jQuery( what + ' .list_arrow.flippable' ).removeClass( 'collapse' );
	    jQuery( what + ' .list_arrow.flippable' ).addClass( 'expand' );
	    jQuery( what + ' .flip' ).slideDown();
	    return false;
	}
}

if (typeof flip_collapse_all == 'undefined') {
	function flip_collapse_all(what) {
	    jQuery( what + ' .list_arrow.flippable' ).removeClass( 'expand' );
	    jQuery( what + ' .list_arrow.flippable' ).addClass( 'collapse' );
	    jQuery( what + ' .flip' ).slideUp();
	    return false;
	}
}

String.prototype.toHHMMSS = function() {
    sec_numb = parseInt( this, 10 );
    var hours = Math.floor( sec_numb / 3600 );
    var minutes = Math.floor( ( sec_numb - ( hours * 3600 ) ) / 60 );
    var seconds = sec_numb - ( hours * 3600 ) - ( minutes * 60 );
    if ( hours < 10 ) {
        hours = '0' + hours;
    }
    if ( minutes < 10 ) {
        minutes = '0' + minutes;
    }
    if ( seconds < 10 ) {
        seconds = '0' + seconds;
    }
    var time = hours + ':' + minutes + ':' + seconds;
    return time;
}

function learndash_timer() {
    document.getElementById( 'learndash_mark_complete_button' ).disabled = true;
    learndash_forced_lesson_time = learndash_forced_lesson_time - 1;
    document.getElementById( 'learndash_timer' ).innerHTML = learndash_forced_lesson_time.toString().toHHMMSS();
    if ( learndash_forced_lesson_time <= 0 ) {
        clearInterval( learndash_timer_var );
        document.getElementById( 'learndash_mark_complete_button' ).disabled = false;
        document.getElementById( 'learndash_timer' ).innerHTML = '';
    }
}

jQuery( function() {
    function force_max_12px_font_size() {
        var f1 = jQuery( '#course_navigation .learndash_nevigation_lesson_topics_list a' ).css( 'font-size' );
        var f2 = jQuery( '#course_navigation .learndash_nevigation_lesson_topics_list a span' ).css( 'font-size' );
        if ( f1 != undefined && f1.replace( 'px', '' ) > 12 || f2 != undefined && f2.replace( 'px', '' ) > 12 ) {
            jQuery( '#course_navigation .learndash_nevigation_lesson_topics_list a, #course_navigation .learndash_nevigation_lesson_topics_list a span' ).css( 'font-size', 12 );
        }
    }
    force_max_12px_font_size();
});