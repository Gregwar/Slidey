var slidesCount = 0;
var currentSlide = 0;
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
    currentSlide = Math.max(0, currentSlide-1);
    scrollToCurrentSlide();
}

function nextSlide()
{
    currentSlide = Math.min(slidesCount-1, currentSlide+1);
    scrollToCurrentSlide();
}

$(document).keydown(function(e){
    if (!slideMode) 
    {
	return;
    }

    if (e.keyCode == 38)
    {
	precSlide();
    }

    if (e.keyCode == 40)
    {
	nextSlide();
    }
});

function initSlides()
{
    var id = 0;
    slidesCount = $('.slide').length;

    $('.slide').each(function() {
	$(this).wrap('<div class="slideWrapper" rel="' + id + '" id="slide' + id + '"></div>');	

	$('#slide' + id).prepend('<div class="slideNumber" rel="' + id + '">' + (id+1) + '/' + slidesCount + '</div>');

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
    scrollToCurrentSlide();
}

function runTextMode()
{
    $('body').css('background-color', 'transparent');
    $('.slideModeSlide').removeClass('slideModeEnabled');
    $('.slideModeNormal').addClass('slideModeEnabled');
    $('.slideOnly').hide();
    $('.textOnly').show();
    slideMode = false;
    $('.slideEnabled').height('auto');
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
    resizeSlides();
});
