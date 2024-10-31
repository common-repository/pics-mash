<?php
/*
Plugin Name: Pics Mash Plugin Free Edition
Plugin URI: http://www.picsmashplugin.com/
Description: Creates a Facemash type rating game using your WordPress images. Basic free edition.
Version: 1.8
Author: MYO
Author URI: http://codecanyon.net/user/mikemayhem3030/portfolio
*/

    #} Hooks

    #} Install/uninstall
    register_activation_hook(__FILE__,'picsmash__install');
    register_deactivation_hook(__FILE__,'picsmash__uninstall');
    
    #} general
    add_action('init', 'picsmash__init');
    add_action('admin_menu', 'picsmash__admin_menu'); #} Initialise Admin menu
    

	#} Initial Vars
	global $picsmash_db_version;
	$picsmash_db_version                = "1.0";
	$picsmash_version               = "1.8";
	$picsmash_activation                = '';


	#} Urls
    global $picsmash_urls;
    $picsmash_urls['home']      	 = 'http://www.picsmashplugin.com/';
    $picsmash_urls['docs']      	 = plugins_url('/documentation/index.html',__FILE__);
	$picsmash_urls['forum']      	 = 'http://forums.epicplugins.com/';
    $picsmash_urls['updateCheck']	 = 'http://www.epicplugins.com/api/';
	$picsmash_urls['regCheck']		 = 'http://www.epicplugins.com/registration/';
	$picsmash_urls['subscribe'] 	 = "http://eepurl.com/tW_t9";
    $picsmash_urls['json']      	 = plugins_url('/json/json.php',__FILE__);
	$picsmash_urls['jsonCheck']      = 'http://www.epicplugins.com/json/';
	$picsmash_urls['goPro']			 = 'http://epicplugins.com/epic-reviews/pics-mash-image-rating-tool/';

	
	#} Page slugs
    global $picsmash_slugs;
    $picsmash_slugs['config']           = "picsmash-plugin-config";
    $picsmash_slugs['share']            = "picsmash-plugin-share";
    $picsmash_slugs['settings']         = "picsmash-plugin-settings";

	#} Install function
	function picsmash__install(){

    #} Default Options
    add_option('Kfactor',24,'','yes');    //default factor for K
    add_option('freepicsmashshared','no','','yes'); 

		
		    $current_user = wp_get_current_user();    //email the current user rather than admin info more likely to reach a human email 
			$userEmail = $current_user->user_email;
		    $userName =  $current_user->user_firstname;
			$LastName =  $current_user->user_lastname;
			$plugin = 'PicsMashFreeEdition';
			
			if(get_option('freepicsmashshared') == 'no'){    //only send them an install email once
			picsmash_sendReg($userEmail,$userName,$plugin);
			update_option('freepicsmashshared', 'yes');
			}

	}  


	#} Uninstall
	function picsmash__uninstall(){
	    
	    #} Removes initial settings, leaves config intact for upgrades.
	    delete_option("picsmash_db_version");
	    delete_option("picsmash_version");
	    delete_option("picsmash_activation");
	    
	}



#} Initialisation - enqueueing scripts/styles
function picsmash__init(){
  
    global $picsmash_slugs, $picsmash_taxonomy; #} Req
    
    #} Admin & Public
    wp_enqueue_script("jquery");
    wp_enqueue_style('picsmashcss', plugins_url('/css/PicsMash.css',__FILE__) );
    
	function my_admin_scripts() {
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_register_script('my-upload', plugins_url('/js/my-script.js',__FILE__), array('jquery','media-upload','thickbox'));
        wp_enqueue_script('my-upload');
        }
	
	
	
	
	function my_admin_styles() {
        wp_enqueue_style('thickbox');
        }
        add_action('admin_print_scripts', 'my_admin_scripts');
        add_action('admin_print_styles', 'my_admin_styles');
    
    #} Admin only
    if (is_admin()) {
        
        #} Admin CSS
        wp_enqueue_style('mysmashCSSADM', plugins_url('/css/MySmashAdmin.css',__FILE__) );

    }
    
    #} Custom post type
    $labels = array(
                'name' => _x('Pics Mash', 'post type general name'),
                'singular_name' => _x('Pics Mash', 'post type singular name'),
                'add_new' => _x('Manually Add Pic', 'pic'),
                'add_new_item' => __('Manually Add New Pic'),
                'edit_item' => __('Edit Pic'),
                'new_item' => __('New Pic'),
                'view_item' => __('View Pic'),
                'search_items' => __('Search Pics'),
                'not_found' =>  __('No pics found'),
                'not_found_in_trash' => __('No pics found in Trash'),
                'parent_item_colon' => '',
                'menu_name' => 'Pics Mash'
            );
    $args = array(
                'labels' => $labels,
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'query_var' => true,
                'rewrite' => true,
                'capability_type' => 'post',
                'has_archive' => true,
                'hierarchical' => false,
                'menu_icon' => plugins_url('i/image.png',__FILE__),
                'menu_position' => null,
                'supports' => array( 'title', 'author','comments')
            );
    #} Register it
    register_post_type('picsmash',$args);

 
}

#} Add le admin menu
function picsmash__admin_menu() {

    global $picsmash_slugs,$picsmash_menu; #} Req
    
    $picsmash_menu = add_menu_page( 'Pics Mash Options', 'My Pics Mash', 'manage_options', $picsmash_slugs['config'], 'picsmash_pages_configs', plugins_url('i/image.png',__FILE__));
    add_submenu_page( $picsmash_slugs['config'], 'Settings', 'Settings', 'manage_options', $picsmash_slugs['settings'] , 'picsmash_pages_settings' );
	add_submenu_page( $picsmash_slugs['config'], 'Share', 'Share', 'manage_options', $picsmash_slugs['share'] , 'picsmash_pages_share' );
    
}

function picsmash_pages_share(){
	global $picsmash_urls;
		?>	
	    <div id="sgpBod">
       <div class='myslogo'><?php echo '<img src="' .plugins_url( 'i/picsmash.jpg' , __FILE__ ). '" > ';   ?></div>
        <div class='mysearch'>
   <?php
		picsmash_header();
		?>
		<div class="postbox">
                <h3 style="padding:8px;"><label><?php _e('How Do Your Images Compete?', 'PicsMash' ); ?></label></h3>
                <div class="inside">
                	<b>This Feature is only available in the <a href ="<?php echo $picsmash_urls['goPro']; ?>">Premium Version</a></b> This page allows you to share your images with <a href = 'http://epicpicsmash.com/' target = '_blank'>Epic Pics Mash.com.</a>   
                	If you do not want to share your photos please visit the settings page and change the option to no. Sharing the images can help you 
                	gain new visitors to your website as the Epic Pics Mash site will contain links back to your sites and help you grow and find new people. 
                </div>
        </div>  
		
		
		<?php
		$src = plugins_url('/i/epicsplash.png',__FILE__);
		echo "<a href = 'http://epicpicsmash.com/' target = '_blank'><img src = '$src' title = 'epic pics mash' alt = 'the ultimate pics mash' /></a>";
?>

</div>
</div>

		
		<?php	 
} 


#} Options page
function picsmash_pages_configs() {
    
    global $wpdb, $picsmash_urls, $picsmash_version,$picsmash_slugs;    #} Req
    // add database pointer
    $wpdb->nggpictures                  = $wpdb->prefix . 'ngg_pictures';
    $wpdb->nggallery                    = $wpdb->prefix . 'ngg_gallery';
    $wpdb->nggalbum                     = $wpdb->prefix . 'ngg_album';
    
    if (!current_user_can('manage_options'))  {
        wp_die( __('You do not have sufficient permissions to access this page.','PicsMash') );
    }
    ?>
    <div id="sgpBod">
       <div class='myslogo'><?php echo '<img src="' .plugins_url( 'i/picsmash.jpg' , __FILE__ ). '" > ';   ?></div>
        <div class='mysearch'>
            
            <?php picsmash_header(); ?>


               <div class="postbox-container" id="main-container" style="width:75%;">
            <div class="postbox">
           <h3 style="text-align:center"><label>Welcome to Pics Mash</label></h3>
                <div class="inside">
                    <p style="text-align:center"><strong>Welcome to Pics Mash v3.0</strong>: the Ultimate Image Rating Tool for WordPress If you want to vote on future features or discover more cool plugins, check out the <br/><a href="http://epicplugins.com" target="_blank">epic plugins site</a>!</p>
                    <div id="SocialGalleryOptions">
                        <div><a href="admin.php?page=<?php echo $picsmash_slugs['settings']; ?>" class="SocialGalleryOB">Settings</a></div>
                        <div><a href="edit.php?post_type=picsmash" class="SocialGalleryOB">Manage Pics</a></div>
                        <div><a href="admin.php?page=<?php echo $picsmash_slugs['share']; ?>" class="SocialGalleryOB">Epic Pics Mash</a></div>
                    </div>
                    <div style="clear:both"></div>
                </div>
            </div>
            
           <?php ps_add_images_postbox(); ?>
            
            <div class="postbox">
                <h3 style="padding:8px;"><label><?php _e('Pics Mash News', 'PicsMash' ); ?></label></h3>
                <div class="inside">
                	<?php picsmash_retrieveNews(); ?>
                </div>
            </div>  
   </div>
   
   <div class="postbox-container" id="side-container" style="width:24%;margin-left:1%">
            <div class="postbox">
                <h3 style="padding:8px;"><label><?php _e('Keep up to date','PicsMash'); ?></label></h3>
                <div class="inside">
                	<!-- Begin MailChimp Signup Form -->

					<style type="text/css">

					</style>
					<div id="mc_embed_signup">
					<form action="http://epicplugins.us6.list-manage.com/subscribe/post?u=1c0fcf180d7cda3d5add7ba76&amp;id=034a5859d6" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
						<label for="mce-EMAIL">Subscribe to our mailing list</label>
						<input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="email address" required>
						<br/><br/>
						<div class="clear"><input type="submit" value="Subscribe" name="subscribe" class = "button-primary" style = "float:right" id="mc-embedded-subscribe" class="button"></div>
					</form>
					</div>
					<div style = "clear:both"></div>
					
					<!--End mc_embed_signup-->

                </div>
            </div>
   </div>

    <div class="postbox-container" id="side-container" style="width:24%;margin-left:1%">
            <div class="postbox">
                <h3 style="padding:8px;"><label><?php _e('Share the love','PicsMash'); ?></label></h3>
                <div class="inside">
                	<p>This plugin has been developed with love & effort, it's a work in progress and I really appreciate all of the contribution you guys make to it. Thank you!</p>
                	
                    <!-- <a href="codecanyon.net/item/social-gallery-wordpress-photo-viewer-plugin/2665332?ref=stormgate" target="_blank">Rate it 5 stars on Code Canyon</a><br /> -->
                  
                    <div  style="text-align:center;margin-top:12px"><strong>Share PicsMash:</strong></div>
                    <div class="socialGalleryShareBox">
	                    <a href="http://www.facebook.com/sharer.php?s= 100&amp;p[title]=Pics Mash - The Ultimate Image Rating WordPress Plugin&amp;p[url]=http://picsmashplugin.com/&amp;p[summary]=Pics Mash helps you discover which image is best. A Must Have plugin for all WordPress users."target="_blank"><img src="<?php echo plugins_url('/i/fbshare.png',__FILE__); ?>" alt="" title="Share on Facebook" /></a>
	        	     	<a href="http://twitter.com/home?status=I Recommend You Pics Mash for WordPress!! http://picsmashplugin.com/" target="_blank"><img src="<?php echo plugins_url('/i/tweet.png',__FILE__); ?>" alt="" title="Share this on Twitter" /></a>
					 	<a href="http://www.linkedin.com/shareArticle?mini=true&url=http://picsmashplugin.com/&title=Pics Mash for WordPress&source=PicsMash" target="_blank"><img src="<?php echo plugins_url('/i/linkedin.png',__FILE__); ?>" alt="" title="Share this on LinkedIn" /></a>
						<a href="https://plus.google.com/share?url=http://picsmashplugin.com/" target="_blank"><img src="<?php echo plugins_url('/i/gp.png',__FILE__); ?>" alt="" title="Share this on Google+1" /></a>
         			</div>
                </div>
            </div>
   </div>

   <div class="postbox-container" id="side-container" style="width:24%;margin-left:1%">
            <div class="postbox">
                <h3 style="padding:8px;"><label><?php _e('Other Plugins','PicsMash'); ?></label></h3>
                <div class="inside">
					<table>
						<tr>
							<td><a href = "http://codecanyon.net/item/wpeddit-reddit-for-wordpress/4053648?ref=mikemayhem3030" target = "_blank"><img src = "http://2.s3.envato.com/files/48455291/wpeddit-thumb.jpg"/></a></td>
							<td style = "padding:3px"><a href = "http://codecanyon.net/item/wpeddit-reddit-for-wordpress/4053648?ref=mikemayhem3030" target = "blank">WPeddit</a> turn your WordPress website into Reddit!
								</td>
						</tr>
						<tr>
							<td><a href = "http://epicplugins.com/external-url-link-to-featured-images/"><img src = "http://epicplugins.com/wp-content/uploads/2013/02/rsz_256.png"/></a></td>
							<td style = "padding:3px"><a href = "http://epicplugins.com/external-url-link-to-featured-images/">Link 2 Featured Image</a> set your featured image from an external URL.</td>
						</tr>
					</table>
                </div>
            </div>
	 
	 <div style = 'clear:both'></div>
	 
	 </div>            

     </div>


</div>
<?php
}

function ps_add_images_postbox(){
				    global $wpdb, $picsmash_urls, $picsmash_version,$picsmash_slugs;    #} Req
			    // add database pointer
			    $wpdb->nggpictures                  = $wpdb->prefix . 'ngg_pictures';
			    $wpdb->nggallery                    = $wpdb->prefix . 'ngg_gallery';
			    $wpdb->nggalbum                     = $wpdb->prefix . 'ngg_album';
	            	?>
	            <div class="postbox">
                <h3><label>Add Images from Media Library</label></h3>
                <div class="inside">
                <?php
                //count the number of images
                $pictures = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type = 'attachment' AND post_status != 'trash' AND (post_mime_type = 'image/jpeg'
                        OR post_mime_type = 'image/gif' OR post_mime_type = 'image/png')");
                    
                $n = count($pictures);
                $URL = get_site_url() . "/wp-admin/upload.php";
                $added = get_option('MediaC');
                echo "<p>Your website has <b>". $n . "</b> images in its <a href = '$URL'>Media Library.</a>";
                
                $NEXTG = get_option('NextG');
                
                if($NEXTG == 'yes'){
                
				$query = "SELECT pid FROM $wpdb->nggpictures";
                $NEXTGEN = $wpdb->get_col($query);
				
                $NG = count($NEXTGEN);
                if($NEXTGEN == NULL){
                    $NG = 0;
                }
                
                $added = get_option('MediaC');
                echo "<br/>Your website has <b>" . $NG . "</b> images in <a href = 'http://www.nextgen-gallery.com/' target = '_blank'>NextGEN galleries</a></p><br/>";
                $n = $n + $NG;
                }else{
                    echo "</p><br/>";
                }
                
                
                if($n==0){
                echo "<p>Please upload some images to your media library or NextGen gallery to be able to rate them.</p>";
                }else{
            
                }
            
                ?>
                <script type="text/javascript">var loadingImg = '<?php echo plugins_url( 'i/loading.gif' , __FILE__ ); ?>';</script>
                <div id="feedback"></div>
                
                <form id = "ps-ajax" action="" method="post">
                    <p id="footerSub">
                        <input class = "button-primary" type="submit" value="Add Images" />
                    </p>
                </form>
                
                </div>
             </div>
<?php
}



add_action('wp_print_scripts','include_jquery_form_plugin');
	
function include_jquery_form_plugin(){
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-form',array('jquery'),false,true ); 
	  }

#} Retrieves updated news.
function picsmash_retrieveNews(){


                include_once(ABSPATH . WPINC . '/feed.php');
                add_filter( 'wp_feed_cache_transient_lifetime' , 'ps_feed_cache' );
				$url = 'http://epicplugins.com/feed/';
                $rss = fetch_feed($url);
                remove_filter( 'wp_feed_cache_transient_lifetime' , 'ps_feed_cache' );
                
                if (!is_wp_error( $rss ) ) {
					
					$maxitems = $rss->get_item_quantity(5); 
                    $rss_items = $rss->get_items(0, $maxitems); 
					
				} ?>
                
                <ul>
                    <?php 
					if ($maxitems == 0) 
						echo '<li>No News (is this good news?)</li>';
                    else 
						foreach ( $rss_items as $item ) : ?>
                    <li>
                        <a href='<?php echo esc_url( $item->get_permalink() ); ?>' target = '_blank'
                        title='<?php echo 'Posted '.$item->get_date('j F Y | g:i a'); ?>'>
                        <?php echo  $item->get_title() ; ?></a><br/>
                        <?php echo  $item->get_description() ; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                
                <?php
	
}

function ps_feed_cache( $seconds )
			{
			  // change the default feed cache recreation period to 2 hours
			  return 7200;
			}

// some functions - just call me Zuck baby!!
function expected($Rb, $Ra) {
     return 1/(1 + pow(10, ($Rb-$Ra)/400));
    }
// Calculate the new winnner score
function win($score, $expected, $k = 24) {
    return $score + $k * (1-$expected);
    }
// Calculate the new loser score
function loss($score, $expected, $k = 24) {
    return $score + $k * (0-$expected);
    }

function get_score($ID){
    $rating = get_post_meta($ID,'rating',true);
    return $rating;
}

function update_score($ID,$score){
    $score = (double)$score;
    update_post_meta($ID, 'rating', $score);
}

function update_wins($ID){
    $votes = get_post_meta($ID,'wins',true);
    $votes = $votes + 1;
    update_post_meta($ID, 'wins', $votes);
}

function update_losses($ID){
    $votes = get_post_meta($ID,'losses',true);
    $votes = $votes + 1;
    update_post_meta($ID, 'losses', $votes);
}


function picsmash_load_scripts($hook){
    global $picsmash_menu;
    if( $hook != $picsmash_menu)
        return;
    
    wp_enqueue_script('picsmash-ajax',plugins_url('/js/ajax-script.js',__FILE__),array('jquery'));
    
}
add_action('admin_enqueue_scripts','picsmash_load_scripts');


function picsmash_get_all_pics(){
    
            global $wp, $wpdb;  #} Req
            // get all of the media images from the media library that we haven't added
            $j = 0;
            $broke = 0;
            
            $querystr = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'picsmash' AND meta_value = 1 ORDER BY post_id";
            $added = $wpdb->get_col($querystr);
            $str = implode(',', $added);

            if(count($added) == 0){
            $querystr = "SELECT * FROM $wpdb->posts WHERE post_status != 'trash' AND post_type = 'attachment' AND (post_mime_type = 'image/jpeg' OR post_mime_type = 'image/gif' OR post_mime_type = 'image/png')";
            }else{
            $querystr = "SELECT * FROM $wpdb->posts WHERE post_status != 'trash' AND ID NOT IN ($str) AND post_type = 'attachment' AND (post_mime_type = 'image/jpeg' OR post_mime_type = 'image/gif' OR post_mime_type = 'image/png')";
            }
            $pictures = $wpdb->get_results($querystr);


            $i = 1;
            
            foreach($pictures as $picture){
                    
                $the_img = wp_get_attachment_image_src( $picture->ID,"full" );
                $img_full = $the_img[0];
                $ID = $picture->ID;
                $title = get_post_meta($picture->ID, '_wp_attached_file',true);
       
                            #} Create a new post in the post database and save as published intitally
                             $my_post = array(
                             'post_title' => $title,
                             'post_status' => 'publish',
                             'post_type' => 'picsmash',
                              );
                        
                            #} Insert the post into the database
                             $post_id = wp_insert_post( $my_post );
                             update_post_meta($post_id, 'rating', 1400);
                             update_post_meta($post_id, 'wins', 0);
                             update_post_meta($post_id, 'losses',0);
                             update_post_meta($post_id,'img_full',$img_full);
                             update_post_meta($post_id,'mediaID',$picture->ID);

                             update_post_meta($picture->ID,'picsmash',1);

                             update_post_meta($post_id,'gallery','Media');
                             update_post_meta($post_id,'picshare',0);
                                    
                            $j++;
                            
                    
                }

            $result['message'] = "<b>" . $j . " new images processed</b>";
            
            echo $result['message'];

            die();
    
}


add_action('wp_ajax_picsmash_get_all_pics', 'picsmash_get_all_pics');


#} Options page
function picsmash_pages_settings() {
    
    global $wpdb, $picsmash_urls, $picsmash_version;    #} Req
    
    if (!current_user_can('manage_options'))  {
        wp_die( __('You do not have sufficient permissions to access this page.') );
    }
    
    
?><div id="sgpBod">
        <div class='myslogo'><?php echo '<img src="' .plugins_url( 'i/picsmash.jpg' , __FILE__ ). '" > ';   ?></div>
        <div class='mysearch'>
        	
        <?php 
        picsmash_header();
		picsmash_html_settings();
    	?>
    	
    </div>
</div>
<?php
}

function picsmash_header(){

    global $picsmash_urls;
    ?>
 

<div id="fb-root"></div>
<script>
  window.fbAsyncInit = function() {
    // init the FB JS SDK
    FB.init({
      appId      : '438275232886336', // App ID from the App Dashboard
      channelUrl : '<?php echo plugins_url('/fb/channel.html',__FILE__);?>', // Channel File for x-domain communication
      status     : true, // check the login status upon init?
      cookie     : true, // set sessions cookies to allow your server to access the session?
      xfbml      : true  // parse XFBML tags on this page?
    });

    // Additional initialization code such as adding Event Listeners goes here

  };

  // Load the SDK's source Asynchronously
  // Note that the debug version is being actively developed and might 
  // contain some type checks that are overly strict. 
  // Please report such bugs using the bugs tool.
  (function(d, debug){
     var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement('script'); js.id = id; js.async = true;
     js.src = "//connect.facebook.net/en_US/all" + (debug ? "/debug" : "") + ".js";
     ref.parentNode.insertBefore(js, ref);
   }(document, /*debug*/ false));
</script>

    <?php
    //build the twitter text tweet
        $URL = $picsmash_urls['home'];
        $siteURL = get_site_url();
        $PicsM = "http://www.picsmashplugin.com/";
        $text = "I love " . $PicsM;
        $hash = "#picsmash";
        $QP = "?url=".$URL."&text=".$text."&hashtags=".$hash;
    ?>

	<a href="https://twitter.com/PicsMashPlugin" class="twitter-follow-button" data-show-count="false" data-lang="en">Follow @PicsMashPlugin</a>
	<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
	

    <?php

        $img = "http://3.s3.envato.com/files/37870139/previewnew.jpg";
        echo "<a href='http://pinterest.com/pin/create/button/?url=$URL&media=$img&description=Description' class='pin-it-button' count-layout='horizontal'><img border'0' src='//assets.pinterest.com/images/PinExt.png' title='Pin It' /></a>";
    ?>

  <div class="fb-like" data-href="http://www.picsmashplugin.com/" data-send="false" data-width="360" data-show-faces="false" data-font="arial"></div>


    <?php
	$home = $picsmash_urls['home'];
	$docs = $picsmash_urls['docs'];
	$forum =$picsmash_urls['forum'];
	$subs = $picsmash_urls['subscribe'];
	$pro = $picsmash_urls['goPro'];	
        echo "<div id = 'ps-links' style = 'padding-top:1%;padding-bottom:1%'><ul style = 'display:inline'>
        <li style = 'display:inline;padding-right:1%'><a href = '$home'>Demo Site</a></li>
        <li style = 'display:inline;padding-right:1%'><a href = '$docs'>Documentation</a></li>
        <li style = 'display:inline;padding-right:1%'><a href = '$pro'>Go PRO</a></li>
        <li style = 'display:inline;padding-right:1%'><a href = '$forum'>Support Forum</a></li>
        <li style = 'display:inline;padding-right:1%'><a href = '$subs'>Subscribe to the EPIC mailing list</a></li>
        <li style = 'display:inline;padding-right:1%'><a href='mailto:mikemayhem3030@gmail.com?Subject=Hi%20Mike You Rock!'>Contact the developer</a></li>
        </ul></div>";



}




function picsmash_html_settings(){
        
    global $wpdb, $picsmash_urls, $picsmash_slugs;  #} Req
    
    
    ?>
    
<div class = "wrap">
	
	<h1>You are currently using the <b>free version</b> of the Pics Mash plugin.</h1>
	<p>Please upgrade to the <b>premium</b> version for only <b>$10</b> from CodeCanyon</p>
	
	<div id = 'ps_cc' style = 'text-align:left'>
	<a href = "<?php echo $picsmash_urls['goPro'];	?>" target = "_blank">
	<?php echo '<img src="' .plugins_url( 'i/CCion.png' , __FILE__ ). '" > ';   ?></br></br><?php echo '<img src="' .plugins_url( 'i/CCbuy.png' , __FILE__ ). '" > ';   ?></a>
	</div>
	
	<br/><br/>
	<h2>Premium Plugin information</h2>
	<h3>Main features</h3>
	<ol>
		<li>Customisable settings</li>
		<li>Create Category, Gallery and Author specific Pics Mash games (e.g. which image is best from the Nature Category?)</li>
		<li>NextGEN Gallery Support</li>
		<li>Manually add new images and external images</li>
		<li>Share and Comment - leverage social networks</li>
		<li>Front end image uploading - allow users to add images to your own Pics Mash (or for them to create their very own Pics Mash)</li>
	</ol>
	
		
	<h3>Find out which image is best</h3>
	Do you often wonder which images on your WordPress website give the biggest <strong>wow</strong> to your visitors? Through this plugin you can find out!!
	
	<img src="http://picsmashplugin.com/23.png" alt="" />
	<h3>Improve Social Sharing and engagement</h3>
	v2.0 of the plugin brings with it a <strong>lot</strong> of new features described below. The biggest of these is the ability to view each image in the Pics Mash game and comment, share and tweet it. This helps engage your website users even more to your photos!
	
	<img src="http://picsmashplugin.com/24.png" alt="" />
	<h3>WordPress comments</h3>
	Don’t fancy having Facebook comments on your site? The plugin works just as well with standard WordPress commenting systems. The choice is yours.
	
	<img src="http://picsmashplugin.com/25.png" alt="" />
	<h3>Allow your users to add images to your Pics Mash</h3>
	Let your website users create their very own Pics Mash competitions. Increase the number of sign ups to your site by asking for users to be registered with you before they can add images to your Pics Mash.
	
	<img src="http://picsmashplugin.com/22.png" alt="" style = "width:100%"/>
	
	Using the shortcode <code>[picsmash author = ‘1’]</code> and <code>[toprated author = ‘1’]</code> will display the images uploaded by user ID = 1 in their own Pics Mash game. Gives you the ability to allow your users to create their very own Pics Mash games!!
	
	You can use the shortcode in user generated custom page templates with coding similar to
	<code>
	&lt;?php
	global $current_user;
	get_currentuserinfo();
	$pid = $current_user-&gt;ID;
	echo do_shortcode(["picsmash author = '$pid'"]);
	?&gt;
	</code>
	<div>
	<h3>Version 2.0 <strong>released</strong> 2 November 2012</h3>
	&nbsp;
	<h4>MAJOR UPGRADE</h4>
	<ol>
		<li>Display Pics Mash rating competitions filtered by categories/author/gallery – using custom taxonomies to keep separate from main site categories</li>
		<li>tag your images with keywords</li>
		<li>Allow logged in users to upload images to your pics mash rating games from the front end – discover new content and increase user engagement</li>
		<li>Give your users something to come back to your site for – they will want to check how their images are doing compared to others</li>
		<li>Each image gets its own custom page with stats (rating,wins,losses); optional social share features (FB like, tweet, Pin it,Google +1 it); and optional discussions systems included (WordPress or Facebook)</li>
		<li>Increase user interaction on each image in the Pics Mash, gain 10x more shares, tweets and increase user engagement – drive more traffic to your site through social channels.</li>
		<li>Integrates seamlessly with <a href="http://codecanyon.net/item/social-gallery-wordpress-photo-viewer-plugin/2665332?ref=mikemayhem3030" target = "_blank">Social Gallery Image Viewer</a> – boost engagement even more.</li>
	</ol>
	
	<a href = "http://codecanyon.net/item/pics-mash-image-rating-tool/3256459?ref=mikemayhem3030" target = "_blank"><?php echo '<img src="' .plugins_url( 'i/CCbuy.png' , __FILE__ ). '" > ';   ?></a>
	
	</div>
	

</div>

<?php }



function get_meta_count( $key = '', $type = 'post', $status = 'publish' ) {
    global $wpdb;
    // Example code only
    // Good idea to add your own arg checking here
    if( empty( $key ) )
        return;
    $r = $wpdb->get_var( $wpdb->prepare( "
        SELECT COUNT(*) as count FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = '%s'
        AND p.post_status != '%s'
        AND p.post_type = '%s'
    ", $key, $status, $type ) );
    return $r;
}







#} Options HTML
function picsmash_html_config(){
        
    global $wpdb, $picsmash_db_version, $picsmash_t, $picsmash_urls, $picsmash_slugs;   #} Req
    
?>
    

<div class="postbox">
     <h3><label>Support</label></h3>
    <div class="inside">
        <p>If you are struggling to use the plugin please check the <a href="<?php echo $picsmash_urls['docs']; ?>" target="_blank">Pic Smash Support Manual</a>. If you are still struggling do not hesitate to contact us.
         We will try and respond to all support requests within 24 to 48 hours.</p>
    </div>
</div><?php



}




#} Outputs HTML message
function picsmash_html_msg($flag,$msg,$includeExclaim=false){
    
    if ($includeExclaim){ $msg = '<div id="sgExclaim">!</div>'.$msg.''; }
    
    if ($flag == -1){
        echo '<div class="sgfail wrap">'.$msg.'</div>';
    }
    if ($flag == 0){ ?>
        <div id="message" class="updated fade below-h2"><p><strong>Settings saved!</strong></p></div>
    <?php }
    if ($flag == 1){
        echo '<div class="sgwarn wrap">'.$msg.'</div>';
    }
    if ($flag == 2){
        echo '<div class="sginfo wrap">'.$msg.'</div>';
    }
    if ($flag == 666){ ?>
        <div id="message" class="updated fade below-h2"><p><strong><?php echo $msg; ?>!</strong></p></div>
    <?php }
}




function PicSmashLoop( $atts ) {
	extract( shortcode_atts( array(
		'author' => 'true',
		'cats' => 'true',
		'gallery' => 'true',
	), $atts ) );
    
    global $wp_query,$paged,$post,$wp,$wpdb;
	
    $content = null;
    
    ob_start();
    echo "<br/>";
    //get the variables
    if(isset($_POST['win']) && isset($_POST['lose'])){
    $winner = $_POST['win'];
    $loser = $_POST['lose'];
    }

     if (isset($winner)) {
    
     $winner_score = get_score($winner);
     $loser_score = get_score($loser);
     
     $winner_expected = expected($loser_score, $winner_score);
     $winner_new_score = win($winner_score, $winner_expected);
    
     $loser_expected = expected($winner_score, $loser_score);
     $loser_new_score = loss($loser_score, $loser_expected);
    
    update_score($winner,$winner_new_score);
    update_score($loser, $loser_new_score);
    update_wins($winner);
    update_losses($loser);
    
    
    
    
    }
        
    
    ?>

    <div class = 'picsmashvoting2' style = "overflow:hidden">
    <?php $i = 0;
	
	$query = "SELECT * FROM $wpdb->posts WHERE post_type = 'picsmash' AND post_status = 'publish' ORDER BY RAND() LIMIT 0,2";
	$pictures = $wpdb->get_results($query);

    
        $i=0;

        	foreach($pictures as $picture){
	        $thumb[$i] = get_post_meta($picture->ID,'img_full',true);
	        
	        $img[$i] = "<img src = '$thumb[$i]'  alt = '' class = 'noLightbox' id ='facemash$i'/>";
	        $postid[$i] = $picture->ID;
	        $rating[$i] = get_post_meta($picture->ID,'rating',true);
	        $votes[$i] = get_post_meta($picture->ID,'wins',true);
	        $losses[$i] = get_post_meta($picture->ID,'losses',true);
	        
	        $i=$i+1;
	        }
        

    $ShowS = get_option('ShowScore','yes','','yes');
        
    ?>
    <div id = 'picsmash'>
        <div id = 'picleft'>
            
            
            <form action = '<?php the_permalink() ?>' method = 'post'>
                <input type="hidden" name = "win" value = <?php echo $postid[0];?> />
                <input type="hidden" name = "lose" value = <?php echo $postid[1];?> />
                <input type="image" src="<?php echo $thumb[0];?>" alt="Submit button" style = "width:100%">
            </form>

            
            <br/>
            <div class = 'scores'>
            
            Rating: <?php echo number_format($rating[0],0); ?>
            <br/>
            Wins: <?php echo number_format($votes[0],0); ?>
            <br/>
            Losses: <?php echo number_format($losses[0],0); ?>
            
            <?php 
            $link = get_permalink($postid[0]);
             ?>
           
            </div>
        </div>
        
        <div id = 'OR'><h3>OR</h3></div>
        
        <div id = 'picright'>
        
            <form action = '<?php the_permalink() ?>' method = 'post'>
                <input type="hidden" name = "win" value = <?php echo $postid[1];?> />
                <input type="hidden" name = "lose" value = <?php echo $postid[0];?> />
                <input type="image" src="<?php echo $thumb[1];?>" alt="Submit button" style = "width:100%">
            </form>
            
            <br/>
            <div class = 'scores'>
 
            
            Rating: <?php echo number_format($rating[1],0); ?>
            <br/>
            Wins:    <?php echo number_format($votes[1],0); ?>
            <br/>
            Losses: <?php echo number_format($losses[1],0); ?>
            <?php 
            $link = get_permalink($postid[1]);
             ?>
         
            </div>
            
        </div>
        
       
        
    </div>
    
   </div>
  
    
    <?php
   
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}
add_shortcode("picsmash", "PicSmashLoop");

add_filter( 'manage_edit-picsmash_columns', 'my_edit_picsmash_columns' ) ;

function my_edit_picsmash_columns( $columns ) {

    $columns = array(
        'cb' => '<input type="checkbox" />',	
        'image' => __( 'Image' ),
        'rating' => __( 'Rating' ),
        'wins' => __( 'Wins' ),
        'losses' => __( 'Losses' ),
        'date' => __( 'Date' )
    );

    return $columns;
}

add_action( 'manage_picsmash_posts_custom_column', 'my_manage_picsmash_columns', 10, 2 );

function my_manage_picsmash_columns( $column, $post_id ) {
    global $post;

    switch( $column ) {

        /* If displaying the 'rating' column. */
            case 'image' :

            /* Get the post meta. */
            $img = get_post_meta($post_id,'img_full',true);
            echo "<img src = '$img' height = 80px width = 80px />";

            break;
        
        
        case 'rating' :

            /* Get the post meta. */
            echo number_format(get_post_meta( $post_id, 'rating', true ),0);

            break;

        /* If displaying the 'genre' column. */
        case 'wins' :
            
            echo number_format(get_post_meta( $post_id, 'wins', true ),0);
            

            break;
        
        case 'losses' :
            
            echo number_format(get_post_meta( $post_id, 'losses', true ),0);
            
            break;

        /* Just break out of the switch statement for everything else. */
default:
            break;
    }
}

add_filter( 'manage_edit-picsmash_sortable_columns', 'my_picsmash_sortable_columns' );

function my_picsmash_sortable_columns( $columns ) {

    $columns['rating'] = 'rating';
    $columns['wins'] = 'wins';
    $columns['losses'] = 'losses';
    

    return $columns;
}


/* Only run our customization on the 'edit.php' page in the admin. */
add_action( 'load-edit.php', 'my_edit_picsmash_load' );

function my_edit_picsmash_load() {
    add_filter( 'request', 'my_sort_picsmash' );
}

/* Sorts the pics. */
function my_sort_picsmash( $vars ) {

    /* Check if we're viewing the 'picsmash' post type. */
    if ( isset( $vars['post_type'] ) && 'picsmash' == $vars['post_type'] ) {

        /* Check if 'orderby' is set to 'rating'. */
        if ( isset( $vars['orderby'] ) && 'rating' == $vars['orderby'] ) {

            /* Merge the query vars with our custom variables. */
            $vars = array_merge(
                $vars,
                array(
                    'meta_key' => 'rating',
                    'orderby' => 'meta_value_num'
                )
            );
        }
        
        if ( isset( $vars['orderby'] ) && 'wins' == $vars['orderby'] ) {

            /* Merge the query vars with our custom variables. */
            $vars = array_merge(
                $vars,
                array(
                    'meta_key' => 'wins',
                    'orderby' => 'meta_value_num'
                )
            );
        }
        
            if ( isset( $vars['orderby'] ) && 'losses' == $vars['orderby'] ) {

            /* Merge the query vars with our custom variables. */
            $vars = array_merge(
                $vars,
                array(
                    'meta_key' => 'losses',
                    'orderby' => 'meta_value_num'
                )
            );
        }
    }

    return $vars;
}





//code for the top rated and bottom rated
function topratedpic( $atts ) {
	extract( shortcode_atts( array(
		'author' => 'true',
		'cats' => 'true',
		'gallery' => 'true',
	), $atts ) );
    
	global $wp_query,$paged,$post,$wp,$wpdb;

    $content = null;
    
    ob_start();
    echo "<br/>";
    
    
    ?>

	<div class = 'picsmashvoting'>
	<?php $i = 0;
	$post_status = "'publish'";
	$post_type = "'picsmash'";
	
	$query = 	 "SELECT $wpdb->posts.* FROM $wpdb->posts 
				  INNER JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID
				  WHERE 1=1
				  AND $wpdb->posts.post_type = $post_type
				  AND $wpdb->posts.post_status = $post_status
				  AND $wpdb->postmeta.meta_key = 'rating'
				  GROUP BY $wpdb->posts.ID
				  ORDER BY $wpdb->postmeta.meta_value DESC LIMIT 100";

	$pictures = $wpdb->get_results($query);

        $i=0;
        foreach($pictures as $picture){
        $thumb[$i] = get_post_meta($picture->ID,'img_full',true);
        
        $img[$i] = "<img src = '$thumb[$i]'  alt = '' id ='facemash$i'/>";
        $postid[$i] = $picture->ID;
        $rating[$i] = get_post_meta($picture->ID,'rating',true);
        $votes[$i] = get_post_meta($picture->ID,'wins',true);
        $losses[$i] = get_post_meta($picture->ID,'losses',true);
		
		echo "<div class = 'picrated'>";
        $title = get_the_title();
        $img[$i] = "<img src = '$thumb[$i]' alt = '' class = 'rated' id ='facemash$i' title = '$title' width = '100%' />";

        $a = "<a href = '$thumb[$i]'>";
        echo $a.$img[$i]."</a>";
        echo "</div>";
        $i=$i+1;

        }

wp_reset_query();  // Restore global post data stomped by the_post().
?>


</div>
    
    <?php
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}
add_shortcode("toprated", "topratedpic");

#} Send registration info to my server
function picsmash_sendReg($e='',$na='',$pl=''){

			global $picsmash_urls, $picsmash_version;	
			if( function_exists('curl_init') ) { 
					$postData = array('ori'=>get_site_url());
					$postData['e'] = $e; //email
					$postData['na'] = $na;  //name
					$postData['pl'] = $pl;  //plugin

					
					$fields = ''; foreach($postData as $key => $value) $fields .= $key . '=' . $value . '&'; rtrim($fields, '&');
					$ch = curl_init($picsmash_urls['regCheck']);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_POST, count($postData));
					curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
					curl_setopt($ch, CURLOPT_TIMEOUT, 10);
					$regDets = curl_exec($ch);
					
					curl_close($ch);
					return $regDets;
			} # else, cry?			
			return  $false;
}





function bottomratedpic( $atts ) {
	extract( shortcode_atts( array(
		'author' => 'true',
		'cats' => 'true',
		'gallery' => 'true',
	), $atts ) );
    
	global $wp_query,$paged,$post,$wp,$wpdb;

    $content = null;
    
    ob_start();
    echo "<br/>";
    
    
    ?>

	<div class = 'picsmashvoting'>
	<?php $i = 0;
	$post_status = "'publish'";
	$post_type = "'picsmash'";
	
	$query = 	 "SELECT $wpdb->posts.* FROM $wpdb->posts 
				  INNER JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID
				  WHERE 1=1
				  AND $wpdb->posts.post_type = $post_type
				  AND $wpdb->posts.post_status = $post_status
				  AND $wpdb->postmeta.meta_key = 'rating'
				  GROUP BY $wpdb->posts.ID
				  ORDER BY $wpdb->postmeta.meta_value ASC LIMIT 100";
	

	$pictures = $wpdb->get_results($query);

        $i=0;
        foreach($pictures as $picture){
        $thumb[$i] = get_post_meta($picture->ID,'img_full',true);
        
        $img[$i] = "<img src = '$thumb[$i]'  alt = '' id ='facemash$i'/>";
        $postid[$i] = $picture->ID;
        $rating[$i] = get_post_meta($picture->ID,'rating',true);
        $votes[$i] = get_post_meta($picture->ID,'wins',true);
        $losses[$i] = get_post_meta($picture->ID,'losses',true);
		
		echo "<div class = 'picrated'>";
        $title = get_the_title();
        $img[$i] = "<img src = '$thumb[$i]' alt = '' class = 'rated' id ='facemash$i' title = '$title' width = '100%' />";

        $a = "<a href = '$thumb[$i]'>";
        echo $a.$img[$i]."</a>";
        echo "</div>";
        $i=$i+1;

        }

wp_reset_query();  // Restore global post data stomped by the_post().
?>


</div>
    
    <?php
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}
add_shortcode("bottomrated", "bottomratedpic");

add_action( 'add_meta_boxes', 'ps_custom_meta_box' );

function ps_custom_meta_box() {
    add_meta_box( 'image link', 'Image', 'ps_rating', 'picsmash', 'normal', 'high' );
}

function ps_rating() {
    global $wp, $wpdb, $post;

    // Noncename needed to verify where the data originated
    echo '<input type="hidden" name="eventmeta_noncename" id="eventmeta_noncename" value="' .
    wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
    // Get the location data if its already been entered
    $img = get_post_meta($post->ID, 'img_full', true);
    $rating = get_post_meta($post->ID, 'rating', true);
    $win = get_post_meta($post->ID, 'wins', true);
    $lose = get_post_meta($post->ID, 'losses', true);
    $gallery = get_post_meta($post->ID, 'gallery',true);
    
    if($rating == 0){
        $rating = 1400;
    }

    // Echo out the field
    ?>
	
	<div id = 'ps_hold_img' style = 'width:100%;overflow:hidden;'>
	<div id = 'ps_img' style = 'width:45%;float:left;padding:2.5%'>
	<?php if($img == false){ ?>

	<?php }else{ ?>
		<img src = "<?php echo $img; ?>" style = "width:100%" />
	<?php } ?>

	</div>

	<div id = 'ps_img' style = 'width:45%;float:right;padding:2.5%'>
	<?php if($img == false){ ?>

	<?php }else{ ?>
		Rating: <?php echo number_format($rating,0); ?> <br/>
		Wins: <?php echo number_format($win,0); ?> <br/>
		Losses: <?php echo number_format($lose,0); ?> <br/>
	<?php } ?>

	</div>
	</div>
	<?php if($img == false){ ?>
        <tr valign="top">
        <th scope="row">Upload Image</th>
        <td><label for="upload_image">
        <input id="upload_image" type="text" size="36" name="upload_image" value="<?php echo $img; ?>" />
        <input id="upload_image_button" type="button" value="Upload Image" />
        <br />Enter an URL or upload an image to include in pics mash.
        </label></td>
        </tr>
	<?php }else{ ?>
	<tr valign="top">
        <th scope="row">Uploaded Image</th>
        <td><label for="upload_image">
	<input id="upload_image" type="hidden" size="36" name="upload_image" value="<?php echo $img; ?>" />
        <div><b> <?php echo $img; ?></div>
        <br />
        </label></td>
        </tr>
	<?php } ?>
	
	
<?php
    echo '<input type="hidden" name="rating" value="' . $rating  . '" class="widefat" />';
    echo '<input type="hidden" name="wins" value="' . $win  . '" class="widefat" />';
    echo '<input type="hidden" name="losses" value="' . $lose  . '" class="widefat" />';


}


function ps_get_attachment_id_from_src ($image_src) {

		global $wpdb;
		$query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";
		$id = $wpdb->get_var($query);
		return $id;

	}





// Save the Metabox Data
function wpt_save_ps_meta($post_id, $post) {

     global $wp, $wpdb;  

    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if( !isset($_POST['eventmeta_noncename'])){
     return $post->ID;
    }
    if (  !wp_verify_nonce( $_POST['eventmeta_noncename'], plugin_basename(__FILE__) )) {
    return $post->ID;
    }
    
    // Is the user allowed to edit the post or page?
    if ( !current_user_can( 'edit_post', $post->ID ))
        return $post->ID;
    // OK, we're authenticated: we need to find and save the data
    // We'll put it into an array to make it easier to loop though.
    $img_full		 	= $_POST['upload_image'];
    $rating				= $_POST['rating'];
    $wins	 			= $_POST['wins'];
    $losses	 			= $_POST['losses'];


    $theID = ps_get_attachment_id_from_src ($img_full);

    
    update_post_meta($post->ID, 'img_full', $img_full);
    update_post_meta($post->ID, 'rating', $rating);
    update_post_meta($post->ID, 'wins', $wins);
    update_post_meta($post->ID, 'losses', $losses);
    update_post_meta($theID, 'picsmash', 1);

    

}
add_action('save_post', 'wpt_save_ps_meta', 1, 2); // save the custom fields

// hide "add new" on wp-admin menu
function hd_add_box() {
  global $submenu;
  unset($submenu['edit.php?post_type=picsmash'][10]);
}

// hide "add new" button on edit page
function hd_add_buttons() {
  global $pagenow;
  if(is_admin()){
	if($pagenow == 'edit.php' && $_GET['post_type'] == 'picsmash'){
		echo '.add-new-h2{display: none;}';
	}
  }
}
add_action('admin_menu', 'hd_add_box');
add_action('admin_head','hd_add_buttons');


