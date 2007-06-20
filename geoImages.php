<?php
/*
Plugin Name: Geotagged Images
Plugin URI: http://scriptamac.at/wordpress/geotaggedImages/
Description: Reads the geographic coordinates from your images exifdata.
Version: 0.01
Author: Sigurd Buchberger
Author URI: http://scriptamac.at/wordpress/geotaggedImages/
Minimum WordPress Version Required: 2.0
*/

/*
Geotagged Images - Reads the geographic coordinates from your images exifdata.
Copyright (c) 2007 Sigurd Buchberger - www.scriptamac.at

This program is free software; you can redistribute it
and/or modify it under the terms of the GNU General Public
License as published by the Free Software Foundation;
either version 2 of the License, or (at your option) any
later version.

This program is distributed in the hope that it will be
useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
PURPOSE. See the GNU General Public License for more
details.

You should have received a copy of the GNU General Public
License along with this program; if not, write to the Free
Software Foundation, Inc., 59 Temple Place, Suite 330,
Boston, MA 02111-1307 USA
*/



// The function will be called when the attachment is added 
add_action('add_attachment', 'get_exif');



//reads exifdata and if available writes the gpstags to the database
function get_exif($id) {
    $fileName = get_attached_file($id);    
    if(!$fileName or !is_readable($fileName) ) return;
    
    //enable if you use big images or tiffs
    //ini_set('memory_limit', '32M');    
    $exifData = exif_read_data($fileName);
    
    
    if($exifData){
        
    	//if the gps related exif data is available
        if($exifData['GPSLongitude'] && $exifData['GPSLatitude']){
        	
        	
        	//retrieve the logitude hours -- if the exifdata uses st like 765756/567567 compute the correct value 
            $h = explode("/",$exifData['GPSLongitude'][0]);    
            if(count($h)==2){$h=floatval($h[0])/floatval($h[1]);}else{$h = floatval($h);}
            
            $min =  explode("/",$exifData['GPSLongitude'][1]);
            if(count($min)==2){$min=floatval($min[0])/floatval($min[1]);}else{$min = floatval($min);}
            
            $sec =  explode("/",$exifData['GPSLongitude'][2]);
            if(count($sec)==2){$sec=floatval($sec[0])/floatval($sec[1]);}else{$sec = floatval($sec);}
            
            
            $lon = $h + $min/60 + $sec/3600;
            
            
            //convert the Ref notation to positive and negative values
            if($exifData['GPSLongitudeRef'] && strtolower(substr($exifData['GPSLongitudeRef'],0,1)) == "w" &&  $lon>0){
                $lon = -1 * $lon;
            }

            $g = explode("/",$exifData['GPSLatitude'][0]);

            if(count($g)==2){$g=floatval($g[0])/floatval($g[1]);}else{$g = floatval($g);}
            $min =  explode("/",$exifData['GPSLatitude'][1]);
            if(count($min)==2){$min=floatval($min[0])/floatval($min[1]);}else{$min = floatval($min);}
            $sec =  explode("/",$exifData['GPSLatitude'][2]);
            if(count($sec)==2){$sec=floatval($sec[0])/floatval($sec[1]);}else{$sec = floatval($sec);}
            
            $lat = $g + $min/60 + $sec/3600;
            
             //convert the Ref notation to positive and negative values
            if($exifData['GPSLatitudeRef'] && strtolower(substr($exifData['GPSLatitudeRef'],0,1)) == "w" &&  $lat>0){
                $lat = -1 * $lat;
            }
        }    
        
        
        // delete the old metadata and add the new value
        delete_post_meta($id, '_geo_location');
		add_post_meta($id, '_geo_location', $lat . ',' . $lon);                
    }

}
