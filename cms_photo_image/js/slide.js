$(function() {
	$("dl#detail_search dt.bt_search").click(function(){
		$("dl#detail_search dd.search_contents").slideToggle("fast");
		$(this).toggleClass("active");
		return false;
	});
});


