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
     * Size of the page images
     */
    this.imageSizes = {};

    /**
     * Generates the menu browser
     */
    this.generateMenu = function()
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

            if (!slidey.slideMode) {
                $('html,body').animate({scrollTop:$('#' + titleId).offset().top}, 300, 0);
            } else {
                slidey.currentSlide = $('#' + titleId).closest('.slideWrapper').attr('rel');
                slidey.scrollTo(slidey.currentSlide);
                slidey.updateDiscovers(0);
            }
        });
    };

    /**
     * Updates the position in the browser
     */
    this.updateMenuPosition = function()
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
    };

    /**
     * Initialize spoilers
     */
    this.initSpoilers = function()
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
    };

    /**
     * Initializes IDs
     */
    this.initIds = function()
    {
        var id = 0;
        this.slidesCount = $('.slide').length;

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

        var imageId = 0;
        $('.contents img').each(function() {
            var myId = $(this).attr('id');

            if (!myId) {
                myId = 'image' + (imageId++);
                $(this).attr('id', myId);
            }

            $(this).load(function() {
                slidey.imageSizes[myId] = [$(this).width(), $(this).height()];
                slidey.updateImage(myId);
            });
        });
    };

    /**
     * Updates all image sizes
     */
    this.updateImages = function()
    {
        for (id in this.imageSizes) {
            this.updateImage(id);
        }
    };

    /**
     * Updates a specific image size
     */
    this.updateImage = function(imageId)
    {
        var image = $('#' + imageId);
        var size = this.imageSizes[imageId];

        if (this.slideMode) {
            image.width(size[0]);
            image.height(size[1]);
        } else {
            image.width(size[0]/2.0);
            image.height(size[1]/2.0);
        }
    };

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
        this.generateMenu();
        this.updateImages();
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
        this.generateMenu();
        this.updateImages();
    };

    /**
     * Called on each tick
     */
    this.tick = function()
    {
        slidey.resizeSlides();
        slidey.updateMenuPosition();
    };

    this.init = function() {
        $(document).ready(function()
        {
            slidey.initSpoilers();
            slidey.initIds();

            setInterval(slidey.tick, 500);
            
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
            });

            slidey.runSlideMode();
            slidey.runTextMode();
            slidey.resizeSlides();
            slidey.updateMenuPosition();
        });

        $(document).keydown(function(e){
            if (!slidey.slideMode) 
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
slidey.init();
