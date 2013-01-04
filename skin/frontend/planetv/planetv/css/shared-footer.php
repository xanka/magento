<?php
// Template Name: Shared Footer
?>

<head>

<link rel="stylesheet" id="DOPTS_JScrollPaneStyle-css" href="http://staging.planet-v.co.uk/wordpress/wp-content/plugins/dopts/libraries/gui/css/jquery.jscrollpane.css?ver=3.4.2" type="text/css" media="all">
<link rel="stylesheet" id="DOPTS_ThumbnailScrollerStyle-css" href="http://staging.planet-v.co.uk/wordpress/wp-content/plugins/dopts/assets/gui/css/jquery.dop.ThumbnailScroller.magento.css?ver=3.4.2" type="text/css" media="all">

<script type="text/javascript" src="http://staging.planet-v.co.uk/wordpress/wp-content/plugins/dopts/assets/js/jquery.dop.ThumbnailScroller.js?ver=3.4.2"></script>
<script type="text/javascript" src="http://staging.planet-v.co.uk/wordpress/wp-content/plugins/dopts/assets/js/dopts-frontend.js?ver=3.4.2"></script>
	
</head>

<div class="footer-container">

<?php echo do_shortcode('[dopts id="1"]'); ?>

        <div class="footer-wrap">
            <div class="footer">
                <h3 class="footer-tag">Find Out More</h3>
   
<div class="footer-lists">


                        		<?php dynamic_sidebar( 'va-footer' ); ?>

                     
                     <?php wp_nav_menu( array(
			'container' => false,
			'theme_location' => 'footer',
			'fallback_cb' => false
		) ); ?>

                    <span id="footer-copyright">Copyright &copy; 2012 Planet V. <a target="_blank" href="http://www.assemblymarketing.co.uk/index.html"> Designed by Assembly Marketing, </a>  | <a class="bookmark" href="#"
                                onclick="javascript:bookmark('<?php echo get_template_directory_uri(); ?>/index.html','Home')">
                                Add to Favourites</a></span>
            </div>
        </div>
    </div>

</div>
