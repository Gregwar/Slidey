/**
 * Manages the menu viewer
 */
function SlideyMenuExtension(slidey)
{
    var extension = this;
    var allSubPassed = false;

    this.shouldBeDisplayed = function()
    {
        return $('h2, h3').length >= 2;
    }

    /**
     * Generates the menu browser
     */
    this.generateMenu = function()
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
    };

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
}
