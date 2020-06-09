/**
 * Manages the interactions
 */
function SlideyInteractiveExtension(slidey)
{
    var extension = this;

    this.path = 'interactive.php/';
    this.extraCurrent = false;
    this.isAdmin = false;
    this.follow = false;
    this.ignore = false;
    this.ignoreOrder = false;

    slidey.on('login', function()
    {
        if (extension.isAdmin) {
            extension.logout();
        } else {
            extension.login();
        }
    });

    slidey.on('moved', function()
    {
        if (extension.isAdmin && !extension.ignore) {
            extension.sendCurrent();
        }
    });

    slidey.on('tick', function()
    {
        // Update the position
        if (extension.follow) {
            extension.updateCurrent();
        }
    });

    slidey.on('stopShowClicked', function()
    {
        if (!extension.isAdmin) {
            // Stop following as well (else we will switch back to slide mode)
            extension.logout();   
        }
    });

    /**
     * Initializes
     */
    slidey.on('init', function()
    {
        $('script').each(function() {
            var src = $(this).attr('src');
            var name = 'slidey.interactive.js';

            if (typeof(src) != 'undefined' && src && src.substr(-name.length) == name) {
                var root = src.substr(0, src.length-name.length);
                extension.path = root+'../../interactive.php/';
            }
        });

        $.getJSON(extension.path + 'getStatus', function(data) {
            extension.updateStatus(data);
        });

        // Logging
        $('#loginWindow form').submit(function() {
            $('#loginWindow').modal('hide');
            $.getJSON(extension.path + 'login?password=' + $('#loginWindow input').val(), function(status) {
                extension.updateStatus(status);

                if (extension.isAdmin) {
                    alert('Vous êtes maintenant identifiés !');
                    extension.sendCurrent();
                } else {
                    $('input').blur();
                    alert('Mauvais mot de passe');
                }
            });
            extension.closeWindows();
            return false;
        });

        // Following/Unfollowing
        $('.followMode').show();
        $('.followMode').click(function() {
            extension.toggleFollow();
            return true;
        });

        $(document).keydown(function(e){
            if ($('input').is(':focus')) {
                return;
            }

            if (e.key == 'Escape') {
                extension.closeWindows();
            }

            if (e.key == '$') {
                extension.login();
                return false;
            }

            if (e.key == '*') {
                $.getJSON(extension.path + 'logout', function(status) {
                    alert('Vous êtes déconnectés');
                    extension.updateStatus(status);
                });
            }
        });
    });
};

SlideyInteractiveExtension.prototype = {
    updateStatus: function(status)
    {
        var lastFollow = this.follow;
        this.isAdmin = (status.admin != undefined);
        this.follow = (status.follower != undefined);

        if (!lastFollow && this.follow) {
            $('.followMode').addClass('active');
            this.updateCurrent();
        }
        if (lastFollow && !this.follow) {
            $('.followMode').removeClass('active');
        }

        slidey.controlsEnabled = (this.isAdmin || !this.follow);
    },

    /**
     * Closes all the windows
     */
    closeWindows: function()
    {
        $('.interactiveWindow').hide();
    },

    updateCurrent: function()
    {
        if (!this.follow) {
            return;
        }
            
        if (!slidey.slideMode) {
            slidey.runSlideMode();
        }

        var extension = this;
        $.getJSON(this.path + 'current', function(current) {
            if (!extension.ignoreOrder && current.page != "") {
                extension.ignore = true;
                if (!current.page) {
                    current.page = 'index.html';
                }

                if (current.page != extension.currentPage()) {
                    document.location.href = current.page;
                }

                slidey.goTo(current.slide, current.discover);
                extension.ignore = false;

                slidey.dispatch('updateCurrent', current);
            }
        });
    },

    sendCurrent: function()
    {
        var extension = this;
        extension.ignoreOrder = true;
        $.getJSON(extension.path + 'update?page=' + extension.currentPage() + '&slide=' + slidey.currentSlide + '&discover=' + slidey.currentDiscover + '&' + extension.extraCurrent, function(status) {
            extension.ignoreOrder = false;
            extension.updateStatus(status);
        });
    },

    logout: function()
    {
        var extension = this;
        $.getJSON(extension.path + 'logout', function(status) {
            extension.updateStatus(status);
        });
    },

    toggleFollow: function()
    {
        var extension = this;
        if (!extension.follow) {
            $.getJSON(extension.path + 'follow', function(status) {
                extension.updateStatus(status);
            });
        } else {
            extension.logout();
        }
    },

    currentPage: function()
    {
        var url = document.URL;
        var page = url.split('#');

        return page[0];
    },

    login: function()
    {
        if (!this.isAdmin) {
            $('#loginWindow').on('shown.bs.modal', function() {
                $('#loginWindow input').val('');
                $('#loginWindow input').focus();
            });
            $('#loginWindow').modal('show');
        } else {
            alert('Vous êtes déjà admin');
        }
    }
};
