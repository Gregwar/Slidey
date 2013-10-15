/**
 * Manages the Images sizes
 */
function SlideyImagesExtension(slidey)
{
    var extension = this;
    
    /**
     * Size of the page images
     */
    this.imageSizes = {};

    /**
     * Ratio
     */
    this.ratio = 1.8;

    /**
     * Initializes
     */
    slidey.on('init', function()
    {
        var imageId = 0;
        $('.contents img').each(function() {
            var myId = $(this).attr('id');

            if (!myId) {
                myId = 'image' + (imageId++);
                $(this).attr('id', myId);
            }

            $(this).load(function() {
                if ($(this).width()) {
                    extension.imageSizes[myId] = [$(this).width(), $(this).height()];
                    extension.updateImage(myId);
                };
            });
        });
    });

    /**
     * Go to slide mode
     */
    slidey.on('slideMode', function()
    {
        extension.updateImages();
    });

    /**
     * Go to text mode
     */
    slidey.on('textMode', function()
    {
        extension.updateImages();
    });
};

SlideyImagesExtension.prototype = {
    /**
     * Updates all image sizes
     */
    updateImages: function()
    {
        for (id in this.imageSizes) {
            this.updateImage(id);
        }
    },

    /**
     * Updates a specific image size
     */
    updateImage: function(imageId)
    {
        if (!imageId in this.imageSizes) {
            return;
        }

        var image = $('#' + imageId);
        var size = this.imageSizes[imageId];

        if (slidey.slideMode) {
            image.width(size[0]);
            image.height(size[1]);
        } else {
            image.width(size[0]/this.ratio);
            image.height(size[1]/this.ratio);
        }
    },
};
