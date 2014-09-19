document.addEventListener("DOMContentLoaded", function() {
	window.setInterval(function() {
		$.ajax({
			url: "/ajax/mail/unread",
			type: "GET",
			dataType: "json",
			success: function(response) {
				$("#mail_count").text(response.count);
				title();
			}
		});
	}, 30000);

	window.setInterval(function() {
		$.ajax({
			url: "/ajax/twitter/total",
			type: "GET",
			dataType: "json",
			success: function(response) {
				$("#tweet_count").text(response.count);
				title();
			}
		});
	}, 30000);

	window.setInterval(function() {
		$.ajax({
			url: "/ajax/rss/total",
			type: "GET",
			dataType: "json",
			success: function(response) {
				$("#rss_count").text(response.count);
				title();
			}
		});
	}, 30000);
});

function title() {
	var total = parseInt($("#mail_count").text()) + parseInt($("#rss_count").text()) + parseInt($("#tweet_count").text());
	$("title").text("(" + total + ") Dashboard");
}