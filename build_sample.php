<?php

include('slidey/slidey.php');

/**
 * Include here your custom libraries
 */

$slidey = new Gregwar\SlideyBuilder;

/**
 * Customizing template
 */

// Setting main title prefix
$slidey->template->mainTitle = 'PHP';

// Adding a CSS stylesheet
$slidey->template->addCss('css/style.css');

// Including a license in the footer
$slidey->template->footer = file_get_contents('license.htm');

/**
 * Adding custom directories
 */

// This will copy the directory "css" to the target directory
$slidey->copyDirectory('css');

// Runs the build to the web directory
$slidey->build();
