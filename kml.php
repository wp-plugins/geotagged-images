<?php

require('./../../wp-blog-header.php');

/*
 * arrays to be used in the templates
 */
$images = array();
$blogPosts = array();
	

$postID = ($_REQUEST['p']) ? intval($_REQUEST['p'],10): false; 
$category =($_REQUEST['cat'])? intval($_REQUEST['cat'],10):false;


if($postID){	
	$posts = array();	
	$posts[] = get_post($postID); 	
}else {
	$criteria = array("category" => $category, 'numberposts' => -1, 'post_type' => '', 'post_status' =>  'publish', 'post_parent' => 0);
	$posts = get_posts($criteria);
	
	//$posts = get_posts(($category)?"category=".$category:"");
}
		
foreach ($posts as $post) {

	$coordinates = get_post_meta($post->ID,"_geo_location");
	if(count($coordinates)>=1 && $coordinates[0] != ","){
		list($lat,$lon) = split(',',$coordinates[0]);		
		$post->lon = floatval($lon);
		$post->lat = floatval($lat);		
		$blogPosts[$post->ID] = $post;
	}

	$criteria = array('post_parent' => $post->ID, 'numberposts' => -1, 'post_type' => '', 'post_status' => '');
	$children = get_posts($criteria);
	
	
	
	foreach ($children as $child) {
		
		if(stripos(get_post_mime_type($child),"image")!==false){
		
			$coordinates = get_post_meta($child->ID,"_geo_location");
			if(count($coordinates)>=1 && $coordinates[0] != ","){
				list($lat,$lon) = split(',',$coordinates[0]);					
				$child->lon = floatval($lon);
				$child->lat = floatval($lat);				
				$images[] = $child;
									
			}
		}		
	}
	
	
}



header('Content-type: application/vnd.google-earth.kml+xml; charset=' . get_option('blog_charset'), true);
header('Content-Disposition: attachment; filename="'.get_bloginfo('title') .'.kml"');

echo '<?xml version="1.0" encoding="' . get_option('blog_charset') .'" ?>';
?><!-- generator="wordpress/<?php echo $wp_version ?>" -->	
<kml xmlns="http://earth.google.com/kml/2.1">
	<Document>
    	<name><?php  echo get_bloginfo('title') ?></name>
    		<open>1</open>
    		<description><?php get_bloginfo('description') ?></description> 
    		
    		<Style id="post normal">
				<IconStyle>
					<Icon>
							<href>http://maps.google.com/mapfiles/kml/pal4/icon8.png</href>
					</Icon>
				</IconStyle>
				<LabelStyle>
					<scale>0</scale>
				</LabelStyle>
				<BalloonStyle>
					<text>$[description]</text>
				</BalloonStyle>				
			</Style>
			
			<Style id="post highlighted">
				<IconStyle>
					<scale>1.1</scale>
					<Icon>
							<href>http://maps.google.com/mapfiles/kml/pal4/icon8.png</href>
					</Icon>
				</IconStyle>
				<BalloonStyle>
					<text>$[description]</text>
				</BalloonStyle>				
			</Style>
		    
    		
			
		    <Style id="image highlighted">
				<IconStyle>
					<Icon>
						<href>http://maps.google.com/mapfiles/kml/pal4/icon46.png</href>
					</Icon>
				</IconStyle>
				<LabelStyle>
					<scale>0</scale>
				</LabelStyle>
				<BalloonStyle>
					<text>$[description]</text>
				</BalloonStyle>
			</Style>
			
			<Style id="image normal">
				<IconStyle>
					<scale>1.1</scale>
					<Icon>
						<href>http://maps.google.com/mapfiles/kml/pal4/icon46.png</href>
					</Icon>
				</IconStyle>
				<BalloonStyle>
					<text>$[description]</text>
				</BalloonStyle>				
			</Style>
		    		
    		<StyleMap id="image">
				<Pair>
					<key>normal</key>
					<styleUrl>#image highlighted</styleUrl>
				</Pair>
				<Pair>
					<key>highlight</key>
					<styleUrl>#image normal</styleUrl>
				</Pair>
			</StyleMap>
    		
    		<StyleMap id="post">
				<Pair>
					<key>normal</key>
					<styleUrl>#post normal</styleUrl>
				</Pair>
				<Pair>
					<key>highlight</key>
					<styleUrl>#post highlighted</styleUrl>
				</Pair>
			</StyleMap>
			
   			<Folder>
				<name>Posts</name>
				<?php foreach ($blogPosts as $post): ?>
					<Placemark>
	        			<name><?php echo $post->post_title ?></name>
						<open>0</open>
		        		<description><![CDATA[
		        		<h1><font style='font-size: 15pt;' color='#333'><?php echo $post->post_title ?></font></h1>
		        		<?php echo str_replace("\n",'<br />',$post->post_content); ?>
		        		]]></description>
	 					<Snippet maxLines="2"><?php echo strip_tags($post->post_content); ?></Snippet>
		        		<Point><coordinates><?php echo $post->lon ?>,<?php echo $post->lat ?></coordinates></Point>
		        		<LookAt>
							<longitude><?php echo $post->lon ?></longitude>
	        				<latitude><?php echo $post->lat ?></latitude>
	        				<altitude>0</altitude>
							<range>3500</range>							
	              		</LookAt>
						<styleUrl>#post</styleUrl>              			
	              	</Placemark>
				<?php endforeach; ?>
			</Folder>
			
			<Folder>
				<name>Images</name>
				<?php foreach ($images as $post): ?>
					<Placemark>
	        			<name><?php echo $post->post_title ?></name>
						<open>0</open>
		        		<description><![CDATA[
		        		<h1><font style='font-size: 15pt;' color='#333'><?php echo $post->post_title; ?></font></h1>
		        		<center><img src="<?php wp_get_attachment_thumb_url($post->ID); ?>" /></center>
		        		<?php echo str_replace("\n",'<br />',$post->post_content); ?>
		        		]]></description>
	 					<Snippet maxLines="2"><?php echo strip_tags($post->post_content); ?></Snippet>
		        		<Point><coordinates><?php echo $post->lon ?>,<?php echo $post->lat ?></coordinates></Point>
		        		<LookAt>
							<longitude><?php echo $post->lon ?></longitude>
	        				<latitude><?php echo $post->lat ?></latitude>
	        				<altitude>0</altitude>
							<range>3500</range>							
	              		</LookAt>
						<styleUrl>#post</styleUrl>              			
	              	</Placemark>
				<?php endforeach; ?>
				
			
			</Folder>
			
			
			
				
	</Document>	
</kml>