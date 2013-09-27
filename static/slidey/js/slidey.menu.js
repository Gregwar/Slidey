/**
 * Manages the menu viewer
 */
function SlideyMenuExtension(slidey)
{
    var extension = this;

    slidey.on('slideMode', function() {
        extension.generateMenu();
    });
    
    slidey.on('textMode', function() {
        extension.generateMenu();
    });
    
    /**
     * Updates the position in the browser
     */
    slidey.on('tick', function() 
    {
        if (!extension.shouldBeDisplayed()) {
            $('.menu').hide();
        }

        var scrollTop = $('html').scrollTop();
        if (!scrollTop) {
            scrollTop = $('body').scrollTop();
        }

        var currentH2 = '';
        var hasH3 = null;
        $('h1, h2, h3').each(function() {
            if ($(this).is(':visible')) {
                var tagName = $(this)[0].tagName.toLowerCase();
                var menuElement = $('#menu_for_' + $(this).attr('id'));

                if (tagName == 'h2') {
                    currentH2 = $(this).attr('id');
                }

                if ($(this).offset().top < scrollTop) {
                    if (!menuElement.hasClass('menuPassed')) {
                        menuElement.addClass('menuPassed');
                    }
                } else {
                    if (menuElement.hasClass('menuPassed')) {
                        menuElement.removeClass('menuPassed');
                    }
                    if (tagName == 'h3' && hasH3 == null) {
                        hasH3 = currentH2;
                    }
                }
            }
        });

        $('.menuh3').hide();
        if (hasH3) {
            $('.menuh2of'+hasH3).show();
        }
    });
    
    slidey.on('changeMode', function() {
        extension.moveMenu();
    });

    slidey.on('init', function() {
        if (!extension.shouldBeDisplayed()) {
            $('.menu').hide();
        }
    });
};

SlideyMenuExtension.prototype = {

    shouldBeDisplayed: function()
    {
        return $('h2, h3').length >= 2;
    },

    /**
     * Move the menu in slide mode
     */
    moveMenu: function()
    {
        if (slidey.slideMode || !this.shouldBeDisplayed()) {
            $('.contents').css('margin-left', 0);
            $('.menu').css('margin-left', -250);
            $('.contents').css('width', $('.core').width());
        } else {
            $('.contents').css('margin-left', 200);
            $('.menu').css('margin-left', -30);
            $('.contents').css('width', 750);
        }
    },

    /**
     * Generates the menu browser
     */
    generateMenu: function()
    {
        if (!this.shouldBeDisplayed()) {
            return;
        }

        var menuElements = '';

        var currentH2 = '';
        $('h1, h2, h3').each(function() {
            var tagName = $(this)[0].tagName.toLowerCase();
            if (tagName == 'h2') {
                currentH2 = $(this).attr('id');
            }
            if ($(this).is(':visible')) {
                var titleText = $(this).find('.titleText');
                var contents;
                if (titleText.length) {
                    contents = titleText.html();
                } else {
                    contents = $(this).html();
                }
                html = '<div id="menu_for_'+$(this).attr('id')+'" rel="'+$(this).attr('id')+'" class="menuItem menuh2of'+currentH2+' menu'+tagName+'">'+contents+'</div>';
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
                slidey.dispatch('moved');
            }
        });
    }
};
