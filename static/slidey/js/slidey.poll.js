/**
 * Polls manager
 */
function SlideyPollExtension(slidey, interactive)
{
    var extension = this;

    this.showResults = false;
    this.pollNumber = null;
    this.currentPoll = null;

    /**
     * Show/Hide the poll
     */
    this.poll = function()
    {
        if (this.currentPoll) {
            $('.poll').hide();
            this.currentPoll.find('.admin').remove();
            this.currentPoll = null;
            this.pollNumber = null;
        } else {
            if ($('#slide' + slidey.currentSlide + ' .poll').size()) {
                extension.showResults = false;
                $('.poll .result').hide();
                var poll = $('#slide' + slidey.currentSlide + ' .poll');
                this.pollNumber = (new Date()).getTime();
                this.currentPoll = poll;
                this.currentPoll.append('<div class="admin"><a href="javascript:void(0)" class="results">Résultats</a></div>');
                this.currentPoll.find('.results').click(function() {
                    extension.showResults = !extension.showResults;

                    if (extension.showResults) {
                        $('.poll .result').fadeIn();
                    } else {
                        $('.poll .result').fadeOut();
                    }
                });
                poll.show();
            }
        }

        interactive.extraCurrent = 'poll='+this.pollNumber;

        interactive.sendCurrent();
    };

    slidey.on('tick', function() {
        // Update the Poll
        if (extension.currentPoll && interactive.isAdmin) {
            $.getJSON(interactive.path + '/getStats', function(data) {
                extension.currentPoll.find('.extra').show();

                var total = 0;
                for (k in data) {
                    total += data[k];
                }

                extension.currentPoll.find('.extra').text(total + ' votant(s)');

                $('.poll .result').text('');

                for (k in data) {
                    html = '' + Math.round(data[k]*100.0/total)+'% ('+data[k]+') ';
                    html+= '<div class="bar" style="width:'+Math.round(data[k]*300.0/total)+'px"></div>';
                    extension.currentPoll.find('.option_' + k + ' .result').html(html);
                }
            });
        }
    });

    slidey.on('updateCurrent', function(current)
    {
        // Updating poll viewing
        if (current.poll) {
            $('.poll').hide();
            $('#slide' + current.slide + ' .poll').show();

            if (current.status.lastPoll != current.poll) {
                $('.poll .extra').hide();
                $('.poll .option').show();
            } else {
                $('.poll .option').hide();
                $('.poll .extra').text('Vote pris en compte');
                $('.poll .extra').show();
            }
        } else {
            $('.poll').hide();
        }
    });
        
    slidey.on('init', function()
    {
        $('.poll').each(function() {
            $(this).addClass('interactiveWindow');
        });

        // Assigning IDs to poll & options
        var pollId = 0;
        $('.poll').each(function() {
            $(this).attr('id', 'poll'+pollId);
            $(this).attr('rel', pollId);
            pollId++;

            var optionId = 0;
            $(this).find('.option').each(function() {
                $(this).addClass('option_' + optionId);
                $(this).attr('rel', optionId);
                optionId++;
            });

            $('.poll').append('<div class="extra"></div>');
            $('.poll .option').append('<span class="result"></span>');
        });

        // Voting for an option
        $('.poll .option').click(function() {
            $.getJSON(interactive.path + '/vote?option=' + $(this).attr('rel'));
        });

        $(document).keydown(function(e){
            if (e.keyCode == 80 && slidey.slideMode && interactive.isAdmin) {
                extension.poll();
            }
        });
    });
}
