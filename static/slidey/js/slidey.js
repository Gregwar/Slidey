var slidesCount = 0;
var currentSlide = 0;

var discoverCount = 0;
var currentDiscover = 0;

var slideMode = false;

var imageSizes = {};

function generateMenu()
{
    var menuElements = '';

    if ($('h1, h2, h3').length < 2) {
	return;
    }

    $('h1, h2, h3').each(function() {
	if ($(this).is(':visible')) {
	    html = '<div id="menu_for_'+$(this).attr('id')+'" rel="'+$(this).attr('id')+'" class="menuItem menu'+$(this)[0].tagName.toLowerCase()+'">'+$(this).html()+'</div>';
	    menuElements += html;
	}
    });

    $('.menu').html(menuElements);

    $('.menuItem').click(function() {
	var titleId = $(this).attr('rel');
	if (!slideMode) {
	    $('html,body').animate({scrollTop:$('#' + titleId).offset().top}, 300, 0);
	} else {
	    currentSlide = $('#' + titleId).closest('.slideWrapper').attr('rel');
	    scrollTo(currentSlide);
	    updateDiscovers(0);
	}
    });
}

function updateMenuPosition()
{
    var scrollTop = $('html').scrollTop();

    $('h1, h2, h3').each(function() {
	if ($(this).is(':visible')) {
	    var menuElement = $('#menu_for_' + $(this).attr('id'));

	    if ($(this).offset().top < scrollTop) {
		if (!menuElement.hasClass('menuPassed')) {
		    menuElement.addClass('menuPassed');
		}
	    } else {
		if (menuElement.hasClass('menuPassed')) {
		    menuElement.removeClass('menuPassed');
		}
	    }
	}
    });
}

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

function initSpoilers()
{
    var spoilerId = 0;

    $('.spoiler').each(function() {
	$(this).attr('id', 'spoiler'+spoilerId);

	$(this).wrap('<div class="spoilerWrapper" id="spoilerWrapper'+spoilerId+'"></div>');
	$('#spoilerWrapper'+spoilerId).prepend('<a rel="'+spoilerId+'" class="spoilerLink" href="javascript:void(0);">Afficher/masquer le contenu</a>');

	spoilerId++;
    });

    $('.spoilerLink').click(function() {
	var spoiler = $('#spoiler' + $(this).attr('rel'));

	if (spoiler.is(':visible')) {
	    spoiler.slideUp();
	} else {
	    spoiler.slideDown();
	}
    });

    $('.spoiler').hide();
}

function init()
{
    initSpoilers();

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

    var titleId = 0;
    $('h1, h2, h3').each(function() {
	if (!$(this).attr('id')) {
	    $(this).attr('id', 'title'+(titleId++));
	}
    });

    var imageId = 0;
    $('.contents img').each(function() {
        var myId = $(this).attr('id');

        if (!myId) {
            myId = 'image' + (imageId++);
            $(this).attr('id', myId);
        }

        $(this).load(function() {
            imageSizes[myId] = [$(this).width(), $(this).height()];
            updateImage(myId);
        });
    });
}

function updateImages()
{
    for (id in imageSizes) {
        updateImage(id);
    }
}

function updateImage(imageId)
{
    var image = $('#' + imageId);
    var size = imageSizes[imageId];

    if (slideMode) {
        image.width(size[0]);
        image.height(size[1]);
    } else {
        image.width(size[0]/2.0);
        image.height(size[1]/2.0);
    }
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
    generateMenu();
    updateImages();
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
    generateMenu();
    updateImages();
}

function tick()
{
    resizeSlides();
    updateMenuPosition();
}

$(document).ready(function() {
    init();
    setInterval(tick, 500);
    
    if (window.location.hash) {
	obj = $(window.location.hash);

	if (obj.length) {
	    currentSlide = obj.closest('.slideWrapper').attr('rel');    
	}
    }

    $('.slideModeNormal').click(function() {
	runTextMode();
	scrollToCurrentSlide();
    });	

    $('.slideModeSlide').click(function() {
	runSlideMode();
        scrollToCurrentSlide();
    });	

    $('.slideNumber').click(function() {
	if (!slideMode) {
	    currentSlide = parseInt($(this).attr('rel'));
	    runSlideMode();
	} else {
	    runTextMode();
	}
	scrollToCurrentSlide();
    });

    runSlideMode();
    runTextMode();
    resizeSlides();
    updateMenuPosition();
});
