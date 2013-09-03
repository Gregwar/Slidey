/**
 * Manages the menu viewer
 */
function SlideyMenuExtension(slidey)
{
    var extension = this;
    var currentTitle = '';
    var allSubPassed = false;

    /**
     * Generates the menu browser
     */
    this.generateMenu = function()
    {
        var menuElements = '';

        if ($('h1, h2, h3').length < 2) {
            return;
        }

        var lastTitle = '';
        $('h1, h2, h3').each(function() {
            var tagName = $(this)[0].tagName.toLowerCase();
            if (tagName == 'h2') {
                lastTitle = $(this).attr('id');
            }
            if ($(this).is(':visible')) {
                if (tagName != 'h3' || lastTitle == currentTitle) {
                    html = '<div id="menu_for_'+$(this).attr('id')+'" rel="'+$(this).attr('id')+'" class="menuItem menu'+tagName+'">'+$(this).html()+'</div>';
                    menuElements += html;
                }
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
        if ($('h1, h2, h3').length < 2) {
            $('.menu').hide();
        }

        var scrollTop = $('html').scrollTop();
        if (!scrollTop) {
            scrollTop = $('body').scrollTop();
        }

        var lastTitle = currentTitle;
        if ($('.menuh2').length) {
            if ($('.menuh2.menuPassed').length) {
                // Some titles are not visibles
                currentTitle = $('.menuh2.menuPassed').last().attr('rel');
            } else {
                // All the titles are visible
                currentTitle = $('.menuh2:not(.menuPassed)').first().attr('rel');
            }

            if (lastTitle != currentTitle) {
                extension.generateMenu();
            }
        }

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
    });
}
