<?php
/**
 * Markup for Business Reviews widget
 */
?>

<div style="width:50%;float:left;">
	<a href="#" onclick="nfdbr.doFeedback();"><?php echo file_get_contents( plugin_dir_path( dirname( __FILE__ ) ) . 'assets/images/thumbs-down.svg' ); ?><br />Help us improve</a>
</div>
<div style="width:50%;float:left;">
	<a href="#" onclick="nfdbr.doReview();"><?php echo file_get_contents( plugin_dir_path( dirname( __FILE__ ) ) . 'assets/images/thumbs-up.svg' ); ?><br />Share a review</a>
</div>
<!--
* Thumb icons from:
* Font Awesome Free 5.0.13 by @fontawesome - https://fontawesome.com
* License - https://fontawesome.com/license (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License)
-->
<div style="clear:both;"></div>
