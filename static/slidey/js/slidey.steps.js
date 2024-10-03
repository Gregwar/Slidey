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

        var stepGroup = 0;
        var stepGroups = {};
        var stepGroupSizes = {};

        $('.step').each(function() {
            if ($(this).data('params') == 'reset' && (stepGroup in stepGroupSizes)) {
                stepGroup += 1;
            }
            if (!(stepGroup in stepGroupSizes)) {
                stepGroupSizes[stepGroup] = 0;
            }
            stepGroupSizes[stepGroup] += 1;

            var myStepId = stepId++;
            stepGroups[myStepId] = [stepGroupSizes[stepGroup], stepGroup];
        });
        stepId = 0;

        $('.step').each(function() {
            var myStepId = stepId++;
            var group = stepGroups[myStepId];
            var groupStep = group[0];
            var groupSize = stepGroupSizes[group[1]];

            $(this).wrap('<div class="stepContents stepContents'+myStepId+'"></div>');
            var contents = $('.stepContents'+myStepId);
            contents.wrap('<div class="stepContainer stepContainer'+myStepId+'"></div>');
            var container = $('.stepContainer'+myStepId);

            container.prepend('<div class="stepChecker"><input type="checkbox" rel="'+myStepId+'" /><br />'+groupStep+'/'+groupSize+'</div>');
            var checkbox = container.find('.stepChecker input[type=checkbox]');groupSize

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

    slidey.on('keypress', function(event) {
        if (event.keyCode == 161) {
            event.preventDefault();
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
