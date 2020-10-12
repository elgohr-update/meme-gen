<?php
/**
 * Example
 */

require_once( 'functions.php' );

$config = load_validate_config();

$image = array_rand($config['memes']);
$text = $config['memes'][$image][array_rand($config['memes'][$image])];

$top_text    = $text[0];
$bottom_text = $text[1];

// setup args for image
$imageLocal = dirname(__FILE__) .'/img/' . $image;
$args = array(
	'top_text'    => $text[0],
	'bottom_text' => $text[1],
	'filename'    => 'turbokut',
	'font'        => dirname(__FILE__) .'/Anton.ttf',
	'memebase'    => file_exists($imageLocal) ? $imageLocal : $image,
	'textsize'    => 40,
	'textfit'     => true,
	'padding'     => 10,
);

// create and output image
memegen_build_image($args);
