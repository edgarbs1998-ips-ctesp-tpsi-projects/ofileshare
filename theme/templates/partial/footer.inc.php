</div>

<?php
// Include page html structure for popups, uploader, etc
include_once ( THEME_TEMPLATES_PATH . "/partial/site_js_html_containers.inc.php" );
?>

<!-- bottom scripts -->
<script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/gsap/main-gsap.js"></script>
<script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/bootstrap.js"></script>
<script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/joinable.js"></script>
<script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/resizeable.js"></script>
<script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/cloudable-api.js"></script>
<script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/toastr.js"></script>
<script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/custom.js"></script>
<script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/handlebars.js"></script>
<script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/typeahead.bundle.js"></script>
<script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/clipboardjs/clipboard.min.js"></script>
<script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/file-manager-gallery/jquery.wookmark.js" type="text/javascript"></script>

<div class="clipboard-placeholder-wrapper">
	<button id="clipboard-placeholder-btn" type="button" data-clipboard-action="copy" data-clipboard-target="#clipboard-placeholder"></button>
	<div id="clipboard-placeholder"></div>
</div>

</body>
</html>