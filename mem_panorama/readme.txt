Plugin:	mem_panorama
Author:	Michael Manfre
URL:	http://manfre.net

Adds easy access tag for ptviewer 3.1.2. PTViewer is an applet use to display panoramic images in an interactive way. For more information about PTViewer, please visit http://webuser.fh-furtwangen.de/~dersch/.

PTViewer was created and is maintained by Helmut Dersch.


To install, create the folder ptviewer at the same level as your textpattern folder add the files ptviewer.jar and frame4btn.gif.

Folder structure should look like the following.

[web root]/
	|
    |---------->	textpattern/
	|                   |----->	...
    |
    |---------->	ptviewer/
	                    |----->	ptviewer.jar
	                    |----->	frame4btn.gif
    

COMPLETED
=========================
All features added after v2.5 - http://webuser.fh-furtwangen.de/~dersch/hdr/Readme.txt


Basic features from < v2.5
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



PENDING
=========================

# mousehs - Name of a user supplied javascript function which gets called everytime the mouse enters or leaves a hotspot. See example and notes on scripting below.
# getview - Name of a user supplied javascript function which gets called everytime the view (pan, tilt or field of view) changes. See example and notes on scripting below.
# frame - An image to be displayed in front of the panorama window. This should be a gif or jpeg image. It will be inserted into the applet window aligned to the lower right edge. See the control buttons in Controls.html for an example.
# shotspot0,1,2,...- Description of static hotspots, see next chapter
# pano0,1,2,3 -List of panorama images for built-in controls. See next chapters for details.
# hsimage - Name of hotspotimage describing masks for all hotspots. This image must have identical dimensions as the panoramic image. See chapter on hotspots below.
# hotspot0,1,2,... - Alternative way to set hotspots, see next chapter
# sound0, sound1, sound2,... - Names of sound files to be used by the applet.
# roi0/1/2 - List of high resolution images to be inserted into the panorama as zoomable feature. For syntax and details see separate chapter below
