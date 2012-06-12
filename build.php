<?php

@include(__DIR__.'/../head.php');

include('slidey.php');

$slidey = new Gregwar\SlideyCacheBuilder();
$slidey->run(__DIR__.'/../');
