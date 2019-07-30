<link rel="stylesheet" href="<?php echo THEME_CSS_PATH; ?>/file_browser_sprite_48px.css" type="text/css" charset="utf-8" />

<script type="text/javascript">
    var cur = -1, prv = -1;
    var pageStart = 0;
    var perPage = 30;
    var fileId = 0;
    var intialLoad = true;
    var uploaderShown = false;
    var fromFilterModal = false;
    var doubleClickTimeout = null;
    var backgroundFolderLoading = false;
	var clipboard = null;
	var triggerTreeviewLoad = true;

    $(function () {
        // initial button state
        updateFileActionButtons();

        <?php if ( defined ( "_INT_FILE_ID" ) ) { ?>
            showFileInformation(<?php echo ( int ) _INT_FILE_ID; ?>);
            backgroundFolderLoading = true;
        <?php } ?>

        // load folder listing
        $("#folderTreeview").jstree({
            "plugins": [
                "themes", "json_data", "ui", "types", "crrm", "contextmenu", "cookies"
            ],
            "themes": {
                "theme": "default",
                "dots": false,
                "icons": true
            },
            "core": {"animation": 150},
            "json_data": {
                "data": [
                    {
                        "data": "File Manager",
                        "attr": {"id": "-1", "rel": "home", "original-text": "File Manager"}
                    },
                    {
                        "data": "Recent Files",
                        "attr": {"id": "recent", "rel": "recent", "original-text": "Recent Files"}
                    },
                    {
                        "data": "All Files <?php echo ( $totalActive > 0 ) ? '(' . $totalActive . ')' : ''; ?>",
                        "attr": {"id": "all", "rel": "all", "original-text": "All Files"}
                    },
                    {
                        "data": "Trash Can <?php echo ( $totalTrash > 0 ) ? '(' . $totalTrash . ')' : ''; ?>",
                        "attr": {"id": "trash", "rel": "bin", "original-text": "Trash Can"}
                    }
                ],
            },
            "contextmenu": {
                "items": buildTreeViewContextMenu
            },
            'progressive_render': true
        }).bind("select_node.jstree", function (event, data) {
			// use this to stop the treeview from triggering a reload of the file manager
			if(triggerTreeviewLoad == false)
			{
				triggerTreeviewLoad = true;
				return false;
			}
            // add a slight delay encase this is a double click
            if (intialLoad == false)
            {
                // wait before loading the files, just encase this is a double click
                clickTreeviewNode(event, data);

                return false;
            }

            clickTreeviewNode(event, data);
        }).bind("load_node.jstree", function (event, data) {
			reSelectFolder();
        }).bind("open_node.jstree", function (event, data) {
            // reassign drag crop for sub-folder
            setupTreeviewDropTarget();
        }).delegate("a", "click", function (event, data) {
            event.preventDefault();
        }).bind('loaded.jstree', function (e, data) {
            // load default view if not stored in cookie
            var doIntial = true;
            if (typeof ($.cookie("jstree_open")) != "undefined")
            {
                if ($.cookie("jstree_open").length > 0)
                {
                    doIntial = false;
                }
            }

            if (doIntial == true)
            {
                $("#folderTreeview").jstree("open_node", $("#-1"));
            }

            // reload stats
            updateStatsViaAjax();
        });

        var doIntial = true;
        if (typeof ($.cookie("jstree_select")) != "undefined")
        {
            if ($.cookie("jstree_select").length > 0)
            {
                doIntial = false;
            }
        }
        if (doIntial == true)
        {
            // load file listing
            $('#nodeId').val('-1');
        }

        $('.layer').bind('drop', function (e) {
            // blocks upload popup on internal moves / folder icons
			if($(e.target).hasClass('folderIconLi') == false)
			{
				uploadFiles();
			}
        });

        setupFileDragSelect();
    });

    function clickTreeviewNode(event, data)
    {
        clearSelected();
        clearSearchFilters(false);

        // load via ajax
        if (intialLoad == true)
        {
            intialLoad = false;
        }
        else
        {
            $('#nodeId').val(data.rslt.obj.attr("id"));
            $('#folderIdDropdown').val($('#nodeId').val());
            if (typeof (setUploadFolderId) === 'function')
            {
                setUploadFolderId($('#nodeId').val());
            }
            loadImages(data.rslt.obj.attr("id"));
        }
    }

    function updateFolderDropdownMenuItems()
    {
        // not a sub folder
        if (isPositiveInteger($('#nodeId').val()) == false)
        {
            $('#subFolderOptions').hide();
            $('#topFolderOptions').show();
        }
        // all sub folders / menu options
        else
        {
            $('#topFolderOptions').hide();
            $('#subFolderOptions').show();
        }
    }

    function reloadDragItems()
    {
        $('.fileIconLi')
			.drop("start", function () {
				$(this).removeClass("active");
				if ($(this).hasClass("selected") == false)
				{
					$(this).addClass("active");
				}
			})
			.drop(function (ev, dd) {
				selectFile($(this).attr('fileId'), true);
			})
			.drop("end", function () {
				$(this).removeClass("active");
			});
        $.drop({multi: true});
    }

    function refreshFolderListing(triggerLoad)
    {
		if(typeof(triggerLoad) != "undefined")
		{
			triggerTreeviewLoad = triggerLoad;
		}
		
        $("#folderTreeview").jstree("refresh");
    }

    function buildTreeViewContextMenu(node)
    {
        var items = {
            "Open": {
                "label": "Open Folder",
                "icon": "glyphicon glyphicon-folder-open",
                "separator_after": false,
                "action": function (obj) {
                    loadImages(obj.attr("id"));
                }
            }
        };

        if ($(node).attr("id") == "trash")
        {
            items["Empty"] = {
                    "label": "Empty Trash",
					"icon": "glyphicon glyphicon-trash",
                    "action": function (obj) {
                        confirmEmptyTrash();
                    }
                };
        }
        else if ($(node).attr("id") == "-1")
        {
            items["Upload"] = {
                    "label": "Upload Files",
					"icon": "glyphicon glyphicon-cloud-upload",
                    "separator_after": true,
                    "action": function (obj) {
                        uploadFiles('');
                    }
                };
                
            items["Add"] = {
                    "label": "Add Folder",
					"icon": "glyphicon glyphicon-plus",
                    "action": function (obj) {
                        showAddFolderForm(obj.attr("id"));
                    }
                };
        }
        else if ($.isNumeric($(node).attr('id')))
        {
            items["Upload"] = {
                    "label": "Upload Files",
                    "icon": "glyphicon glyphicon-cloud-upload",
                    "separator_after": true,
                    "action": function (obj) {
                        uploadFiles(obj.attr("id"));
                    }
                };
                
            items["Add"] = {
                    "label": "Add Sub Folder",
                    "icon": "glyphicon glyphicon-plus",
                    "action": function (obj) {
                        showAddFolderForm(obj.attr("id"));
                    }
                };
            items["Edit"] = {
                    "label": "Edit",
                    "icon": "glyphicon glyphicon-pencil",
                    "action": function (obj) {
                        showAddFolderForm(null, obj.attr("id"));
                    }
                };
            items["Delete"] = {
                    "label": "Delete",
                    "icon": "glyphicon glyphicon-remove",
                    "action": function (obj) {
                        confirmRemoveFolder(obj.attr("id"));
                    }
                };

            items["HtmlMenuSection"] = {
                    "label": "<span class='menu-folder-details'><ul><li>Owner: "+$(node).attr('owner')+"</li><li>Size: "+$(node).attr('total_size')+"</li></ul></span>",
                    "separator_before": true,
                    "action": function (obj) {
                        loadImages(obj.attr("id"));
                    }
                };
        }

        return items;
    }

    function confirmRemoveFolder(folderId)
    {
        // only allow actual sub folders
        if (isPositiveInteger(folderId) == false)
        {
            return false;
        }

        if (confirm('Are you sure you want to remove this folder? Any files within the folder will be moved into your default root folder and remain active.'))
        {
            removeFolder(folderId);
        }

        return false;
    }

    function removeFolder(folderId)
    {
        $.ajax({
            dataType: "json",
            url: "<?php echo CORE_AJAX_WEB_ROOT; ?>/account_delete_folder.ajax.php",
            data: {folderId: folderId},
            success: function (data) {
                if (data.error == true)
                {
                    showErrorNotification('Error', data.msg);
                }
                else
                {
                    showSuccessNotification('Success', data.msg);
                    
                    loadImages(data.parent_folder);

                    // refresh treeview
                    refreshFolderListing();
                }
            }
        });
    }

    function confirmEmptyTrash()
    {
        if (confirm('Are you sure you want to empty the trash can? Any statistics and other file information will be permanently deleted.'))
        {
            emptyTrash();
        }

        return false;
    }

    function emptyTrash()
    {
        $.ajax({
            dataType: "json",
            url: "<?php echo CORE_AJAX_WEB_ROOT; ?>/account_empty_trash.ajax.php",
            success: function (data) {
                if (data.error == true)
                {
                    showErrorNotification('Error', data.msg);
                }
                else
                {
                    showSuccessNotification('Success', data.msg);
                    
                    // reload file listing
                    loadFiles();

                    // refresh treeview
                    refreshFolderListing();

                    // reload stats
                    updateStatsViaAjax();
                }
            }
        });
    }

    var hideLoader = false;
    function loadFiles(folderId)
    {
        // get variables
        if (typeof (folderId) == 'undefined')
        {
            folderId = $('#nodeId').val();
        }

        loadImages(folderId);
    }

    function dblClickFile(fileId)
    {

    }
	
	function clearExistingHoverFileItem()
	{
		$('.hoverItem').removeClass('hoverItem');
	}

    function showFileMenu(liEle, clickEvent)
    {
        clickEvent.stopPropagation();
		hideOpenContextMenus();
		
        fileId = $(liEle).attr('fileId');
        downloadUrl = $(liEle).attr('dtfullurl');
        statsUrl = $(liEle).attr('dtstatsurl');
        isDeleted = $(liEle).hasClass('fileDeletedLi');
        fileName = $(liEle).attr('dtfilename');
        extraMenuItems = $(liEle).attr('dtextramenuitems');
         var items = {
            "Stats": {
                "label": "View",
				"icon": "glyphicon glyphicon-zoom-in",
                "action": function (obj) {
                    viewFile(fileId);
                }
            }
        };

        if (isDeleted == false)
        {
            var items = {};

            // replace any items for overwriting (plugins)
            if (extraMenuItems.length > 0)
            {
                items = JSON.parse(extraMenuItems);
                for (i in items)
                {
                    // setup click action on menu item
                    eval("items['" + i + "']['action'] = " + items[i]['action']);
                }
            }
			
			// default menu items
            items["View"] = {
                "label": "View",
				"icon": "glyphicon glyphicon-zoom-in",
                "action": function (obj) {
                    viewFile(fileId);
                }
            };

            items["Download"] = {
                "label": "Download " + fileName,
				"icon": "glyphicon glyphicon-download-alt",
                "separator_after": true,
                "action": function (obj) {
                    openUrl('<?php echo CORE_PAGE_WEB_ROOT; ?>/account_home_direct_download.php?fileId=' + fileId);
                }
            };   

            items["Edit"] = {
                "label": "Edit File Info",
				"icon": "glyphicon glyphicon-pencil",
                "action": function (obj) {
                    showEditFileForm(fileId);
                }
            };

            items["Delete"] = {
                "label": "Delete",
				"icon": "glyphicon glyphicon-remove",
                "separator_after": true,
                "action": function (obj) {
                    selectFile(fileId, true);
                    deleteFiles();
                }
            };
			
			items["Copy"] = {
                "label": "Copy Url to Clipboard",
				"icon": "entypo entypo-clipboard",
				"classname": "fileMenuItem"+fileId,
                "separator_after": true,
                "action": function (obj) {
					selectFile(fileId, true);
					fileUrlText = '';
					for (i in selectedItems)
					{
						fileUrlText += selectedItems[i][3] + "<br/>";
					}
                    $('#clipboard-placeholder').html(fileUrlText);
					copyToClipboard('.fileMenuItem'+fileId);
                }
            };

			items["Select"] = {
                "label": "Select File",
				"icon": "glyphicon glyphicon-check",
                "action": function (obj) {
                    selectFile(fileId, true);
                }
            };

            items["Links"] = {
                "label": "Links",
				"icon": "glyphicon glyphicon-link",
                "action": function (obj) {
                    selectFile(fileId, true);
                    viewFileLinks();
                    // clear selected if only 1
                    if (countSelected() == 1)
                    {
                        clearSelected();
                    }
                }
            };

            // replace any items for overwriting
            for (i in extraMenuItems)
            {
                if (typeof (items[i]) != 'undefined')
                {
                    items[i] = extraMenuItems[i];
                }
            }
        }
        $.vakata.context.show(items, $(liEle), clickEvent.pageX - 15, clickEvent.pageY - 8, liEle);
        return false;
    }

    function showFolderMenu(liEle, clickEvent)
    {
        clickEvent.stopPropagation();
        folderId = $(liEle).attr('folderId');
		var items = {
                "Upload": {
                    "label": "Upload Files",
					"icon": "glyphicon glyphicon-cloud-upload",
                    "separator_after": true,
                    "action": function (obj) {
                        uploadFiles(folderId);
                    }
                },
				"Add": {
                    "label": "Add Sub Folder",
					"icon": "glyphicon glyphicon-plus",
                    "action": function (obj) {
                        showAddFolderForm(folderId);
                    }
                },
                "Edit": {
                    "label": "Edit",
					"icon": "glyphicon glyphicon-pencil",
                    "action": function (obj) {
                        showAddFolderForm(null, folderId);
                    }
                },
                "Delete": {
                    "label": "Delete",
					"icon": "glyphicon glyphicon-remove",
                    "action": function (obj) {
                        confirmRemoveFolder(folderId);
                    }
                },
				"Copy": {
                    "label": "Copy Url to Clipboard",
					"icon": "entypo entypo-clipboard",
					"classname": "folderMenuItem"+folderId,
                    "separator_before": true,
                    "action": function (obj) {
						$('#clipboard-placeholder').html($('#folderItem'+folderId).attr('sharing-url'));
						copyToClipboard('.folderMenuItem'+folderId);
                    }
                }
            };

        $.vakata.context.show(items, $(liEle), clickEvent.pageX - 15, clickEvent.pageY - 8, liEle);
        return false;
    }

    function selectFile(fileId, onlySelectOn)
    {
        if (typeof (onlySelectOn) == "undefined")
        {
            onlySelectOn = false;
        }

        // clear any selected if ctrl key not pressed
        if ((ctrlPressed == false) && (onlySelectOn == false))
        {
            showFileInformation(fileId);

            return false;
        }

        elementId = 'fileItem' + fileId;
        if (($('.' + elementId).hasClass('selected')) && (onlySelectOn == false))
        {
            $('.' + elementId).removeClass('selected');
            if (typeof (selectedItems['k' + fileId]) != 'undefined')
            {
                delete selectedItems['k' + fileId];
            }
        }
        else
        {
            $('.' + elementId + '.owned-image:not(.fileDeletedLi)').addClass('selected');
            if ($('.' + elementId).hasClass('selected'))
            {
                selectedItems['k' + fileId] = [fileId, $('.' + elementId).attr('dttitle'), $('.' + elementId).attr('dtsizeraw'), $('.' + elementId).attr('dtfullurl'), $('.' + elementId).attr('dturlhtmlcode'), $('.' + elementId).attr('dturlbbcode')];
            }
        }

        updateSelectedFilesStatusText();
        updateFileActionButtons();
    }

    var ctrlPressed = false;
    $(window).keydown(function (evt) {
        if (evt.which == 17) {
            ctrlPressed = true;
        }
    }).keyup(function (evt) {
        if (evt.which == 17) {
            ctrlPressed = false;
        }
    });

    $(window).keydown(function (evt) {
        if (evt.which == 65) {
            if (ctrlPressed == true)
            {
                selectAllFiles();
                return false;
            }
        }
    })

    function updateFileActionButtons()
    {
        totalSelected = countSelected();
        if (totalSelected > 0)
        {
            $('.fileActionLinks').removeClass('disabled');

        }
        else
        {
            $('.fileActionLinks').addClass('disabled');
        }
    }

    function viewFileLinks()
    {
        count = countSelected();
        if (count > 0)
        {
            fileUrlText = '';
            htmlUrlText = '';
            bbCodeUrlText = '';
            for (i in selectedItems)
            {
                fileUrlText += selectedItems[i][3] + "<br/>";
                htmlUrlText += selectedItems[i][4] + "&lt;br/&gt;<br/>";
                bbCodeUrlText += '[URL='+selectedItems[i][3]+']'+selectedItems[i][3] + "[/URL]<br/>";
            }

            $('#popupContentUrls').html(fileUrlText);
            $('#popupContentHTMLCode').html(htmlUrlText);
            $('#popupContentBBCode').html(bbCodeUrlText);

            jQuery('#fileLinksModal').modal('show', {backdrop: 'static'}).on('shown.bs.modal');
        }
    }

    function showLightboxNotice()
    {
        jQuery('#generalModal').modal('show', {backdrop: 'static'}).on('shown.bs.modal', function () {
            $('.general-modal .modal-body').html($('#filePopupContentWrapperNotice').html());
        });
    }

    function showFileInformation(fileId)
    {
        // hide any context menus
        hideOpenContextMenus();

        // load overlay
        showFileInline(fileId);
    }

    function loadPage(startPos)
    {
        $('html, body').animate({
            scrollTop: $(".page-body").offset().top
        }, 700);
        pageStart = startPos;
        refreshFileListing();
    }
</script>


<script>
    function showAddFolderForm(parentId, editFolderId)
    {
        // only allow actual sub folders on edit
        if ((typeof (editFolderId) != 'undefined') && (isPositiveInteger(editFolderId) == false))
        {
            return false;
        }

        showLoaderModal();
        if (typeof (parentId) == 'undefined')
        {
            parentId = $('#nodeId').val();
        }

        if (typeof (editFolderId) == 'undefined')
        {
            editFolderId = 0;
        }

        jQuery('#addEditFolderModal .modal-content').load("<?php echo WEB_ROOT; ?>/ajax/account_add_edit_folder.ajax.php", {parentId: parentId, editFolderId: editFolderId}, function () {
            hideLoaderModal();
            jQuery('#addEditFolderModal').modal('show', {backdrop: 'static'}).on('shown.bs.modal', function () {
                $('#addEditFolderModal input').first().focus();
            });
        });
    }

<?php
// load folder structure as array
$folderListing = FileFolder::loadAllForSelect ( $user->id, "|||" );
$folderListingArr = array ( );
foreach ( $folderListing AS $key => $value ) {
    $folderListingArr [ $key ] = Validate::prepareOutput ( $value );
}
$jsArray = json_encode ( $folderListing );
echo "var folderArray = " . $jsArray . ";\n";
?>
    function markInternalNotificationsRead()
    {
        $.ajax({
            dataType: "json",
            url: "<?php echo CORE_AJAX_WEB_ROOT; ?>/account_internal_notification_mark_all_read.ajax.php",
            success: function (data) {
                $('.internal-notification .unread').addClass('read').removeClass('unread');
                $('.internal-notification .text-bold').removeClass('text-bold');
                $('.internal-notification .badge').hide();
                $('.internal-notification .unread-count').html('You have 0 new notifications.');
                $('.internal-notification .mark-read-link').hide();
            }
        });
    }

    progressWidget = null;
    function showProgressWidget(intialText, title, complete, timeout)
    {
		if(typeof(timeout) == "undefined")
		{
			timeout = 0;
		}
		
        if (progressWidget != null)
        {
            progressWidget.hide();
        }

        var opts = {
            "closeButton": false,
            "debug": false,
            "positionClass": "toast-bottom-right",
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": timeout,
            "extendedTimeOut": "0",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut",
            "onclick": function () {
                showUploaderPopup();
            }
        };

        if (complete == true)
        {
            progressWidget = toastr.success(intialText, title, opts);
        }
        else
        {
            progressWidget = toastr.info(intialText, title, opts);
        }
    }

    function updateProgressWidgetText(text)
    {
        if (progressWidget == null)
        {
            return false;
        }

        $(progressWidget).find('.toast-message').html(text);
    }

    function checkShowUploadProgressWidget()
    {
        if (uploadComplete == false)
        {
            showProgressWidget('Uploading...', 'Upload Progress:', false);
        }
    }

    function checkShowUploadFinishedWidget()
    {
        showProgressWidget('Upload complete.', 'Upload Progress:', true, 6000);
    }

    function updateStatsViaAjax()
    {
        // first request stats via ajax
        $.ajax({
            dataType: "json",
            url: "<?php echo CORE_AJAX_WEB_ROOT; ?>/account_get_account_file_stats.ajax.php",
            success: function (data) {
                updateOnScreenStats(data);
            }
        });
    }

    function updateOnScreenStats(data)
    {
        // update list of folders for breadcrumbs
        folderArray = jQuery.parseJSON(data.folderArray);

        // update folder drop-down list in the popup uploader
        $("#folder_id").html(data.folderSelectForUploader);

        // update root folder stats
        if (data.totalRootFiles > 0)
        {
            $("#folderTreeview").jstree('set_text', '#-1', $('#-1').attr('original-text') + ' (' + data.totalRootFiles + ')');
        }
        else
        {
            $("#folderTreeview").jstree('set_text', '#-1', $('#-1').attr('original-text'));
        }

        // update trash folder stats
        if (data.totalTrashFiles > 0)
        {
            $("#folderTreeview").jstree('set_text', '#trash', $('#trash').attr('original-text') + ' (' + data.totalTrashFiles + ')');
        }
        else
        {
            $("#folderTreeview").jstree('set_text', '#trash', $('#trash').attr('original-text'));
        }

        // update all folder stats
        $("#folderTreeview").jstree('set_text', '#all', $('#all').attr('original-text') + ' (' + data.totalActiveFiles + ')');

        // update total storage stats
        $(".remaining-storage .progress .progress-bar").attr('aria-valuenow', data.totalStoragePercentage);
        $(".remaining-storage .progress .progress-bar").width(data.totalStoragePercentage + '%');
        $("#totalActiveFileSize").html(data.totalActiveFileSizeFormatted);
    }

    function isDesktopUser()
    {
        if ((getBrowserWidth() <= 1024) && (getBrowserWidth() > 0))
        {
            return false;
        }

        return true;
    }

    function getBrowserWidth()
    {
        return $(window).width();
    }

    function showFileInline(fileId)
    {
        viewFile(fileId);
    }

    function showImageBrowseSlide(folderId)
    {
        $('#imageBrowseWrapper').show();
        $('#albumBrowseWrapper').hide();
        loadFiles(folderId);
    }

    function handleTopSearch(event, ele, isAdvSearch)
    {
		// make sure we have a default setting for advance search
		if(typeof(isAdvSearch) == 'undefined')
		{
			isAdvSearch = false;
		}
		
		searchText = $(ele).val();
        $('#filterText').val(searchText);

        // check for enter key
		doSearch = false;
		if(event == null)
		{
			doSearch = true;
		}
		else
		{
			var charCode = (typeof event.which === "number") ? event.which : event.keyCode;
			if (charCode == 13)
			{
				doSearch = true;
			}
		}
		
		// do search
		if(doSearch == true)
		{
			// make sure we have something to search
			if(searchText.length == 0)
			{
				showErrorNotification('Error', 'Please enter something to search for.');
				return false;
			}
			
			filterUploadedDateRange = '';
			if(isAdvSearch == true)
			{
				filterUploadedDateRange = $('#filterUploadedDateRange').val();
			}
			
			url = WEB_ROOT+'/search/?filterUploadedDateRange='+filterUploadedDateRange+'&t='+encodeURIComponent(searchText);
			window.location = url;
		}

        return false;
    }

	function setupPostPopup()
	{
		// hover over tooptips
		setupToolTips();
		
		// radios
		replaceCheckboxes();
		
		// block enter key from being pressed
		$('#registeredEmailAddress').keypress(function (e) {
			if (e.which == 13)
			{
				return false;
			}
		});
	}
	
	function shareFolderInternally(folderId)
	{
		setShareFolderButtonLoading();
		$.ajax({
            dataType: "json",
            url: "<?php echo CORE_AJAX_WEB_ROOT; ?>/share_folder_internally.ajax.php",
            data: {folderId: folderId, registeredEmailAddress: $('#registeredEmailAddress').val(), permissionType: $('input[name=permission_radio]:checked').val()},
            success: function (data) {
                if (data.error == true)
                {
                    showErrorNotification('Error', data.msg);
					clearShareFolderButtonLoading();
                }
                else
                {
					$('#registeredEmailAddress').val('');
					loadExistingInternalShareTable(data.folderId);
					clearShareFolderButtonLoading();
					showSuccessNotification('Success', data.msg);
                }
            }
        });
	}
	
	function loadExistingInternalShareTable(folderId)
	{
		$('#existingInternalShareTable').load("<?php echo WEB_ROOT; ?>/ajax/account_existing_internal_share.ajax.php", {folderId: folderId}).hide().fadeIn();
	}
	
	function shareFolderInternallyRemove(folderShareId)
	{
		$.ajax({
            dataType: "json",
            url: "<?php echo CORE_AJAX_WEB_ROOT; ?>/share_folder_internally_remove.ajax.php",
            data: {folderShareId: folderShareId},
            success: function (data) {
                if (data.error == true)
                {
                    showErrorNotification('Error', data.msg);
                }
                else
                {
					loadExistingInternalShareTable(data.folderId);
					showSuccessNotification('Success', data.msg);
                }
            }
        });
	}
	
	function setShareFolderButtonLoading()
	{
		$('#shareFolderInternallyBtn').removeClass('btn-info');
		$('#shareFolderInternallyBtn').addClass('btn-default disabled');
		$('#shareFolderInternallyBtn').html('Processing <i class="entypo-arrows-cw"></i>');
	}
	
	function clearShareFolderButtonLoading()
	{
		$('#shareFolderInternallyBtn').removeClass('btn-default disabled');
		$('#shareFolderInternallyBtn').addClass('btn-info');
		$('#shareFolderInternallyBtn').html('Grant Access" <i class="entypo-lock"></i>');
	}
	
	function copyToClipboard(ele)
	{
		destroyClipboard();
		clipboard = new Clipboard(ele);
		clipboard.on('success', function(e) {
			showSuccessNotification('Success', 'Copied to clipboard.');
			$('#clipboard-placeholder').html('');
		});

		clipboard.on('error', function(e) {
			showErrorNotification('Error', 'Failed copying to clipboard.');
		});
	}
	
	function destroyClipboard()
	{
		if(clipboard != null)
		{
			clipboard.destroy();
		}
	}

	var createdUrl = false;
	function generateFolderSharingUrl(folderId)
	{
		$.ajax({
            dataType: "json",
            url: "<?php echo CORE_AJAX_WEB_ROOT; ?>/generate_folder_sharing_url.ajax.php",
            data: {folderId: folderId},
            success: function (data) {
                if (data.error == true)
                {
                    showErrorNotification('Error', data.msg);
                }
                else
                {
                    $('#sharingUrlInput').html(data.msg);
					$('#shareEmailFolderUrl').html(data.msg);
					$('#nonPublicSharingUrls').fadeIn();
					$('#nonPublicSharingUrls').html($('.social-wrapper-template').html().replace(/SHARE_LINK/g, data.msg));
					createdUrl = true;
                }
            }
        });
	}
</script>
