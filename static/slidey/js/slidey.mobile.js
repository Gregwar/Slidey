function SlideyMobileExtension(slidey)
{
    var extension = this;

    /**
     * Test if the client is a mobile
     */
    this.isMobile = function()
    {
        var ua = navigator.userAgent.toLowerCase();
        return ua.indexOf('mobile') > 1;
    };

    slidey.on('init', function()
    {
        if (extension.isMobile()) {
            $('.showMobile').show();

            $('.showMobile').click(function() {
                console.log('!!!!');
                $('.mobileControls').toggle();
            });
        }

        $('.mobileControls .left').click(function() {
            if (slidey.controlsEnabled) {
                slidey.precDiscover();
            }
        });
        $('.mobileControls .right').click(function() {
            if (slidey.controlsEnabled) {
                slidey.nextDiscover();
            }
        });
        $('.mobileControls .up').click(function() {
            if (slidey.controlsEnabled) {
                slidey.precSlide();
            }
        });
        $('.mobileControls .down').click(function() {
            if (slidey.controlsEnabled) {
                slidey.nextSlide();
            }
        });
        $('.mobileControls .login').click(function() {
            slidey.dispatch('login');
        });
    });
}
