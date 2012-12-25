/**
 * Manages the Images sizes
 */
function SlideyImagesExtension(slidey)
{
    /**
     * Size of the page images
     */
    this.imageSizes = {};

    var extension = this;

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
                extension.imageSizes[myId] = [$(this).width(), $(this).height()];
                extension.updateImage(myId);
            });
        });
    });

    /**
     * Updates all image sizes
     */
    this.updateImages = function()
    {
        for (id in this.imageSizes) {
            this.updateImage(id);
        }
    };

    /**
     * Updates a specific image size
     */
    this.updateImage = function(imageId)
    {
        var image = $('#' + imageId);
        var size = this.imageSizes[imageId];

        if (slidey.slideMode) {
            image.width(size[0]);
            image.height(size[1]);
        } else {
            image.width(size[0]/2.0);
            image.height(size[1]/2.0);
        }
    };

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
