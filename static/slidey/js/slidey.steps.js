/**
 * Manage the steps of a checklist
 */
function SlideyStepsExtension(slidey)
{
    var self = this;
    this.key = null;
    this.checklist = [];
    this.cleanCounter = 0;

    this.updateCheckbox = function(checkbox)
    {
        var stepId = parseInt(checkbox.attr('rel'));
        var contents = $('.stepContents' + stepId);
        if (checkbox.is(':checked')) {
            contents.css('opacity', '0.4');
            self.checklist[stepId] = 1;
        } else {
            contents.css('opacity', '1.0');
            self.checklist[stepId] = 0;
        }

        if (typeof(localStorage) != 'undefined') {
            localStorage.setItem(self.key, self.checklist.join(','));
        }
    };

    slidey.on('init', function() {
        self.key = document.location+'/steps';
        var stepId = 0;
        var numSteps = $('.step').length;

        if (typeof(localStorage) != 'undefined') {
            var value = localStorage.getItem(self.key);
            if (value != null) {
                self.checklist = value.split(',');
                for (k in self.checklist) {
                    self.checklist[k] = parseInt(self.checklist[k]);
                }
            }
        }

        $('.step').each(function() {
            var myStepId = stepId++;
            $(this).wrap('<div class="stepContents stepContents'+myStepId+'"></div>');
            var contents = $('.stepContents'+myStepId);
            contents.wrap('<div class="stepContainer stepContainer'+myStepId+'"></div>');
            var container = $('.stepContainer'+myStepId);

            container.prepend('<div class="stepChecker"><input type="checkbox" rel="'+myStepId+'" /><br />'+(myStepId+1)+'/'+numSteps+'</div>');
            var checkbox = container.find('.stepChecker input[type=checkbox]');

            checkbox.change(function() {
                self.updateCheckbox($(this));
            });

            if (self.checklist.length >= myStepId) {
                if (self.checklist[myStepId]) {
                    checkbox.attr('checked', 'checked');
                    self.updateCheckbox(checkbox);
                }
            } else {
                self.checklist[myStepId] = 0;
            }
        });
    });

    slidey.on('keypress', function(code) {
        if (code == 161) {
            self.cleanCounter++;

            if (self.cleanCounter >= 3) {
                self.checklist = [];
                $('.stepContainer').each(function() {
                    var checkbox = $(this).find('.stepChecker input[type=checkbox]');
                    if (checkbox.is(':checked')) {
                        checkbox.attr('checked', false);
                    }
                    self.updateCheckbox(checkbox);
                });
            }
        } else {
            self.cleanCounter = 0;
        }
    });
};
