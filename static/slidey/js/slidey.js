/**
 * Slidey will manage the slideMode and textMode
 */
function Slidey()
{
    var slidey = this;

    /**
     * Is the slide Mode enabled ?
     */
    this.slideMode = false;

    /**
     * Are the controls enabled ?
     */
    this.controlsEnabled = true;

    /**
     * Slide count and current
     */
    this.slidesCount = 0;
    this.currentSlide = 0;

    /**
     * Discover count and current
     */
    this.discoverCount = 0;
    this.currentDiscover = 0;

    /**
     * Ticking interval
     */
    this.interval = null;

    /**
     * Events
     */
    this.events = {};

    /**
     * Add an event
     */
    this.on = function(evt, callback)
    {
        if (this.events[evt] == undefined) {
            this.events[evt] = [];
        }

        this.events[evt].push(callback);
    };

    /**
     * Invoke an event
     */
    this.dispatch = function(evt, argument)
    {
        if (this.events[evt] != undefined) {
            var callbacks = this.events[evt];
            for (k in callbacks) {
                callbacks[k](argument);
            }
        }
    };

    /**
     * Resizes the slide to fit the screen size
     */
    this.resizeSlides = function()
    {
        var width = $(document.body).width();
        var height = $(window).height();

        if (this.slideMode)
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
    };

    /**
     * Go to the given slide & discover
     */
    this.goTo = function(slide, discover)
    {
        if (slidey.currentSlide != slide) {
            slidey.currentSlide = slide;
            slidey.scrollTo(slidey.currentSlide);
        }
            
        $('#slide' + this.currentSlide + ' .discover').hide();

        for (i=0; i<discover; i++) {
            $('#discover_' + slide + '_' + i).show();
        }
    };

    /**
     * Update the discovers visibility
     */
    this.updateDiscovers = function()
    {
        $('.discover').hide();

        for (i=0; i<this.currentSlide-1; i++) {
            $('#slide' + i + ' .discover').show();
        }

        this.updateCurrentDiscover(0);
    };

    /**
     * Update the current slide discover visibility
     *
     * If end is set, starts with the end (all discovered)
     */
    this.updateCurrentDiscover = function(end)
    {
        this.discoverCount = $('#slide' + this.currentSlide + ' .discover').length;

        if (end) {
            $('#slide' + this.currentSlide + ' .discover').show();
            this.currentDiscover = this.discoverCount;
        } else {
            $('#slide' + this.currentSlide + ' .discover').hide();
            this.currentDiscover = 0;
        }
    };

    /**
     * Scrolls to a given slide
     */
    this.scrollTo = function(slideId)
    {
        $('html,body').animate({scrollTop:$('#slide' + slideId).offset().top}, 300, 0, function() {
            if (slidey.slideMode) {
               $('body').css('background-color', $('#slide' + slideId + ' .slide').css('background-color'));
            }
        });
    };

    /**
     * Scrolls to the current slide
     */
    this.scrollToCurrentSlide = function()
    {
        this.scrollTo(this.currentSlide);
    };

    /**
     * Go to the slide just before
     */
    this.precSlide = function()
    {
        if (this.currentSlide > 0) {
            this.currentSlide--;
            this.scrollToCurrentSlide();
            this.updateCurrentDiscover(1);
            this.dispatch('moved');
            return 1;
        }

        return 0;
    };

    /**
     * Go to the slide just after
     */
    this.nextSlide = function()
    {
        if (this.currentSlide < this.slidesCount-1) {
            this.currentSlide++;
            this.scrollToCurrentSlide();
            this.updateCurrentDiscover(1);
            this.dispatch('moved');
            return 1;
        }

        return 0;
    };

    /**
     * Go to the discover just before
     */
    this.precDiscover = function()
    {
        if (this.currentDiscover == 0) {
            if (this.precSlide()) {
               this.updateCurrentDiscover(1);
            }
        } else {
            this.currentDiscover--;
            $('#discover_' + this.currentSlide + '_' + this.currentDiscover).hide();
        }
            
        this.dispatch('moved');
    };

    /**
     * Go to the discover just after
     */
    this.nextDiscover = function()
    {
        if (this.currentDiscover == this.discoverCount) {
            if (this.nextSlide()) {
                this.updateCurrentDiscover(0);
            }
        } else {
            $('#discover_' + this.currentSlide + '_' + this.currentDiscover).show();
            this.currentDiscover++;
        }
        
        this.dispatch('moved');
    };

    /**
     * Initializes IDs
     */
    this.on('init', function()
    {
        var id = 0;
        slidey.slidesCount = $('.slide').length;

        $('.slide').each(function() {
            $(this).wrap('<div class="slideWrapper" rel="' + id + '" id="slide' + id + '"></div>');	

            $('#slide' + id).prepend('<div class="slideNumber" rel="' + id + '">' + (id+1) + '/' + slidey.slidesCount + '</div>');

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
    });

    /**
     * Go to slide mode
     */
    this.runSlideMode = function()
    {
        $('.slideModeNormal').removeClass('slideModeEnabled');
        $('.slideModeSlide').addClass('slideModeEnabled');
        $('.slideOnly').show();
        $('.textOnly').hide();
        this.slideMode = true;
        $('.slideWrapper').addClass('slideEnabled');
        this.resizeSlides();
        this.updateDiscovers();
        this.dispatch('slideMode');
        this.dispatch('moved');
    };

    /**
     * Go to text mode
     */
    this.runTextMode = function()
    {
        $('body').css('background-color', 'transparent');
        $('.slideModeSlide').removeClass('slideModeEnabled');
        $('.slideModeNormal').addClass('slideModeEnabled');
        $('.discover').show();
        $('.slideOnly').hide();
        $('.textOnly').show();
        this.slideMode = false;
        $('.slideEnabled').height('auto');
        this.resizeSlides();
        $('.slideWrapper').removeClass('slideEnabled');
        this.dispatch('textMode');
    };

    /**
     * Called on each tick
     */
    this.on('tick', function()
    {
        slidey.resizeSlides();
    });

    this.tick = function()
    {
        slidey.dispatch('tick');
    };

    this.stopInterval = function()
    {
        if (this.interval) {
            clearInterval(this.interval);
        }
    };

    this.resetInterval = function()
    {
        this.stopInterval();
        this.interval = setInterval(slidey.tick, 500);
    };

    this.init = function() {
        $(document).ready(function()
        {
            slidey.dispatch('init');
            slidey.resetInterval();
            
            if (window.location.hash) {
                obj = $(window.location.hash);

                if (obj.length) {
                    slidey.currentSlide = obj.closest('.slideWrapper').attr('rel');    
                }
            }

            $('.slideModeNormal').click(function() {
                slidey.runTextMode();
                slidey.scrollToCurrentSlide();
            });	

            $('.slideModeSlide').click(function() {
                slidey.runSlideMode();
                slidey.scrollToCurrentSlide();
            });	

            $('.slideNumber').click(function() {
                if (!slidey.slideMode) {
                    slidey.currentSlide = parseInt($(this).attr('rel'));
                    slidey.runSlideMode();
                } else {
                    slidey.runTextMode();
                }
                slidey.scrollToCurrentSlide();
                slidey.dispatch('moved');
            });

            slidey.runSlideMode();
            slidey.runTextMode();

            slidey.dispatch('tick');
        });

        $(document).keydown(function(e){
            if (!slidey.slideMode || !slidey.controlsEnabled)
            {
                return;
            }

            switch (e.keyCode) {
                case 37: // Left
                    slidey.precDiscover();
                   break;
                case 39: // Right
                    slidey.nextDiscover();
                    break;
                case 38: // Up
                    slidey.precSlide();
                break;
                case 40: // Down
                    slidey.nextSlide();
                break;
            }
        });
    };
};

var slidey = new Slidey();
new SlideyMenuExtension(slidey);
new SlideyImagesExtension(slidey);
new SlideySpoilersExtension(slidey);
new SlideyMobileExtension(slidey);
new SlideyStepsExtension(slidey);
if (typeof(SlideyInteractiveExtension) != 'undefined') {
    interactive = new SlideyInteractiveExtension(slidey);
}
if (typeof(SlideyPollExtension) != 'undefined') {
    new SlideyPollExtension(slidey, interactive);
}
slidey.init();
