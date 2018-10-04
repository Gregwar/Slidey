/**
 * Manages the poll
 */
function SlideyPollExtension(slidey, interactive)
{
    this.interactive = interactive;
    this.currentPoll = null;
    this.pollOpened = false;
    var extension = this;

    slidey.on('init', function() {
        var pollId = 0;
        $('.poll').each(function() {
            var answerId = 0;
            $(this).find('li').each(function() {
                var html = $(this).html();
                html = '<label class="poll_label"><input rel="'+answerId+'" class="poll_answer" type="radio" name="poll_'+pollId+'" /> '+html+'</label>';
                html += '<div class="poll_result poll_result_'+answerId+'">';
                html += '<div class="progress">';
                html += '<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                $(this).html(html);
                answerId++;
            });
            pollId++;
            $(this).append('<div class="poll_count"></div>');
            $(this).append('<a href="javascript:void(0);" class="poll_show" rel="'+pollId+'">Afficher les résultats</a>');
        });

        $('.poll_show').hide();
        $('.poll_show').click(function() {
            extension.closePoll();
        });

        $('.poll_answer').click(function() {
            if (extension.currentPoll && extension.pollOpened) {
                var vote = $(this).attr('rel');
                $.getJSON(interactive.path + 'votePoll?answer='+vote);
                $('.poll_label').removeClass('text-primary');
                $(this).parent().addClass('text-primary');
            }
        });
    });

    slidey.on('moved', function() {
        var poll = $('#slide' + slidey.currentSlide + ' .poll');
        if (poll.length) {
            extension.currentPoll = poll;
            extension.pollOpened = false;
            extension.startPoll(poll.find('li').length);
            extension.update();
            $('.poll_label').removeClass('text-primary');
        } else {
            extension.currentPoll = null;
        }
    });

    slidey.on('tick', function() {
        extension.update()
    });

    slidey.on('keypress', function(event) {
        if (event.keyCode == 9) {
            event.preventDefault();
            if (extension.currentPoll) {
                extension.currentPoll.find('.poll_show').click();
            }
        }
    });
}

SlideyPollExtension.prototype = {
    startPoll: function(size) {
        var id = this.currentPoll.attr('id');
        if (this.interactive.isAdmin) {
            $.getJSON(this.interactive.path+'startPoll?id='+id+'&size='+size);
        }

        $('.poll_result').hide();
        $('.poll_answer').attr('checked', false);
    },

    closePoll: function() {
        if (this.interactive.isAdmin) {
            $.getJSON(this.interactive.path+'endPoll');
        }
    },

    update: function() {
        if (this.currentPoll) {
            var extension = this;
            $.getJSON(this.interactive.path+'infoPoll', function(data) {
                extension.currentPoll.find('.poll_count').text(data.count+' votant(s)');
                extension.pollOpened = false;
                if (data.id == extension.currentPoll.attr('id')) {
                    if (data.opened) {
                        extension.pollOpened = true;
                        if (extension.interactive.isAdmin) {
                            extension.currentPoll.find('.poll_show').show();
                        }
                        extension.currentPoll.find('input').show();
                        extension.currentPoll.find('.poll_result').hide();
                    } else {
                        extension.currentPoll.find('.poll_show').hide();
                        extension.currentPoll.find('input').hide();

                        for (var k in data.answers) {
                            if (data.answers[k] == 0) {
                                value = 0;
                            } else {
                                var value = Math.round(100*data.answers[k]/parseFloat(data.count));
                            }
                            extension.currentPoll.find('.poll_result_'+k).show();
                            extension.currentPoll.find('.poll_result_'+k+' .progress-bar').attr('aria-valuenow', value);
                            extension.currentPoll.find('.poll_result_'+k+' .progress-bar').css('width', value+'%');
                            extension.currentPoll.find('.poll_result_'+k+' .progress-bar').html(value+'% ('+data.answers[k]+')');
                        }
                    }
                } else {
                    extension.currentPoll.find('.poll_result').hide();
                    extension.currentPoll.find('.poll_show').hide();
                    extension.currentPoll.find('input').hide();
                }
            });
        }
    }
};
