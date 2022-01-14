<?php
/**
 * Memegen
 *
 * Generate meme-style image
 *
 * memegen_build_image()
 * memegen_font_size_guess()
 * memegen_imagettfstroketext()
 * memegen_sanitize()
 *
 */

/**
 * Build the image based on array of args
 *
 * @param array $args
 *     top_text    string Text for top of image
 *     bottom_text string Text for bottom of image
 *     filename    string Filename to save image as
 *     font        string Path to font file
 *     memebase    string Path to base image
 *     textsize    int    Font size
 *     textfit     bool   Fit text to image
 *     linespacing int    Line spacing // Pending
 *     padding     int    Padding between text and image
 * @return void
 */
function memegen_build_image( $args = array() ) {

	list( $width, $height ) = getimagesize( $args['memebase'] );

	$args['textsize'] = empty( $args['textsize'] ) ? round( $height/10 ) : $args['textsize'];

	extract( $args );

	// alright, lets make an image
	if (ends_with(strtolower($args['memebase']), '.png')) {
		$im = imagecreatefrompng($args['memebase']);
	} else {
		$im = imagecreatefromjpeg($args['memebase']);
	}

	// make base image transparent
	$black = imagecolorallocate( $im, 0, 0, 0 );
	imagecolortransparent( $im, $black );

	$textcolor = imagecolorallocate( $im, 255, 255, 255 );

	$angle = 0;

	$top_text = strtoupper( trim( $args['top_text'] ) );
	$bottom_text = strtoupper( trim( $args['bottom_text'] ) );

	$fit = isset( $textfit ) ? $textfit : true;

	// top layer text
	extract( memegen_font_size_guess( $textsize, ($width-$padding*2), $font, $top_text, $fit ) );
	$from_side = ($width - $box_width)/2;
	$from_top = $box_height + $padding;
	// imagettftext( $im, $textsize, $angle, $from_side, $from_top, $textcolor, $font, $top_text );
	memegen_imagettfstroketext( $im, $fontsize, $angle, $from_side, $from_top, $textcolor, $black, $font, $top_text, 1 );

	// bottom layer text
	extract( memegen_font_size_guess( $textsize, ($width-$padding*2), $font, $bottom_text, $fit ) );
	$from_side = ($width - $box_width)/2;
	$from_top = $height - $padding;
	// imagettftext( $im, $textsize, $angle, $from_side, $from_top, $textcolor, $font, $bottom_text );
	memegen_imagettfstroketext( $im, $fontsize, $angle, $from_side, $from_top, $textcolor, $black, $font, $bottom_text, 1 );

	$basename = basename( $args['memebase'], '.jpg' );

	// resize
	if (array_key_exists('max_width', $args) && !empty($args['max_width'])) {
		$mw = $args['max_width'];
		$w = imagesx($im);
		$h = imagesy($im);

		if ($w > $mw) {
			$nw = $mw;
			$nh = $h / $w * $nw;

			$im = imagescale($im, $nw, $nh);
		}
	}

	// output
	header('Content-Type: image/jpeg');
	header('Content-Disposition: filename="'. $basename .'-'. $filename .'.jpg"');
	imagejpeg( $im );
	imagedestroy( $im );

}

/**
 * Font size guess
 *
 * Check if font box is too big for image and reduce recursively as needed till it does
 *
 * @param int $fontsize
 * @param int $imwidth
 * @param string $font TTF
 * @param string $text
 * @param bool $fit Try and fit text to image
 * @return array Font size, font box width, font box height
 */
function memegen_font_size_guess( $fontsize, $imwidth, $font, $text, $fit ) {

	$angle = 0;

	$_box = imageftbbox( $fontsize, $angle, $font, $text );
	$box_width = $_box[4] - $_box[6];
	$box_height = $_box[3] - $_box[5];

	if ( $box_width > $imwidth && $fit ) {

		// $sub = round( ( $box_width - $imwidth) * .08, 0, PHP_ROUND_HALF_DOWN );
		// if ( $sub < 1 ) $sub = 1;
		$sub = 1;
		$fontsize = $fontsize - $sub;

		return memegen_font_size_guess( $fontsize, $imwidth, $font, $text, $fit );

	}

	return compact( 'fontsize', 'box_width', 'box_height' );

}

/**
 * Writes the given text with a border into the image using TrueType fonts.
 * http://www.johnciacia.com/2010/01/04/using-php-and-gd-to-add-border-to-text/
 * @author John Ciacia
 * @param image An image resource
 * @param size The font size
 * @param angle The angle in degrees to rotate the text
 * @param x Upper left corner of the text
 * @param y Lower left corner of the text
 * @param textcolor This is the color of the main text
 * @param strokecolor This is the color of the text border
 * @param fontfile The path to the TrueType font you wish to use
 * @param text The text string in UTF-8 encoding
 * @param px Number of pixels the text border will be
 * @see http://us.php.net/manual/en/function.imagettftext.php
 */
function memegen_imagettfstroketext(&$image, $size, $angle, $x, $y, &$textcolor, &$strokecolor, $fontfile, $text, $px) {

	for($c1 = ($x-abs($px)); $c1 <= ($x+abs($px)); $c1++)
		for($c2 = ($y-abs($px)); $c2 <= ($y+abs($px)); $c2++)
			$bg = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);

	return imagettftext($image, $size, $angle, $x, $y, $textcolor, $fontfile, $text);
}

/**
 * Sanitize
 *
 * Replace non-alphanumeric characters with hyphens
 * Reduce any multihyphens down to one
 *
 * @param string $input
 * @return string $input
 */
function memegen_sanitize( $input ) {
	$input = preg_replace( '/[^a-zA-Z0-9-_]/', '-', $input );
	$input = preg_replace( '/--*/', '-', $input );
	return $input;
}


function dd(...$things) {
    die(var_dump($things));
}


function load_validate_config() {
	$env = getenv('CONFIG');
	if (! empty($env)) {
		$config = json_decode(base64_decode($env), true);
	} else {
		$config = json_decode(file_get_contents('config.json'), true);
	}
	json_last_error() == JSON_ERROR_NONE or die('Failed to load config'); // PHP sucks.

	$images = array_filter(scandir('img/'), function ($file) {
		if (substr($file, 0, 1) === '.') {
			return false;
		}

		return true;
	});
	foreach ($config['memes'] as $filename => $values) {
		if (starts_with($filename, 'http://') || starts_with($filename, 'https://')) {
			continue;
		}

		in_array( $filename, $images) or die('Missing image: ' . $filename);
	}

	return $config;
}

function starts_with($haystack, $needle) {
	return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
}

function ends_with($haystack, $needle) {
	return substr_compare($haystack, $needle, -strlen($needle)) === 0;
}
