var slidesCount = 0;
var currentSlide = 0;

var discoverCount = 0;
var currentDiscover = 0;

var slideMode = false;

function resizeSlides()
{
    var width = $(document.body).width();
    var height = $(window).height();

    if (slideMode)
    {
	$('.slideEnabled').height(height);

	$('.slideNumber').each(function() {
	    $(this).css('margin-left', width-$(this).width()-5);
	    $(this).css('margin-top', height-$(this).height()-5);
	});
    } else {
	$('.slideNumber').each(function() {
	    $(this).css('margin-left', width + $(this).width() - 20);
	    $(this).css('margin-top', 5);
	});
    }
}

function updateDiscovers()
{
    $('.discover').hide();

    for (i=0; i<currentSlide-1; i++) {
	$('#slide' + i + ' .discover').show();
    }

    updateCurrentDiscover(0);
}

function updateCurrentDiscover(end)
{
    discoverCount = $('#slide' + currentSlide + ' .discover').length;

    if (end) {
	$('#slide' + currentSlide + ' .discover').show();
	currentDiscover = discoverCount;
    } else {
	$('#slide' + currentSlide + ' .discover').hide();
        currentDiscover = 0;
    }
}

function scrollTo(slideId)
{
    $('html,body').animate({scrollTop:$('#slide' + slideId).offset().top}, 300, 0, function() {
	if (slideMode) {
	   $('body').css('background-color', $('#slide' + slideId + ' .slide').css('background-color'));
	}
    });
}

function scrollToCurrentSlide()
{
    scrollTo(currentSlide);
}

function precSlide()
{
    if (currentSlide > 0) {
	currentSlide--;
        scrollToCurrentSlide();
	updateCurrentDiscover(1);

	return 1;
    }

    return 0;
}

function nextSlide()
{
    if (currentSlide < slidesCount-1) {
        currentSlide++;
        scrollToCurrentSlide();
	updateCurrentDiscover(1);

	return 1;
    }

    return 0;
}

function precDiscover()
{
    if (currentDiscover == 0) {
	if (precSlide()) {
	   updateCurrentDiscover(1);
	}
    } else {
	currentDiscover--;
	$('#discover_' + currentSlide + '_' + currentDiscover).hide();
    }
}

function nextDiscover()
{
    if (currentDiscover == discoverCount) {
	if (nextSlide()) {
	    updateCurrentDiscover(0);
	}
    } else {
	$('#discover_' + currentSlide + '_' + currentDiscover).show();
	currentDiscover++;
    }
}

$(document).keydown(function(e){
    if (!slideMode) 
    {
	return;
    }

    switch (e.keyCode) {
	case 37: // Left
	    precDiscover();
	   break;
	case 39: // Right
	    nextDiscover();
	    break;
	case 38: // Up
	    precSlide();
	break;
	case 40: // Down
	    nextSlide();
        break;
    }
});

function initSlides()
{
    var id = 0;
    slidesCount = $('.slide').length;

    $('.slide').each(function() {
	$(this).wrap('<div class="slideWrapper" rel="' + id + '" id="slide' + id + '"></div>');	

	$('#slide' + id).prepend('<div class="slideNumber" rel="' + id + '">' + (id+1) + '/' + slidesCount + '</div>');

	var discover_id = 0;

	$(this).find('.discover').each(function() {
	    $(this).attr('id', 'discover_' + id + '_' + discover_id);
	    discover_id++;
	});

	id++;
    });
}

function runSlideMode() 
{
    $('.slideModeNormal').removeClass('slideModeEnabled');
    $('.slideModeSlide').addClass('slideModeEnabled');
    $('.slideOnly').show();
    $('.textOnly').hide();
    slideMode = true;
    $('.slideWrapper').addClass('slideEnabled');
    resizeSlides();
    updateDiscovers();
    scrollToCurrentSlide();
}

function runTextMode()
{
    $('body').css('background-color', 'transparent');
    $('.slideModeSlide').removeClass('slideModeEnabled');
    $('.slideModeNormal').addClass('slideModeEnabled');
    $('.discover').show();
    $('.slideOnly').hide();
    $('.textOnly').show();
    slideMode = false;
    $('.slideEnabled').height('auto');
    resizeSlides();
    $('.slideWrapper').removeClass('slideEnabled');
    scrollToCurrentSlide();
}

$(document).ready(function() {
    initSlides();
    setInterval(resizeSlides, 1000);
    
    if (window.location.hash) {
	obj = $(window.location.hash);

	if (obj.length) {
	    currentSlide = obj.closest('.slideWrapper').attr('rel');    
	}
    }

    $('.slideModeNormal').click(function() {
	runTextMode();
    });	

    $('.slideModeSlide').click(function() {
	runSlideMode();
    });	

    $('.slideNumber').click(function() {
	if (!slideMode) {
	    currentSlide = parseInt($(this).attr('rel'));
	    runSlideMode();
	} else {
	    runTextMode();
	}
    });

    runSlideMode();
    runTextMode();
    resizeSlides();
});
