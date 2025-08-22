/**
 * Convly Public Tracking JavaScript
 */

(function($) {
    'use strict';

    window.convlyTracker = {
        tracked: false,
        clickHandlersAttached: false,
        trackedButtons: {}, // برای ذخیره وضعیت هر دکمه

        init: function() {
            // Track page view on load
            if (!this.tracked) {
                this.trackPageView();
            }

            // Attach click handlers
            if (!this.clickHandlersAttached) {
                this.attachClickHandlers();
				
            }
			// Track scroll depth
this.trackScrollDepth();
        },

        trackPageView: function() {
            if (this.tracked || !convly_public || !convly_public.page_info) {
                return;
            }

            const data = {
                action: 'convly_track_view',
                nonce: convly_public.nonce,
                page_id: convly_public.page_info.page_id,
                page_url: convly_public.page_info.page_url,
                page_title: convly_public.page_info.page_title,
                page_type: convly_public.page_info.page_type,
                visitor_id: convly_public.visitor_id
            };

            $.ajax({
                url: convly_public.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        convlyTracker.tracked = true;
                    }
                },
                error: function() {
                    console.error('Convly: Failed to track page view');
                }
            });
        },

        attachClickHandlers: function() {
            if (this.clickHandlersAttached || !convly_public.buttons || convly_public.buttons.length === 0) {
                return;
            }

            // Attach handlers for each configured button
            convly_public.buttons.forEach(function(button) {
                const selector = button.button_type === 'link' 
                    ? `a#${button.button_css_id}` 
                    : `#${button.button_css_id}`;

                // Use event delegation for dynamic content
                $(document).on('click', selector, function(e) {
                    // کلید یکتا برای هر دکمه در هر صفحه
                    const buttonKey = convly_public.page_info.page_id + '_' + button.button_css_id;
                    
                    // اگر این دکمه در این صفحه قبلاً کلیک نشده، آن را ثبت کن
                    if (!convlyTracker.trackedButtons[buttonKey]) {
                        convlyTracker.trackClick(button.button_css_id);
                        convlyTracker.trackedButtons[buttonKey] = true;
                    }
                });
            });

            this.clickHandlersAttached = true;
        },

        trackClick: function(buttonId) {
            const data = {
                action: 'convly_track_click',
                nonce: convly_public.nonce,
                page_id: convly_public.page_info.page_id,
                button_id: buttonId,
                visitor_id: convly_public.visitor_id
            };

            $.ajax({
                url: convly_public.ajax_url,
                type: 'POST',
                data: data,
                error: function() {
                    console.error('Convly: Failed to track button click');
                }
            });
        }
		
		trackScrollDepth: function() {
    let maxScroll = 0;
    let scrollTracked = false;
    
    $(window).on('scroll', function() {
        const scrollTop = $(window).scrollTop();
        const docHeight = $(document).height();
        const winHeight = $(window).height();
        const scrollPercent = Math.round((scrollTop / (docHeight - winHeight)) * 100);
        
        if (scrollPercent > maxScroll) {
            maxScroll = scrollPercent;
        }
    });
    
    // Send max scroll depth before page unload
    $(window).on('beforeunload', function() {
        if (!scrollTracked && maxScroll > 0) {
            // Use sendBeacon for reliability
            const data = new FormData();
            data.append('action', 'convly_track_scroll');
            data.append('nonce', convly_public.nonce);
            data.append('page_id', convly_public.page_info.page_id);
            data.append('visitor_id', convly_public.visitor_id);
            data.append('scroll_depth', maxScroll);
            
            navigator.sendBeacon(convly_public.ajax_url, data);
            scrollTracked = true;
        }
    });
},
		
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        convlyTracker.init();
    });

    // Also initialize on window load as fallback
    $(window).on('load', function() {
        convlyTracker.init();
    });

})(jQuery);