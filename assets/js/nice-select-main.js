(function ($) {
	$(document).ready(function () {
		console.log(niceSelectData);
		const selector = niceSelectData.selector != "" ? niceSelectData.selector : "select";
		if (niceSelectData.placeholder_text !== "") {
			$(selector).prepend("<option style='display:none' data-value='" + niceSelectData.placeholder_text + "' selected>" + niceSelectData.placeholder_text + "</option>");
		}
		if (niceSelectData.alignment === "right") {
			$(selector).addClass('right');
		}
		if (niceSelectData.fullWidth === "enable") {
			$(selector).addClass('wide');
		} else {
			$(selector).removeClass('wide');
		}
		$(selector).niceSelect();
		$('head').append("<style>" + niceSelectData.custom_css + "</style>");
		$(selector).next('.nice-select').find("li").first().hide();
	});
})(jQuery);