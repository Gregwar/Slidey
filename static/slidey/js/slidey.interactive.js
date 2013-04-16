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

    this.updateStatus = function(status)
    {
        var lastFollow = this.follow;
        this.isAdmin = (status.admin != undefined);
        this.follow = (status.follower != undefined);

        if (!lastFollow && this.follow) {
            $('.followMode').addClass('followModeEnabled');
            this.updateCurrent();
        }
        if (lastFollow && !this.follow) {
            $('.followMode').removeClass('followModeEnabled');
        }

        slidey.controlsEnabled = (this.isAdmin || !this.follow);
    };

    /**
     * Closes all the windows
     */
    this.closeWindows = function()
    {
        $('.interactiveWindow').hide();
    };

    this.updateCurrent = function()
    {
        if (!this.follow) {
            return;
        }
            
        if (!slidey.slideMode) {
            slidey.runSlideMode();
        }

        $.getJSON(extension.path + 'current', function(current) {
            if (!extension.ignoreOrder) {
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
    };

    this.sendCurrent = function()
    {
        extension.ignoreOrder = true;
        $.getJSON(extension.path + 'update?page=' + extension.currentPage() + '&slide=' + slidey.currentSlide + '&discover=' + slidey.currentDiscover + '&' + extension.extraCurrent, function(status) {
            extension.ignoreOrder = false;
            extension.updateStatus(status);
        });
    };

    this.logout = function()
    {
        $.getJSON(extension.path + 'logout', function(status) {
            extension.updateStatus(status);
        });
    };

    this.toggleFollow = function()
    {
        if (!extension.follow) {
            $.getJSON(extension.path + 'follow', function(status) {
                extension.updateStatus(status);
            });
        } else {
            extension.logout();
        }
    };

    this.currentPage = function()
    {
        var url = document.URL.split('/');
        var page = url[url.length-1].split('#');

        return page[0];
    };

    this.login = function()
    {
        if (!extension.isAdmin) {
            $('.loginWindow').show();
            $('.loginWindow input').val('');
            $('.loginWindow input').focus();
        } else {
            alert('Vous êtes déjà admin');
        }
    };

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

    /**
     * Initializes
     */
    slidey.on('init', function()
    {
        $.getJSON(extension.path + 'getStatus', function(data) {
            extension.updateStatus(data);
        });

        // Logging
        $('.loginWindow form').submit(function() {
            $.getJSON(extension.path + 'login?password=' + $('.loginWindow input').val(), function(status) {
                extension.updateStatus(status);

                if (extension.isAdmin) {
                    alert('Vous êtes maintenant identifiés !');
                    extension.sendCurrent();
                } else {
                    alert('Mauvais mot de passe');
                }
            });
            extension.closeWindows();
            return false;
        });

        // Following/Unfollowing
        $('.followMode').click(function() {
            extension.toggleFollow();
        });

        $(document).keydown(function(e){
            if ($('input').is(':focus')) {
                return;
            }

            if (e.keyCode == 27) {
                extension.closeWindows();
            }

            if (e.keyCode == 76) {
                extension.login();
                return false;
            }

            if (e.keyCode == 68) {
                $.getJSON(extension.path + 'logout', function(status) {
                    alert('Vous êtes déconnectés');
                    extension.updateStatus(status);
                });
            }

            if (e.keyCode == 70) {
                extension.toggleFollow();
            }
        });
    });
}
