/**
 * Manages the menu viewer
 */
function SlideyMenuExtension(slidey)
{
    var extension = this;

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
    });
}
