/**
 * Adds permalinks ("#") on titles
 */
function SlideyPermalinkExtension(slidey)
{
    slidey.on('init', function() {
        $('.contents h1, .contents h2, .contents h3').each(function() {
            var prev = $(this).prev();

            if (prev.length && prev[0].tagName.toLowerCase() == 'a' && prev.attr('id')) {
                var html = '<a class="permalink" href="#'+prev.attr('id')+'">#</a> <span class="titleText">'+$(this).html()+'</span>';
                $(this).html(html);
            }
        });
    });
};
