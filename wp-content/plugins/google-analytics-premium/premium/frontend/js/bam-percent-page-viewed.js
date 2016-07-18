/**
 * Percent page viewed plugin
 *
 * Usage:
 * bamPercentPageViewed.init({ option : 'value' });
 *
 * Options:
 * trackDelay : 1500 - The delay (in ms) before a scroll position is considered stable (reduce scroll bouncers)
 * percentInterval : 10 - Track every 10% the page is scrolled | Default: 25
 * callback : function(data){ console.log(data); } - The callback for the previous page scroll position | Default: null
 * cookieName : _bamPercentPageViewed - The name of the cookie to store the data
 *
 */
(function(bamPercentPageViewed)
{
	/**
	 * Default options
	 */
	var defaultOptions = {
		trackDelay: 1500,
		percentInterval : 25,
		callback : null,
		cookieName: '_bamPercentPageViewed'
	};


	/**
	 * Last scroll action
	 */
	var lastScroll = null;



	/**
	 * Initialize the tracker and pass in optional overrides
	 * @param object
	 * @return bamPercentPageViewed
	 */
	bamPercentPageViewed.init = function(options)
	{
		// Options
		if(typeof(options) == typeof({}))
		{
			for(var property in options)
			{
				if(options.hasOwnProperty(property))
				{
					defaultOptions[property] = options[property];
				}
			}
		}

		// Callback
		if(typeof(options.callback) == 'function')
		{
			// If there is data to track, fire callback
			var cookieData = bamPercentPageViewed.callback();
			if(cookieData !== false)
			{
				options.callback.call(window, cookieData);
			}
		}

		// Get the amount of screen currently in view
		processScroll();

		// Do not break the chain
		return this;
	};



	/**
	 * Get callback data externally
	 * @return object|false
	 */
	bamPercentPageViewed.callback = function()
	{
		var cookieData = readCookie();
		if(cookieData !== false && cookieData.scrollPercent > 0 && cookieData.documentLocation.length > 0)
		{
			// Clear cookie
			setCookie(0, '', true);

			// Return object
			return cookieData;
		}

		return false;
	};



	/**
	 * Get data externally without resetting
	 * @return object|false
	 */
	bamPercentPageViewed.data = function()
	{
		var cookieData = readCookie();
		if(cookieData !== false && cookieData.scrollPercent > 0 && cookieData.documentLocation.length > 0)
		{
			// Return object
			return cookieData;
		}

		return false;
	};



	/**
	 * Throttle function borrowed from:
	 * Underscore.js 1.5.2
	 * http://underscorejs.org
	 * (c) 2009-2013 Jeremy Ashkenas, DocumentCloud and Investigative Reporters & Editors
	 * Underscore may be freely distributed under the MIT license.
	 */
	function throttle(func, wait)
	{
		var context, args, result;
		var timeout = null;
		var previous = 0;
		var later = function()
		{
			previous = new Date();
			timeout = null;
			result = func.apply(context, args);
		};
		return function()
		{
			var now = new Date();
			if(!previous) previous = now;
			var remaining = wait - (now - previous);
			context = this;
			args = arguments;
			if(remaining <= 0)
			{
				clearTimeout(timeout);
				timeout = null;
				previous = now;
				result = func.apply(context, args);
			}else if(!timeout){
				timeout = setTimeout(later, remaining);
			}
			return result;
		};
	}



	/**
	 * Page scroll percentage
	 * @return int
	 */
	function scrollPercent()
    {
        return Math.ceil( ((scrollPosition() + viewportHeight()) / pageHeight()) * 100 );
    }


	/**
	 * Page height
	 * @return int
	 */
	function pageHeight()
	{
		return Math.max(
			document.body.scrollHeight, document.documentElement.scrollHeight,
			document.body.offsetHeight, document.documentElement.offsetHeight,
			document.body.clientHeight, document.documentElement.clientHeight
		);
	}



	/**
	 * Viewport height
	 * @return int
	 */
	function viewportHeight()
	{
		return (window.innerHeight || document.documentElement.clientHeight || document.getElementsByTagName('body')[0].clientHeight || 0);
	}



	/**
	 * Current window scroll position
	 * @return int
	 */
	function scrollPosition()
	{
		return (window.pageYOffset || document.documentElement.scrollTop)  - (document.documentElement.clientTop || 0);
	}



	/**
	 * Scroll percentage by interval
	 * @return int
	 */
	function scrollPercentByInterval(percentInterval)
	{
		var multiplier = parseInt(scrollPercent() / (parseInt(percentInterval) - 0.0000000001));
		return (multiplier * parseInt(percentInterval));
	}



	/**
	 * Set cookie
	 * @param int scrollPercent
	 * @param string documentLocation
	 * @param bool forceReset
	 * @return bool
	 */
	function setCookie(scrollPercent, documentLocation, forceReset)
	{
		scrollPercent = parseInt(scrollPercent);

		// Get previous cookie value
		var cookieData = readCookie();

		if(scrollPercent > cookieData.scrollPercent || forceReset === true)
		{
			// Get expiration time
			expire_time = new Date(); // Local time
			expire_time.setTime(expire_time.getTime() + (60 * 60000));

			// Set cookie
			document.cookie = defaultOptions.cookieName + '=' + escape(scrollPercent + '|||' + documentLocation) + "; expires=" + expire_time.toUTCString() + '; path=/';
		}
	}



	/**
	 * Read cookie value
	 * @return object
	 */
	function readCookie()
	{
		// Get cookie
		var value = ((document.cookie.match('(^|; )' + defaultOptions.cookieName + '=([^;]*)')||0)[2]);
		if(value === undefined)
		{
			value = '0|||';
		}

		// Decode and split
		value = decodeURIComponent(value);
		value = value.split('|||');

		// Return object
		return (value.length == 2) ? {
			scrollPercent : parseInt(value[0]),
			documentLocation : value[1]
		} : false;
	}



	/**
	 * Process scroll
	 */
	function processScroll()
	{
		if(lastScroll === null || (new Date().getTime()) - lastScroll >= parseInt(defaultOptions.trackDelay))
		{
			setCookie(scrollPercentByInterval(defaultOptions.percentInterval), document.location.href);
		}
	}



	/**
	 * Listen for throttled window scroll event
	 */
	window.onscroll = throttle(function()
	{
		lastScroll = new Date().getTime();

		setTimeout(processScroll, parseInt(defaultOptions.trackDelay) + 25);
	}, 100);



}(window.bamPercentPageViewed = window.bamPercentPageViewed || {}));