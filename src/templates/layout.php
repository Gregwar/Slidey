<!DOCTYPE html>
<html>
    <head>
	<meta charset="utf-8" />
	<title><?php echo $slidey->title(); ?></title>
        <link type="text/css" media="screen" rel="stylesheet" href="slidey/css/style.css" />
        <link rel="shortcut icon" href="favicon.ico" />
	<script type="text/javascript" src="slidey/js/jquery.js"></script>
        <script type="text/javascript" src="slidey/js/slidey.js"></script>
	<?php echo $slidey->header(); ?>
    </head>
    <body>
	<div class="slideMode">
	    <a href="javascript:void(0)" class="slideModeNormal slideModeEnabled"></a>
	    <a href="javascript:void(0)" class="slideModeSlide"></a>
	    <a href="index.html" class="goHome"></a>
	</div>

	<div class="menu">
	</div>

	<div class="contents">
	    <div class="slide middleSlide">
		    <?php echo $slidey->pageTitle(); ?>
	    </div>

	   <?php echo $slidey->contents(); ?>
	</div>
	<?php echo $slidey->browser(); ?>
	<?php echo $slidey->footer(); ?>
    </body>
</html>
