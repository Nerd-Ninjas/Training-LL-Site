$(document).on("click", ".app-sidebar .side-menu li a, .app-header a, .row a" , function (e) {
	e.preventDefault(); // Sidemenu class
	'use strict';
	var page = $(this).attr("href");

	if ($(this).attr("target") == "_self") { window.location.href = page; return true };
	if ($(this).attr("target") == "_blank") {window.open(page, "_blank"); return false};

	if (page == "javascript: void(0);") return false;

	window.location.hash = page;

	
	$(".app-sidebar .side-menu li, .app-sidebar .side-menu li a").removeClass("active");

	$(".app-sidebar .side-menu a").each(function () {
		var pageUrl = window.location.hash.slice(1);
		if ($(this).attr("href") == pageUrl) {
			$(this).addClass("active");
			$(this).parent().addClass("is-expanded");
			$(this).parent().parent().prev().addClass("active");
			$(this).parent().parent().addClass("open");
			$(this).parent().parent().prev().addClass("is-expanded");
			$(this).parent().parent().parent().addClass("is-expanded");
			$(this).parent().parent().parent().parent().addClass("open");
			$(this).parent().parent().parent().parent().prev().addClass("active");
			$(this).parent().parent().parent().parent().parent().addClass("is-expanded");
		}
	});


	if (page == "javascript: void(0);") return false;
	call_ajax_page(page);
	// names();

	
});

function call_ajax_page(page) {

	var title = page;
	var title1 = title;
	
	//document.title = title1 + " | Learnlike";

	$.ajax({
		url: "pages/" + page,
		cache: false,
		dataType: "html",
		type: "GET",
		async: true,
		success: function (data) {
			$("#content").empty();
			$("#content").html(data);
			window.location.hash = page;
			$(window).scrollTop(0);
		}
	});

}



$(document).ready(function () {
	var path = window.location.hash.slice(1);
	if (path == "index.php") {
		call_ajax_page("index.php");
	}
	 else {
		call_ajax_page("index.php");
	}
	
});