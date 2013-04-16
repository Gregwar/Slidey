/**
 * Manage the steps of a checklist
 */
function SlideyStepsExtension(slidey)
{
    slidey.on('init', function() {
        var stepId = 0;
        $('.step').each(function() {
            var myStepId = stepId++;
            $(this).wrap('<div class="stepContents stepContents'+myStepId+'"></div>');
            var contents = $('.stepContents'+myStepId);
            contents.wrap('<div class="stepContainer stepContainer'+myStepId+'"></div>');
            var container = $('.stepContainer'+myStepId);

            container.prepend('<div class="stepChecker"><input type="checkbox" /></div>');
            var checkbox = container.find('.stepChecker input[type=checkbox]');

            checkbox.change(function() {
                if ($(this).is(':checked')) {
                    contents.css('opacity', '0.4');
                } else {
                    contents.css('opacity', '1.0');
                }
            });
        });
    });
};
