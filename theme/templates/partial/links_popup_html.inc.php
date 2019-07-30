<!-- links -->
<div id="fileLinksModal" class="modal fade custom-width file-links-wrapper" style="z-index: 10000;">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">File Urls</h4>
            </div>

            <div class="modal-body">
				<div class="row">
			
					<div class="col-md-3">
						<div class="modal-icon-left"><img src="<?php echo THEME_IMAGE_PATH; ?>/modal_icons/link.png"/></div>
					</div>
					
					<div class="col-md-9">
						<samp>
							<div id="popupContentUrls" class="link-section" onClick="selectAllText(this);
									return false;"></div>
							<div id="popupContentHTMLCode" class="link-section" style="display: none;" onClick="selectAllText(this);
									return false;"></div>
							<div id="popupContentBBCode" class="link-section" style="display: none;" onClick="selectAllText(this);
									return false;"></div>
						</samp>
					</div>
				</div>
            </div>

            <div class="modal-footer">
                <div class="row">
                    <div class="col-md-8 text-left">
                        <div class="btn-group">
                            <button type="button" class="btn btn-info active" onClick="showLinkSection('popupContentUrls', this);
                                    return false;">File Urls</button>
                            <button type="button" class="btn btn-info" onClick="showLinkSection('popupContentHTMLCode', this);
                                    return false;">HTML Code</button>
                            <button type="button" class="btn btn-info" onClick="showLinkSection('popupContentBBCode', this);
                                    return false;">Forum BBCode</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>