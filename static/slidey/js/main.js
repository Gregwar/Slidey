var slidey = new Slidey();
new SlideyMenuExtension(slidey);
new SlideyImagesExtension(slidey);
new SlideySpoilersExtension(slidey);
new SlideyMobileExtension(slidey);
new SlideyStepsExtension(slidey);
new SlideyPermalinkExtension(slidey);
if (typeof(SlideyInteractiveExtension) != 'undefined') {
    interactive = new SlideyInteractiveExtension(slidey);
}
slidey.init();