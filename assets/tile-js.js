jQuery( document ).ready(function() {

	// # force tile container li's clickable!
	jQuery(function() {
		jQuery('.tiles_container > li').bind('touchend click', function(){
			var url = jQuery('a', this).attr('href');
			var target = jQuery('a', this).attr('target');

			if(url !== 'undefined') {
				// # check for target new window
				if(target == '_blank') {
					window.open(url, '_blank');
				} else {
					window.location = url;
				}
			}
        	
        	return false;
			
		});
	});

	// # icon effects
	jQuery('.tiles_href').bind('click touchstart', function() {
        	jQuery(this).addClass('tiles_icon tiles_bg');
	});

	jQuery('.tiles_href').bind('touchend', function() {

		if(jQuery(this).parent().hasClass('tiles_icon')) {
			jQuery(this).parent().removeAttr('class');
		}
	});

	jQuery('.tiles_href').hover(function() {

		jQuery(this).parent().toggleClass('tiles_icon tiles_bg');

		if(jQuery(this).parent().attr('class') == '') {
			jQuery(this).parent().removeAttr('class');
		}
		
	});


	// # responsive helpers:
	jQuery('#nav-trigger, #nav-sidebar-trigger').click(function(){
    	// # trigger tileShift()
		tileShift(jQuery(window).width());
	});
	// # we need to duplicate the above for our resize events
    jQuery(window).resize(function () {
    	// # trigger tileShift()
		tileShift(jQuery(window).width());
	}).resize();


    // # function to shift and resize tiles depending on panel visibility
	function tileShift(bodyW) {

		var target = '.tiles_container li';
		var is_menu = jQuery('#navigation').hasClass('navigation-hidden');
		var is_sidebar = jQuery('#main-content').hasClass('sidebar-hidden');

		if(bodyW > 0 && bodyW <= 640) {
	 		if(!is_menu) {
				jQuery(target).css('cssText', 'width: 95% !important;');
	 		} else {
				jQuery(target).css('cssText', 'width: 95% !important;');
	 		}
	 	} else if(bodyW  >= 641 && bodyW <= 768) {
	 		if(!is_menu ) {
				jQuery(target).css('cssText', 'width: 95% !important;');
	 		} else {
				jQuery(target).css('cssText', 'width: 48% !important;');
	 		}
	 	} else if(bodyW  >= 769 && bodyW <= 1024) {
	 		if(!is_sidebar) {
				jQuery(target).css('cssText', 'width: 95% !important;');
	 		} else {
				jQuery(target).css('cssText', 'width: 48% !important;');
	 		}
	 	} else if(bodyW >= 1025 && bodyW <= 1279) {

	 		if(!is_sidebar) {
				jQuery(target).css('cssText', 'width: 48% !important;');

	 		} else if(!is_menu && is_sidebar) {
				jQuery(target).css('cssText', 'width: 48% !important;');

			} else if(is_menu && is_sidebar) {
				jQuery(target).css('cssText', 'width: 32% !important;');

	 		} else {
				jQuery(target).css('cssText', 'width: 32% !important;');
	 		}
		} else {
			jQuery(target).css('cssText', 'width: 32% !important;');
		}

		return;
	}

	// # assign icons based on page location:
	jQuery(function() {
		
		var url = window.location.pathname;
		var iconClass;

		if(url == "/training/connected-home-training/") {
			iconClass = 'tiles_container_video';
		} else if(url == "/training/credit-card-training/pcr-credit-card/" || 
		   url == "/training/credit-card-training/" || 
		   url == "/training/credit-card-training/emv-credit-card-training/") {
			iconClass = 'tiles_container_creditcard';
		} else if(url == "/store-product-info/appliance-stock-check-order-forms/appliance-stock-check/") {
			iconClass = 'tiles_container_external';
		} else if(url == "/employee-info/benefits-plan-info/health-dental-vision-plans/") {
			iconClass = 'tiles_container_health';
		}else if(url == "/employee-info/benefits-plan-info/disability-coverage/") {
			iconClass = 'tiles_container_disability';
		} else if(url == "/employee-info/employee-perks/entertainment-guide/") {
			iconClass = 'tiles_container_tickets';
		} else if(url == "/store-product-info/product-showroom-info/bedding/" || 
		   url == "/store-product-info/product-showroom-info/bedding/mattress/" || 
		   url == "/store-product-info/product-showroom-info/bedding/bed-frames/" || 
		   url == "/store-product-info/product-showroom-info/bedding/mattress-accessories/") {
			iconClass = 'tiles_container_bedding';
		} else if(url == "/store-product-info/sales-contests/" || 
		   url == "/store-product-info/appliance-newsletters/core-appliances/" || 
		   url == "/store-product-info/appliance-newsletters/designer-appliances/" || 
		   url == '/store-product-info/appliance-newsletters/' ) {
			iconClass = 'tiles_container_rebates';
		} else if(url == "/store-product-info/product-showroom-info/in-store-promo-tags/") {
			iconClass = 'tiles_container_tags';
		} else if(url == "/store-product-info/product-showroom-info/in-store-promo-tags/") {
			iconClass = 'tiles_container_tags';
		} else if(url == "/store-product-info/product-showroom-info/plan-o-grams/") {
			iconClass = 'tiles_container_planograms';
		} else if(url == "/store-product-info/product-showroom-info/outdoor-cooking/") {
			iconClass = 'tiles_container_cooking';
		} else if(url == "/security-center/") {
			iconClass = 'tiles_container_security';
		}

		if(!jQuery('.tiles_container').hasClass(iconClass)) {
			jQuery('.tiles_container').addClass(iconClass);
		}

		return;
	});
});