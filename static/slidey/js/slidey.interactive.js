/**
 * Manages the interactions
 */
function SlideyInteractiveExtension(slidey)
{
    var path = 'interactive.php/';
    var extension = this;

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

        $.getJSON(path + 'current', function(current) {
            if (!extension.ignoreOrder) {
                extension.ignore = true;
                if (!current[0]) {
                    current[0] = 'index.html';
                }

                if (current[0] != extension.currentPage()) {
                    document.location.href = current[0];
                }

                slidey.goTo(current[1], current[2]);
                extension.ignore = false;
            }
        });
    };

    this.logout = function()
    {
        $.getJSON(path + 'logout', function(status) {
            extension.updateStatus(status);
        });
    };

    this.toggleFollow = function()
    {
        if (!extension.follow) {
            $.getJSON(path + 'follow', function(status) {
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
            extension.ignoreOrder = true;
            $.getJSON(path + 'update?page=' + extension.currentPage() + '&slide=' + slidey.currentSlide + '&discover=' + slidey.currentDiscover, function(status) {
                extension.ignoreOrder = false;
                extension.updateStatus(status);
            });
        }
    });

    slidey.on('tick', function()
    {
        if (extension.follow) {
            extension.updateCurrent();
        }
    });

    /**
     * Initializes
     */
    slidey.on('init', function()
    {
        $.getJSON(path + 'getStatus', function(data) {
            extension.updateStatus(data);
        });

        $('.loginWindow form').submit(function() {
            $.getJSON(path + 'login?password=' + $('.loginWindow input').val(), function(status) {
                extension.updateStatus(status);

                if (extension.isAdmin) {
                    alert('Vous êtes maintenant identifiés !');
                } else {
                    alert('Mauvais mot de passe');
                }
            });
            extension.closeWindows();
            return false;
        });

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
                $.getJSON(path + 'logout', function(status) {
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
