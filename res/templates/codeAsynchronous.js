var _gaq = _gaq || [];
_gaq.push(['_setAccount', "###ACCOUNT###"]);
###COMMANDS###

(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();

	// Provide convenience methods related to GA API usage, wrapped in some global object
var RsGoogleAnalytics = {
	/**
	 * Opens a cross-domain link while passing the appropriate parameters for continued visitor tracking
	 * (see https://developers.google.com/analytics/devguides/collection/gajs/methods/gaJSApiDomainDirectory#_gat.GA_Tracker_._getLinkerUrl)
	 *
	 * @param link <a> tag that was clicked
	 */
	crossDomainLink: function(link) {
		_gaq.push(function() {
				// Get the default tracker
			var pageTracker = _gat._getTrackerByName();
				// Extract the destination URL and attach tracking information to it
			var linkerUrl = pageTracker._getLinkerUrl(link.href);
				// Redirect to cross-domain URL, optionally in new window if target is so defined
			if (link.target == '_blank') {
				window.open(linkerUrl);
			} else {
				window.location = linkerUrl;
			}
		});
	}
};
