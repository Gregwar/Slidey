/**
 * Manages the spoilers (show/hide text)
 */
function SlideySpoilersExtension(slidey)
{
    /**
     * Initialize spoilers
     */
    slidey.on('init', function()
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
    });
}
