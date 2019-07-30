<?php
define ( "MAX_CONCURRENT_THUMBNAIL_REQUESTS", 5 );

$fid = null;
if ( isset ( $_POST [ "fid" ] ) ) {
    $fid = ( int ) $_POST [ "fid" ];
}
?>

<script>
    var fileUrls = [];
    var fileUrlsHtml = [];
    var fileUrlsBBCode = [];
    var fileDeleteHashes = [];
    var fileShortUrls = [];
	var fileNames = [];
	var uploadPreviewQueuePending = [];
	var uploadPreviewQueueProcessing = [];
	var statusFlag = 'pending';
    var lastEle = null;
    var startTime = null;
    var fileToEmail = '';
    var filePassword = '';
    var fileCategory = '';
    var fileFolder = '';
    var uploadComplete = true;
    $(document).ready(function () {
        document.domain = '<?php echo CONFIG_SITE_HOST_URL; ?>';
<?php
    // if php restrictions are lower than permitted, override
    $phpMaxSize = Functions::getPHPMaxUpload ( );
    $maxUploadSizeNonChunking = 0;
    if ( $phpMaxSize < $userMaxUploadSize ) {
        $maxUploadSizeNonChunking = $phpMaxSize;
    }
    ?>
            // figure out if we should use "chunking""
            var maxChunkSize = 0;
            var uploaderMaxSize = <?php echo ( int ) $maxUploadSizeNonChunking; ?>;
    <?php if ( USE_CHUNKED_UPLOADS ) { ?>
                if ( browserXHR2Support ( ) ){
                    maxChunkSize = <?php echo ( Functions::getPHPMaxUpload ( ) > CHUNKED_UPLOAD_SIZE ? CHUNKED_UPLOAD_SIZE : Functions::getPHPMaxUpload ( ) - 5000 /* 5 KB */ ); // in bytes, allow for smaller PHP upload limits  ?>;
                    var uploaderMaxSize = <?php echo $userMaxUploadSize; ?>;
                }
    <?php } ?>

            // Initialize the jQuery File Upload widget:
            $('#fileUpload #uploader').fileupload({
                sequentialUploads: true,
                url: '<?php echo CORE_AJAX_WEB_ROOT; ?>/file_upload_handler.ajax.php',
                maxFileSize: uploaderMaxSize,
                minFileSize: 1,
                formData: {},
				autoUpload: false,
                xhrFields: {
                    withCredentials: true
                },
                getNumberOfFiles: function () {
                    return getTotalRows();
                },
                previewMaxWidth: 160,
                previewMaxHeight: 134,
                previewCrop: true,
                messages: {
                    maxFileSize: 'File is too large',
                    minFileSize: 'File is too small'
                },
                maxChunkSize: maxChunkSize
            })
                    .on('fileuploadadd', function (e, data) {
                        $('#fileUpload #uploader #fileListingWrapper').removeClass('hidden');
                        $('#fileUpload #uploader #initialUploadSection').addClass('hidden');
                        $('#fileUpload #uploader #initialUploadSectionLabel').addClass('hidden');

                        // fix for safari
                        getTotalRows();
                        // end safari fix

                        totalRows = getTotalRows() + 1;
                    })
                    .on('fileuploadstart', function (e, data) {
                        uploadComplete = false;

                        // hide/show sections
                        $('#fileUpload #addFileRow').addClass('hidden');
                        $('#fileUpload #processQueueSection').addClass('hidden');
                        $('#fileUpload #processingQueueSection').removeClass('hidden');

                        // hide cancel icons
                        $('#fileUpload .cancel').hide();
                        $('#fileUpload .cancel').click(function () {
                            return false;
                        });

                        // show faded overlay on images
                        $('#fileUpload .previewOverlay').addClass('faded');

                        // set timer
                        startTime = (new Date()).getTime();
                    })
                    .on('fileuploadstop', function (e, data) {
                        // finished uploading
                        updateTitleWithProgress(100);
                        updateProgessText(100, data.total, data.total);
                        $('#fileUpload #processQueueSection').addClass('hidden');
                        $('#fileUpload #processingQueueSection').addClass('hidden');
                        $('#fileUpload #completedSection').removeClass('hidden');

                        // set all remainging pending icons to failed
                        $('#fileUpload .processingIcon').parent().html('<img src="<?php echo THEME_IMAGE_PATH; ?>/red_error_small.png" width="16" height="16"/>');

                        uploadComplete = true;
                        sendAdditionalOptions();

                        // flag as finished for later on
						statusFlag = 'finished';
						
                        if (typeof (checkShowUploadFinishedWidget) === 'function')
                        {
                            checkShowUploadFinishedWidget();
                        }

						delay(function() {
							$('#hide_modal_btn').click();
						}, 1500);

                        refreshFolderListing ( false );
                    })
                    .on('fileuploadprogressall', function (e, data) {
                        // progress bar
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        $('#progress .progress-bar').css(
                                'width',
                                progress + '%'
                                );

                        // update page title with progress
                        updateTitleWithProgress(progress);
                        updateProgessText(progress, data.loaded, data.total);
                    })
                    .on('fileuploadsend', function (e, data) {
                        // show progress ui elements
                        $(data['context']).find('.previewOverlay .progressText').removeClass('hidden');
                        $(data['context']).find('.previewOverlay .progress').removeClass('hidden');
                    })
                    .on('fileuploadprogress', function (e, data) {
                        // progress bar
                        var progress = parseInt(data.loaded / data.total * 100, 10);

                        // update item progress
                        $(data['context']).find('.previewOverlay .progressText').html(progress + '%');
                        $(data['context']).find('.previewOverlay .progress .progress-bar').css('width', progress + '%');
                    })
                    .on('fileuploaddone', function (e, data) {

                        // hide faded overlay on images
                        $(data['context']).find('.previewOverlay').removeClass('faded');

                        // keep a copy of the urls globally
                        fileUrls.push(data['result'][0]['url']);
                        fileUrlsHtml.push(data['result'][0]['url_html']);
                        fileUrlsBBCode.push(data['result'][0]['url_bbcode']);
                        fileDeleteHashes.push(data['result'][0]['delete_hash']);
                        fileShortUrls.push(data['result'][0]['short_url']);
						fileNames.push(data['result'][0]['name']);

                        var isSuccess = true;
                        if (data['result'][0]['error'] != null)
                        {
                            isSuccess = false;
                        }

                        var html = '';
                        html += '<div class="template-download-img';
                        if (isSuccess == false)
                        {
                            html += ' errorText';
                        }
                        html += '" ';
                        if (isSuccess == true)
                        {
                            html += 'onClick="window.open(\'' + data['result'][0]['url'] + '\'); return false;"';
                        }
						html += ' title="'+data['result'][0]['name']+'"';
                        html += '>';

                        if (isSuccess == true)
                        {
							previewUrl = '<?php echo THEME_IMAGE_PATH; ?>/trans_1x1.gif';
							if(data['result'][0]['success_result_html'].length > 0)
							{
								previewUrl = data['result'][0]['success_result_html'];
							}
							
							html += "<div id='finalThumbWrapper"+data['result'][0]['file_id']+"'></div>";
							queueUploaderPreview('finalThumbWrapper'+data['result'][0]['file_id'], previewUrl, data['result'][0]['file_id']);
                        }
                        else
                        {
                            html += 'Error uploading: ' + data['result'][0]['name'];
                        }
                        html += '</div>';

                        // update screen with success content
                        $(data['context']).replaceWith(html);
						//processUploaderPreviewQueue();
                    })
                    .on('fileuploadfail', function (e, data) {
                        // hand deletes
                        if (data.errorThrown == 'abort')
                        {
                            $(data['context']).remove();
                            return true;
                        }

                        // update screen with error content, ajax issues
                        var html = '';
                        html += '<div class="template-download-img errorText">';
                        html += 'ERROR: There was a server problem when attempting the upload.';
                        html += '</div>';
                        $(data['context'])
                                .replaceWith(html);

                        totalRows = getTotalRows();
                        if (totalRows > 0)
                        {
                            totalRows = totalRows - 1;
                        }
                    });

            // Open download dialogs via iframes,
            // to prevent aborting current uploads:
            $('#fileUpload #uploader #files a:not([target^=_blank])').on('click', function (e) {
                e.preventDefault();
                $('<iframe style="display:none;"></iframe>')
                        .prop('src', this.href)
                        .appendTo('body');
            });

            $('#fileUpload #uploader').bind('fileuploadsubmit', function (e, data) {
                // The example input, doesn't have to be part of the upload form:
                data.formData = {_sessionid: '<?php echo session_id(); ?>', cTracker: '<?php echo md5(microtime()); ?>', maxChunkSize: maxChunkSize, folderId: fileFolder};
            });

        $('.showAdditionalOptionsLink').click(function (e) {
            // show panel
            showAdditionalOptions();

            // prevent background clicks
            e.preventDefault();

            return false;
        });

<?php if ( $fid != null ) { ?>
            saveAdditionalOptions(true);
<?php } ?>
    });
	
	function queueUploaderPreview(thumbWrapperId, previewImageUrl, previewImageId)
	{
		uploadPreviewQueuePending[thumbWrapperId] = [previewImageUrl, previewImageId];
	}
	
	function processUploaderPreviewQueue()
	{
		if(getTotalProcessing() >= <?php echo (int)MAX_CONCURRENT_THUMBNAIL_REQUESTS; ?>)
		{
			return false;
		}
		
		for(i in uploadPreviewQueuePending)
		{
			var filename = $('#'+i).parent().attr('title');
			$('#'+i).html("<img src='"+uploadPreviewQueuePending[i][0]+"' id='finalThumb"+uploadPreviewQueuePending[i][1]+"' onLoad=\"showUploadThumbCheck('finalThumb"+uploadPreviewQueuePending[i][1]+"', "+uploadPreviewQueuePending[i][1]+");\"/><div class='filename'>"+filename+"</div>");
			uploadPreviewQueueProcessing[i] = uploadPreviewQueuePending[i];
			delete uploadPreviewQueuePending[i];
			return false;
		}
	}
	
	function getTotalPending()
	{
		total = 0;
		for(i in uploadPreviewQueuePending)
		{
			total++;
		}
		
		return total;
	}
	
	function getTotalProcessing()
	{
		total = 0;
		for(i in uploadPreviewQueueProcessing)
		{
			total++;
		}
		
		return total;
	}

	function showUploadThumbCheck(thumbId, itemId)
	{
		$('#'+thumbId).after("<div class='image-upload-thumb-check' style='display: none;'><i class='glyphicon glyphicon-ok'></i></div>");
		$('#'+thumbId).parent().find('.image-upload-thumb-check').fadeIn().delay(1000).fadeOut();
		
		// finish uploading
		if(getTotalPending() == 0 && getTotalProcessing() == 0)
		{
			// refresh treeview
			if (typeof (checkShowUploadFinishedWidget) === 'function')
			{
				refreshFolderListing();
			}
		}

		// trigger the next
		delete uploadPreviewQueueProcessing['finalThumbWrapper'+itemId];
		processUploaderPreviewQueue();
	}
	
	function getPreviewExtension(filename)
	{
		fileExtension = filename.substr(filename.lastIndexOf('.')+1);
		if((fileExtension == 'gif') || (fileExtension == 'mng'))
		{
			return 'gif';
		}
		
		return 'jpg';
	}
	
    function setUploadFolderId(folderId)
    {
        if (typeof (folderId != "undefined") && ($.isNumeric(folderId)))
        {
            $('#upload_folder_id').val(folderId);
        }
        else if ($('#nodeId').val() == '-1')
        {
            $('#upload_folder_id').val('');
        }
        else if ($.isNumeric($('#nodeId').val()))
        {
            $('#upload_folder_id').val($('#nodeId').val());
        }
        else
        {
            $('#upload_folder_id').val('');
        }
        saveAdditionalOptions(true);
    }

    function getSelectedFolderId()
    {
        return $('#upload_folder_id').val();
    }

    function updateProgessText(progress, uploadedBytes, totalBytes)
    {
        // calculate speed & time left
        nowTime = (new Date()).getTime();
        loadTime = (nowTime - startTime);
        if (loadTime == 0)
        {
            loadTime = 1;
        }
        loadTimeInSec = loadTime / 1000;
        bytesPerSec = uploadedBytes / loadTimeInSec;

        textContent = '';
        textContent += 'Progress: ' + progress + '%';
        textContent += ' ';
        textContent += '(' + bytesToSize(uploadedBytes, 2) + ' / ' + bytesToSize(totalBytes, 2) + ')';

        $("#fileupload-progresstextLeft").html(textContent);

        rightTextContent = '';
        rightTextContent += 'Speed: ' + bytesToSize(bytesPerSec, 2) + 'ps. ';
        rightTextContent += 'Remaining: ' + humanReadableTime((totalBytes / bytesPerSec) - (uploadedBytes / bytesPerSec));

        $("#fileupload-progresstextRight").html(rightTextContent);

        // progress widget for file manager
        if (typeof (updateProgressWidgetText) === 'function')
        {
            updateProgressWidgetText(textContent);
        }
    }

    function getUrlsAsText()
    {
        urlStr = '';
        for (var i = 0; i < fileUrls.length; i++)
        {
            urlStr += fileUrls[i] + "\n";
        }

        return urlStr;
    }

    function viewFileLinksPopup()
    {
        fileUrlText = '';
        htmlUrlText = '';
        bbCodeUrlText = '';
        if (fileUrls.length > 0)
        {
            for (var i = 0; i < fileUrls.length; i++)
            {
                fileUrlText += fileUrls[i] + "<br/>";
                htmlUrlText += fileUrlsHtml[i] + "&lt;br/&gt;<br/>";
				bbCodeUrlText += '[URL='+fileUrls[i]+'][/URL]<br/>';
            }
        }

        $('#popupContentUrls').html(fileUrlText);
        $('#popupContentHTMLCode').html(htmlUrlText);
        $('#popupContentBBCode').html(bbCodeUrlText);

        jQuery('#fileLinksModal').modal('show', {backdrop: 'static'}).on('shown.bs.modal');
    }

    function showLinkSection(sectionId, ele)
    {
        $('.link-section').hide();
        $('#' + sectionId).show();
        $(ele).parent().children('.active').removeClass('active');
        $(ele).addClass('active');
        $('.file-links-wrapper .modal-header .modal-title').html($(ele).html());
    }

    function selectAllText(el)
    {
        if (typeof window.getSelection != "undefined" && typeof document.createRange != "undefined")
        {
            var range = document.createRange();
            range.selectNodeContents(el);
            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        }
        else if (typeof document.selection != "undefined" && typeof document.body.createTextRange != "undefined")
        {
            var textRange = document.body.createTextRange();
            textRange.moveToElementText(el);
            textRange.select();
        }
    }

    function updateTitleWithProgress(progress)
    {
        if (typeof (progress) == "undefined")
        {
            var progress = 0;
        }
        if (progress == 0)
        {
            $(document).attr("title", "<?php echo PAGE_NAME; ?> - <?php echo CONFIG_SITE_NAME; ?>");
		}
		else
		{
			$(document).attr("title", progress + "% Uploaded - <?php echo PAGE_NAME; ?> - <?php echo CONFIG_SITE_NAME; ?>");
		}
	}

	function getTotalRows()
	{
		totalRows = $('#files .template-upload').length;
		if (typeof (totalRows) == "undefined")
		{
			return 0;
		}

		return totalRows;
	}

	function showAdditionalInformation(ele)
	{
		// block parent clicks from being processed on additional information
		$('.sliderContent table').unbind();
		$('.sliderContent table').click(function (e) {
			e.stopPropagation();
		});

		// make sure we've clicked on a new element
		if (lastEle == ele)
		{
			// close any open sliders
			$('.sliderContent').slideUp('fast');
			// remove row highlighting
			$('.sliderContent').parent().parent().removeClass('rowSelected');
			lastEle = null;
			return false;
		}
		lastEle = ele;

		// close any open sliders
		$('.sliderContent').slideUp('fast');

		// remove row highlighting
		$('.sliderContent').parent().parent().removeClass('rowSelected');

		// select row and popup content
		$(ele).addClass('rowSelected');

		// set the position of the sliderContent div
		$(ele).find('.sliderContent').css('left', 0);
		$(ele).find('.sliderContent').css('top', ($(ele).offset().top + $(ele).height()) - $('.file-upload-wrapper .modal-content').offset().top);
		$(ele).find('.sliderContent').slideDown(400, function () {
		});

		return false;
	}

	function saveFileToFolder(ele)
	{
		// get variables
		shortUrl = $(ele).closest('.sliderContent').children('.shortUrlHidden').val();
		folderId = $(ele).val();

		// send ajax request
		var request = $.ajax({
			url: "<?php echo CORE_AJAX_WEB_ROOT; ?>/update_folder.ajax.php",
			type: "POST",
			data: {shortUrl: shortUrl, folderId: folderId},
			dataType: "html"
		});
	}

	function showAdditionalOptions(hide)
	{
		if (typeof (hide) == "undefined")
		{
			hide = false;
		}

		if (($('#additionalOptionsWrapper').is(":visible")) || (hide == true))
		{
			$('#additionalOptionsWrapper').slideUp();
		}
		else
		{
			$('#additionalOptionsWrapper').slideDown();
		}
	}

	function saveAdditionalOptions(hide)
	{
		if (typeof (hide) == "undefined")
		{
			hide = false;
		}

		// save values globally
		fileFolder = $('#upload_folder_id').val();

		// attempt ajax to save
		processAddtionalOptions();

		// hide
		showAdditionalOptions(hide);
	}

	function processAddtionalOptions()
	{
		// make sure the uploads have completed
		if (uploadComplete == false)
		{
			return false;
		}

		return sendAdditionalOptions();
        return false;
	}

	function sendAdditionalOptions()
	{
		// make sure we have some urls
		if (fileDeleteHashes.length == 0)
		{
			return false;
		}

		$.ajax({
			type: "POST",
			url: "<?php echo WEB_ROOT; ?>/ajax/update_file_options.ajax.php",
			data: {fileToEmail: fileToEmail, filePassword: filePassword, fileCategory: fileCategory, fileDeleteHashes: fileDeleteHashes, fileShortUrls: fileShortUrls, fileFolder: fileFolder}
		}).done(function (msg) {
			originalFolder = fileFolder;
			if(originalFolder == '')
			{
				originalFolder = '-1';
			}
			fileToEmail = '';
			filePassword = '';
			fileCategory = '';
			fileFolder = '';
			fileDeleteHashes = [];
			if (typeof updateStatsViaAjax === "function")
			{
				//updateStatsViaAjax();
			}
			if (typeof refreshFileListing === "function")
			{
				//refreshFileListing();
				loadImages(originalFolder);
			}

		});
	}
</script>
