Slidey
======

This library can be use to generate hybrid slide & documentation contents.

Installation
------------

Slidey can be installed by cloning this repository recursively

```
git clone --recursive git@github.com:Gregwar/Slidey.git slidey
```

Usage
-----

To use slidey, you can create a new project by simply cloning it and create a `build.php`
file. You can have a look at the example `build_sample.php` :

```php
<?php

include('slidey/slidey.php');

/**
 * Include here your custom libraries
 */

$slidey = new Gregwar\Slidey\SlideyStandard;

/**
 * Customizing template
 */

// Setting main title prefix
$slidey->template->mainTitle = 'My show';

// Adding a CSS stylesheet
$slidey->template->addCss('css/style.css');

// Including a license in the footer
$slidey->template->footer = file_get_contents('license.htm');

/**
 * Adding custom directories
 */

// This will copy the directory "css" to the target directory
$slidey->copy('css');

// Runs the build to the web directory
$slidey->build();

```

This will crawl the `pages/` directory looking for chapters and build your slidey website, 
you can have a look at https://github.com/Gregwar/PHP to see a full example of pages.

Example
-------

This project is used to create a french PHP lecture :

http://gregwar.com/php/

License
-------

Slidey is under MIT license, for more information, please refer to the `LICENSE` file
