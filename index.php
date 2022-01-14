<?php
/**
 * Example
 */

require_once( 'functions.php' );

$config = load_validate_config();

$image = NULL;
$text = NULL;
if (!empty($_GET['seed'])) {
	$seed = $_GET['seed'];
	$imageIndex = crc32($seed) % count($config['memes']);
	$imagesUrls = array_keys($config['memes']);
	$image = $imagesUrls[$imageIndex];

	$textIndex = crc32($seed) % count($config['memes'][$image]);
	$text = $config['memes'][$image][$textIndex];
} else {
	$image = array_rand($config['memes']);
	$text = $config['memes'][$image][array_rand($config['memes'][$image])];
}

$top_text    = $_GET['top'] ?: $text[0];
$bottom_text = $_GET['bottom'] ?: $text[1];

// setup args for image
$imageLocal = dirname(__FILE__) .'/img/' . $image;
$args = array(
	'top_text'    => $top_text,
	'bottom_text' => $bottom_text,
	'filename'    => 'turbokut',
	'font'        => dirname(__FILE__) .'/Anton.ttf',
	'memebase'    => file_exists($imageLocal) ? $imageLocal : $image,
	'textsize'    => 40,
	'textfit'     => true,
	'padding'     => 10,
	'max_width'   => $_GET['max_width']
);

// create and output image
memegen_build_image($args);
