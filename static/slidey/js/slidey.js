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
     * Called on each tick
     */
    this.on('tick', function()
    {
        slidey.resizeSlides(false);
    });

    /**
     * Initializes IDs
     */
    this.on('init', function()
    {
        var id = 0;
        slidey.slidesCount = $('.slide').length;

        $('.slide').each(function() {
            $(this).wrap('<div class="slideWrapper" rel="' + id + '" id="slide' + id + '"></div>');	

            $('#slide' + id).prepend('<div class="btn btn-default slideNumber" rel="' + id + '">' + (id+1) + '/' + slidey.slidesCount + '</div>');

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
};

Slidey.prototype = {
    /**
     * Add an event
     */
    on: function(evt, callback)
    {
        if (this.events[evt] == undefined) {
            this.events[evt] = [];
        }

        this.events[evt].push(callback);
    },

    /**
     * Invoke an event
     */
    dispatch: function(evt, argument)
    {
        if (this.events[evt] != undefined) {
            var callbacks = this.events[evt];
            for (k in callbacks) {
                callbacks[k](argument);
            }
        }
    },

    /**
     * Resizes the slide to fit the screen size
     */
    resizeSlides: function(force)
    {
        var width = $('.core').width();
        var height = $(window).height();

        if (this.slideMode)
        {
            $('.slideEnabled').height(height);

            $('.fullSlide').width(width);
            $('.fullSlide').height(height);
            $('.slide.fullSlide').width($('body').width());

            $('.slideNumber').each(function() {
                $(this).css('margin-left', width-$(this).width()+20);
                $(this).css('margin-top', height-$(this).height()-100);
            });

            this.scrollTo(this.currentSlide);
        } else {
            if (force) {
                width = $('.contents').width();
                $('.slideNumber').each(function() {
                    $(this).css('margin-left', width - 20);
                    $(this).css('margin-top', 5);
                });
            }
        }
    },

    /**
     * Go to the given slide & discover
     */
    goTo: function(slide, discover)
    {
        if (this.currentSlide != slide) {
            this.currentSlide = slide;
            this.scrollTo(this.currentSlide);
            this.dispatch('moved');
        }
            
        $('#slide' + this.currentSlide + ' .discover').hide();

        for (i=0; i<discover; i++) {
            $('#discover_' + slide + '_' + i).show();
        }
    },

    /**
     * Update the discovers visibility
     */
    updateDiscovers: function()
    {
        $('.discover').hide();

        for (i=0; i<this.currentSlide-1; i++) {
            $('#slide' + i + ' .discover').show();
        }

        this.updateCurrentDiscover(0);
    },

    /**
     * Update the current slide discover visibility
     *
     * If end is set, starts with the end (all discovered)
     */
    updateCurrentDiscover: function(end)
    {
        this.discoverCount = $('#slide' + this.currentSlide + ' .discover').length;

        if (end) {
            $('#slide' + this.currentSlide + ' .discover').show();
            this.currentDiscover = this.discoverCount;
        } else {
            $('#slide' + this.currentSlide + ' .discover').hide();
            this.currentDiscover = 0;
        }
    },

    /**
     * Scrolls to a given slide
     */
    scrollTo: function(slideId)
    {
        var slidey = this;
        $('html,body').animate({scrollTop:$('#slide' + slideId).offset().top+1}, 300, 0, function() {
            if (slidey.slideMode) {
               $('body').css('background-color', $('#slide' + slideId + ' .slide').css('background-color'));
            }
        });
    },

    /**
     * Scrolls to the current slide
     */
    scrollToCurrentSlide: function()
    {
        this.scrollTo(this.currentSlide);
    },

    /**
     * Go to the slide just before
     */
    precSlide: function()
    {
        if (this.currentSlide > 0) {
            this.currentSlide--;
            this.scrollToCurrentSlide();
            this.updateCurrentDiscover(1);
            this.dispatch('moved');
            return 1;
        }

        return 0;
    },

    /**
     * Go to the slide just after
     */
    nextSlide: function()
    {
        if (this.currentSlide < this.slidesCount-1) {
            this.currentSlide++;
            this.scrollToCurrentSlide();
            this.updateCurrentDiscover(1);
            this.dispatch('moved');
            return 1;
        }

        return 0;
    },

    /**
     * Go to the discover just before
     */
    precDiscover: function()
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
    },

    /**
     * Go to the discover just after
     */
    nextDiscover: function()
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
    },

    /**
     * Go to slide mode
     */
    runSlideMode: function()
    {
        $('.slideModeNormal').removeClass('active');
        $('.slideModeSlide').addClass('active');
        $('.slideOnly').show();
        $('.textOnly').hide();
        this.slideMode = true;
        this.dispatch('changeMode');
        $('.slideWrapper').addClass('slideEnabled');
        this.resizeSlides(true);
        this.updateDiscovers();
        this.dispatch('slideMode');
        this.dispatch('moved');
    },

    /**
     * Go to text mode
     */
    runTextMode: function()
    {
        $('body').css('background-color', 'transparent');
        $('.slideModeSlide').removeClass('active');
        $('.slideModeNormal').addClass('active');
        $('.discover').show();
        $('.slideOnly').hide();
        $('.textOnly').show();
        this.slideMode = false;
        this.dispatch('changeMode');
        $('.slideEnabled').height('auto');
        this.resizeSlides(true);
        $('.slideWrapper').removeClass('slideEnabled');
        this.dispatch('textMode');
    },

    tick: function()
    {
        this.dispatch('tick');
    },

    stopInterval: function()
    {
        if (this.interval) {
            clearInterval(this.interval);
        }
    },

    resetInterval: function()
    {
        var slidey = this;
        this.stopInterval();
        this.interval = setInterval(function() {
            slidey.tick();
        }, 1000);
    },

    init: function() {
        var slidey = this;

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

            //slidey.runSlideMode();
            slidey.runTextMode();

            slidey.dispatch('tick');
        });
        
        $(document).keydown(function(e){
            slidey.dispatch('keypress', e);

            if (!slidey.slideMode || !slidey.controlsEnabled) {
                return;
            }

            switch (e.keyCode) {
                case 33: // Page up
                case 37: // Left
                    e.preventDefault();
                    slidey.precDiscover();
                   break;
                case 34: // Page down
                case 39: // Right
                    e.preventDefault();
                    slidey.nextDiscover();
                    break;
                case 38: // Up
                    e.preventDefault();
                    slidey.precSlide();
                break;
                case 40: // Down
                    e.preventDefault();
                    slidey.nextSlide();
                break;
            }
        });
    }
};
