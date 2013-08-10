/* 
 * This script handles mosaic events.
 */

$(window).load(function() {
    // circle
    $('.circle').mosaic({
        opacity		:	0.85            // opacity for overlay (0-1)
    });
    // fade
    $('.fade').mosaic();
    // bar
    $('.bar').mosaic({
        animation	:	'slide'		// fade or slide
    });
    // bar 2
    $('.bar2').mosaic({
        animation	:	'slide'		// fade or slide
    });
    // bar 3
    $('.bar3').mosaic({
        animation	:	'slide',	// fade or slide
        anchor_y	:	'top'		// vertical anchor position
    });
    // cover
    $('.cover').mosaic({
        animation	:	'slide',	// fade or slide
        hover_x		:	'400px'		// horizontal position on hover
    });
    // cover 2
    $('.cover2').mosaic({
        animation	:	'slide',	// fade or slide
        anchor_y	:	'top',		// vertical anchor position
        hover_y		:	'80px'		// vertical position on hover
    });
    // cover 3
    $('.cover3').mosaic({
        animation	:	'slide',	// fade or slide
        hover_x		:	'400px',	// horizontal position on hover
        hover_y		:	'300px'		// vertical position on hover
    });    
});