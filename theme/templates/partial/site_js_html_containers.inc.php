<!-- uploader -->
<div id="fileUploadWrapper" class="modal fade file-upload-wrapper">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php
            require_once ( THEME_TEMPLATES_PATH . "/partial/uploader.inc.php" );
            ?>
        </div>
    </div>
</div>

<div id="filePopupContentWrapper" style="display: none;">
    <div id="filePopupContent" class="filePopupContent"></div>
</div>

<div id="filePopupContentWrapperNotice" style="display: none;">
    <div id="filePopupContentNotice" class="filePopupContentNotice"></div>
</div>

<!-- filter modal -->
<div class="modal fade custom-width filter-modal" id="filterModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Search Your Files</h4>
            </div>

            <div class="modal-body">

				<div class="row">
			
					<div class="col-md-3">
						<div class="modal-icon-left"><img src="<?php echo THEME_IMAGE_PATH; ?>/modal_icons/file_search.png"/></div>
					</div>
					
					<div class="col-md-9">
					
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="filterText" class="control-label">Search</label>
									<input type="text" class="form-control" name="filterText" id="filterText" placeholder="Freetext search..." value="<?php echo isset ( $filterText ) ? Validate::prepareOutput ( $filterText ) : ""; ?>">
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label for="filterUploadedDateRange" class="control-label">Upload Date</label>
									<div class="daterange daterange-inline" data-format="MMMM D, YYYY" data-start-date="<?php echo date("F j, Y", strtotime('-30 day')); ?>" data-end-date="<?php echo date("F j, Y"); ?>" data-callback="">
										<i class="entypo-calendar"></i>
										<span>Select range...</span>
									</div>
									<input type="hidden" name="filterUploadedDateRange" id="filterUploadedDateRange" value="" />
								</div>
							</div>
						</div>
					</div>
				</div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-info" onClick="handleTopSearch(null, $('#filterText'), true); return false;">Search <i class="entypo-search"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- add/edit folder -->
<div id="addEditFolderModal" class="modal fade custom-width edit-folder-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            Loading, please wait...
        </div>
    </div>
</div>

<!-- edit file -->
<div id="editFileModal" class="modal fade custom-width edit-file-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            Loading, please wait...
        </div>
    </div>
</div>

<!-- stats -->
<div id="statsModal" class="modal fade custom-width stats-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            Loading, please wait...
        </div>
    </div>
</div>

<?php
// Links html code
require_once ( THEME_TEMPLATES_PATH . "/partial/links_popup_html.inc.php" );
?>

<!-- download folder modal -->
<div id="downloadFolderModal" class="modal fade custom-width custom-width download-folder-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Download Zip File</h4>
            </div>

            <div class="modal-body" style="height: 440px;">
				<div class="row">
					<div class="col-md-3">
						<div class="modal-icon-left"><img src="<?php echo THEME_IMAGE_PATH; ?>/modal_icons/box_download.png"/></div>
					</div>
					
					<div class="col-md-9"></div>
				</div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- view file details modal -->
<div id="fileDetailsModal" class="modal fade custom-width file-details-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            Loading, please wait...
        </div>
    </div>
</div>

<!-- general notice modal -->
<div id="generalModal" class="modal fade custom-width general-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- for full screen images -->
<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
    <!-- Background of PhotoSwipe. It's a separate element as animating opacity is faster than rgba(). -->
    <div class="pswp__bg"></div>
    <!-- Slides wrapper with overflow:hidden. -->
    <div class="pswp__scroll-wrap">
        <!-- Container that holds slides. 
            PhotoSwipe keeps only 3 of them in the DOM to save memory. Don't modify these 3 pswp__item elements, data is added later on. -->
        <div class="pswp__container">
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
        </div>
        <!-- Default (PhotoSwipeUI_Default) interface on top of sliding area. Can be changed. -->
        <div class="pswp__ui pswp__ui--hidden">
            <div class="pswp__top-bar">
                <!--  Controls are self-explanatory. Order can be changed. -->
                <div class="pswp__counter"></div>
                <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
                <button class="pswp__button pswp__button--share" title="Share"></button>
                <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
                <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>

                <!-- element will get class pswp__preloader--active when preloader is running -->
                <div class="pswp__preloader">
                    <div class="pswp__preloader__icn">
                        <div class="pswp__preloader__cut">
                            <div class="pswp__preloader__donut"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                <div class="pswp__share-tooltip"></div> 
            </div>
            <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></button>
            <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></button>
            <div class="pswp__caption">
                <div class="pswp__caption__center"></div>
            </div>
        </div>
    </div>
</div>