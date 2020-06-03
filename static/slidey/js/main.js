var slidey = new Slidey();
new SlideyMenuExtension(slidey);
new SlideyImagesExtension(slidey);
new SlideySpoilersExtension(slidey);
// new SlideyMobileExtension(slidey);
new SlideyStepsExtension(slidey);
new SlideyPermalinkExtension(slidey);
if (typeof(SlideyInteractiveExtension) != 'undefined') {
    var interactive = new SlideyInteractiveExtension(slidey);
    if (typeof(SlideyPollExtension) != 'undefined') {
        new SlideyPollExtension(slidey, interactive);
    }
}
slidey.init();
