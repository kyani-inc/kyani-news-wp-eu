
(function ($) {
	$(document).ready(function () {
		$('.nav-button').click(function (e) {
			e.preventDefault();
			$('body').toggleClass('nav-open');
			$('#side-panel-menu').toggleClass('visible');
		});

		$("ul.dropdown-menu [data-toggle='dropdown']").on("click", function (event) {
			event.stopPropagation();
			$(this).siblings().toggleClass("show");
		});
	});

	$(document).click(function (event) {
		if ($('body').hasClass('nav-open')) {
			if (!($(event.target).hasClass("side-menu") || $(event.target).is('#nav-icon3') || $(event.target).hasClass('side-panel-btn'))) {
				$('body').toggleClass('nav-open');
				$('#side-panel-menu').toggleClass('visible');
			}
		}
	});
})(jQuery);

(function ($) {
	var autocollapse = function (menu, maxHeight) {

		var nav = $(menu);
		var navHeight = nav.innerHeight();
		if (navHeight >= maxHeight) {

			$(menu + ' .dropdown').removeClass('d-none');
			$(".navbar-nav").removeClass('w-auto').addClass("w-100");

			while (navHeight > maxHeight) {
				//  add child to dropdown
				var children = nav.children(menu + ' li:not(:last-child)');
				var count = children.length;
				$(children[count - 1]).prependTo(menu + ' .dropdown-menu');
				navHeight = nav.innerHeight();
			}
			$(".navbar-nav").addClass("w-auto").removeClass('w-100');

		} else {

			var collapsed = $(menu + ' .dropdown-menu').children(menu + ' li');

			if (collapsed.length === 0) {
				$(menu + ' .dropdown').addClass('d-none');
			}

			while (navHeight < maxHeight && (nav.children(menu + ' li').length > 0) && collapsed.length > 0) {
				//  remove child from dropdown
				collapsed = $(menu + ' .dropdown-menu').children('li');
				$(collapsed[0]).insertBefore(nav.children(menu + ' li:last-child'));
				navHeight = nav.innerHeight();
			}

			if (navHeight > maxHeight) {
				autocollapse(menu, maxHeight);
			}
		}
	}

	$(document).ready(function () {

		// when the page loads
		autocollapse('#nav', 50);

		// when the window is resized
		$(window).on('resize', function () {
			autocollapse('#nav', 50);
		});

	});

})(jQuery);
