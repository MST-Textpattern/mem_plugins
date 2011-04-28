<?php

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
# $plugin['name'] = 'abc_plugin';

$plugin['version'] = '0.1';
$plugin['author'] = 'mem_panorama';
$plugin['author_uri'] = 'http://manfre.net/';
$plugin['description'] = 'Adds easy access tag for the PTViewer applet v3.1.2';

@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h1. mem_panorama

Author:	Michael Manfre
URL:	"http://manfre.net":http://manfre.net

p. Adds an easy access tag for PTViewer 3.1.2. PTViewer is an applet use to display panoramic images in an interactive way. 

p. PTViewer was created and is maintained by Helmut Dersch. For more information about PTViewer, please visit "http://webuser.fh-furtwangen.de/~dersch/":http://webuser.fh-furtwangen.de/~dersch/.

h3. Usage

p. Place the tag ==<mem_panorama %attrs% />== in an entry, page or form. Replace %attrs% with the attributes from the completed list below.

p. ==<mem_panorama file="/images/panorama_1.jpg" pan="-30" tilt="5" />==

h3. COMPLETED

p. All features added after v2.5 - "http://webuser.fh-furtwangen.de/~dersch/hdr/Readme.txt":http://webuser.fh-furtwangen.de/~dersch/hdr/Readme.txt


p. Basic features from < v2.5
# auto - autorotation angle (-360...360, default 0) pan angle is incremented by that amount for each frame. Specify degrees, fractional values allowed.
# barcolor - the color of the progress bar (default dark gray).
# bar_x - the x-coordinate of the upper left point of the progress bar (default width/4)
# bar_y - the y-coordinate of the upper left point of the progress bar (default height*3/4)
# bar_height - the height of the progress bar (default 10 pixels).
# bar_width - the width of the progress bar (default width/2).
# bgcolor - Hexadecimal integer specifying color of background
# file - The filename of the panoramic image. Alternatively, a panorama from a list specified using the pano0/1/2 tag can be loaded using the filename 'ptviewer:Number'  . Example: 'ptviewer:3' loads the panorama with list number 3.
# fov - initial horizontal field of view (12...165, default 70)
# fovmax - maximum field of view (default 165)
# fovmin - minimum field of view (default 12)
# maxarray - the maximum size for linear arrays on this machine (default 524288 for Netscape). See the notes about large images.
# pan - initial pan angle (-180...180, default 0)
# panmax - maximum pan angle ( 0...180, default none)
# panmin - minimum pan angle (0..-180, default none)
# pwidth, pheight - Width and height of Panoramic image in pixel. By default, these parameter equal the width and height of the image specified in 'file'. Required only if regions of interest (ROI) are inserted later. See chapter below.
# quality - determines which of the two built-in pixel-interpolators are used for rendering the images: either the nearest-neighbor (nn, fast, low image quality) or the bilinear (bi, slow, high image quality) interpolator. The available options are 0 - always nn; 1 - nn for panning and autopanning, bi for stills; 2 - nn for panning, bi for stills and autopanning; 3 - always bi. (default 3).
# tilt - initial tilt  angle (-90...90, default 0)
# tiltmax - maximum tilt angle (90 to 0, default  90 for spherical, or  vertical field of view for cylindrical panos)
# tiltmin - minimum tilt angle (-90 to 0, default -90 for spherical, or -vertical field of view for cylindrical panos)
# view_x - x-coordinate of upper left corner of the panorama viewer. (default 0).
# view_y - y-coordinate of upper left corner of the panorama viewer. (default 0).
# wait - Name of image (gif or jpeg) to be displayed during download. Specify path relative to html-document. This image is displayed centered in the applet window
# waittime - Minimum time to display the wait image, in Milliseconds (default 0). This is useful if an animated gif-image is used, and the animation should finish before display of the panorama.
# view_height - the height of the panorama viewer in pixels. Defaults o height of applet window.
# view_width - the width of the panorama viewer in pixels. Defaults to width of applet window.



h3. PENDING

p. These features may never be included.

# mousehs - Name of a user supplied javascript function which gets called everytime the mouse enters or leaves a hotspot. See example and notes on scripting below.
# getview - Name of a user supplied javascript function which gets called everytime the view (pan, tilt or field of view) changes. See example and notes on scripting below.
# frame - An image to be displayed in front of the panorama window. This should be a gif or jpeg image. It will be inserted into the applet window aligned to the lower right edge. See the control buttons in Controls.html for an example.
# shotspot0,1,2,...- Description of static hotspots, see next chapter
# pano0,1,2,3 -List of panorama images for built-in controls. See next chapters for details.
# hsimage - Name of hotspotimage describing masks for all hotspots. This image must have identical dimensions as the panoramic image. See chapter on hotspots below.
# hotspot0,1,2,... - Alternative way to set hotspots, see next chapter
# sound0, sound1, sound2,... - Names of sound files to be used by the applet.
# roi0/1/2 - List of high resolution images to be inserted into the panorama as zoomable feature. For syntax and details see separate chapter below


# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

function mem_build_param($n,$v) {
	if (empty($n) || empty($v) || $v===false) return '';
	return '<param name="'.$n.'" value="'.$v.'" />';
}

function mem_clamp($n,$min='',$max=''){
	if (is_numeric($n)){
		if (is_numeric($min) && $n < $min) return $min;
		if (is_numeric($max) && $n > $max) return $max;
		return $n;
	}
	return false;
}

function mem_panorama($atts) {
	global $path_from_root;
	
	$ptdir = $path_from_root . 'ptviewer/';
	
	extract(lAtts(array(
			'buttons'	=>	'1',
			'class'		=>	'panorama',
			'file'		=>	'',
			'height'	=>	'200',
			'width'		=>	'400',
			'wraptag'	=>	'div',
			'auto'		=>	'',
			'autorange'	=>	'',
			'barcolor'	=>	'',
			'bar_x'	=>	'',
			'bar_y'	=>	'',
			'bar_height'	=>	'',
			'bar_width'	=>	'',
			'bgcolor'	=>	'',
			'exposure'	=>	'',
			'fov'		=>	'',
			'fovmin'	=>	'',
			'fovmax'	=>	'',
			'fovsharp'	=>	'',
			'gamma'		=>	'',
			'maxarray'	=>	'',
			'pan'		=>	'',
			'panmin'	=>	'',
			'panmax'	=>	'',
			'pheight'	=>	'',
			'pwidth'	=>	'',
			'tilt'		=>	'',
			'tiltmin'	=>	'',
			'tiltmax'	=>	'',
			'quality'	=>	'',
			'view_x'	=>	'',
			'view_y'	=>	'',
			'view_height'	=>	'',
			'view_width'	=>	'',
			'wait'		=>	'',
			'waittime'	=>	'',
		), $atts));
	
	if (empty($file)) return '';
	
	$auto		= mem_clamp($auto,-360,360);
	$autorange	= mem_clamp($autorange,0,100);
	$bar_x		= (is_numeric($bar_x)) ? $bar_x : '';
	$bar_y		= (is_numeric($bar_y)) ? $bar_y : '';
	$bar_height= (is_numeric($bar_height)) ? $bar_height : '';
	$bar_width	= (is_numeric($bar_width)) ? $bar_width : '';
	$buttons 	= ($buttons=='1');
	$exposure	= (is_numeric($exposure)) ? $exposure : '';
	$fov		= mem_clamp($fov,12,165);
	$fovmin		= mem_clamp($fovmin,12,165);
	$fovmax		= mem_clamp($fovmax,12,165);
	$fovsharp	= mem_clamp($fovsharp,0,180);
	$gamma		= (is_numeric($gamma)) ? $gamma : '';
	$height		= (is_numeric($height)) ? $height : '200';
	$maxarray	= (is_numeric($maxarray)) ? $maxarray : '';
	$pan 		= mem_clamp($pan,-180,180);
	$panmin		= mem_clamp($panmin,-180,0);
	$panmax 	= mem_clamp($panmax,0,180);
	$pheight	= (is_numeric($pheight)) ? $pheight : '';
	$pwidth		= (is_numeric($pwidth)) ? $pwidth : '';
	$tilt		= mem_clamp($tilt,-90,90);
	$tiltmin	= mem_clamp($tiltmin,-90,0);
	$tiltmax	= mem_clamp($tiltmax,0,90);
	$view_x		= (is_numeric($view_x)) ? $view_x : '';
	$view_y		= (is_numeric($view_y)) ? $view_y : '';
	$view_height= (is_numeric($view_height)) ? $view_height : '';
	$view_width	= (is_numeric($view_width)) ? $view_width : '';
	$waittime	= mem_clamp($waittime,0,3600000);
	$width		= (is_numeric($width)) ? $width : '400';


	$hash='ptviewer'.time();

	$out ='';
	$out .='<applet name="'.$hash.'" archive="'.$ptdir.'ptviewer.jar" code="ptviewer.class" width="'.$width.'" height="'.$height.'">';
	$out .=mem_build_param('file',$file);
	$out .=mem_build_param('auto', $auto);
	$out .=mem_build_param('autorange', $autorange);
	$out .=mem_build_param('barcolor', $barcolor);
	$out .=mem_build_param('bgcolor', $bgcolor);
	$out .=mem_build_param('bar_x', $bar_x);
	$out .=mem_build_param('bar_y', $bar_y);
	$out .=mem_build_param('bar_height',$bar_height);
	$out .=mem_build_param('bar_width',$bar_width);
	$out .=mem_build_param('exposure', $exposure);
	$out .=mem_build_param('fov', $fov);
	$out .=mem_build_param('fovmin', $fovmin);
	$out .=mem_build_param('fovmax', $fovmax);
	$out .=mem_build_param('fovsharp', $fovsharp);
	$out .=mem_build_param('gamma', $gamma);
	$out .=mem_build_param('maxarray', $maxarray);
	$out .=mem_build_param('pan', $pan);
	$out .=mem_build_param('panmin', $panmin);
	$out .=mem_build_param('panmax', $panmax);
	$out .=mem_build_param('pheight',$pheight);
	$out .=mem_build_param('pwidth',$pwidth);
	$out .=mem_build_param('tilt', $tilt);
	$out .=mem_build_param('tiltmin', $tiltmin);
	$out .=mem_build_param('tiltmax', $tiltmax);
	$out .=mem_build_param('quality', $quality);
	$out .=mem_build_param('view_x', $view_x);
	$out .=mem_build_param('view_y', $view_y);
	$out .=mem_build_param('view_height',$view_height);
	$out .=mem_build_param('view_width',$view_width);
	if (!empty($wait)) {
		$out .=mem_build_param('wait', $wait);
		$out .=mem_build_param('waittime', $waittime);
	}
	
	if ($buttons===true){
		$auto = $auto==0? '0.5' : $auto;
		$out .=mem_build_param('frame', $ptdir.'frame4btn.gif');
		$out .=mem_build_param('shotspot0', ' x'.($width-56).' y'.($height-14).' a'.($width-42).' b'.($height)	." u'ptviewer:startAutoPan(".$auto.",0,1)' ");
		$out .=mem_build_param('shotspot1', ' x'.($width-42).' y'.($height-14).' a'.($width-28).' b'.($height)	." u'ptviewer:stopAutoPan()' ");
		$out .=mem_build_param('shotspot2', ' x'.($width-28).' y'.($height-14).' a'.($width-14).' b'.($height)	." u'ptviewer:ZoomIn()' ");
		$out .=mem_build_param('shotspot3', ' x'.($width-14).' y'.($height-14).' a'.($width).' b'.($height)		." u'ptviewer:ZoomOut()' ");
	}

	$out .='</applet>';

	if (!empty($wraptag)) {
		$out = tag($out,$wraptag, (!empty($class)?' class="'.$class.'"':'') );
	}
	
	return $out;
}

# --- END PLUGIN CODE ---

?>
