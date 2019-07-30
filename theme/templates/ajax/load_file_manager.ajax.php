<?php

// Setup database
$db = Database::getInstance ( );

// Setup response array
$rs = array (
    "html" => "",
    "javascript" => "",
    "page_title" => "",
    "page_url" => "",
);

// Sorting options
$sortingOptions = array (
    "order_by_filename_asc"         => "Filename ASC",
    "order_by_filename_desc"        => "Filename DESC",
    "order_by_uploaded_date_asc"    => "Uploaded Date ASC",
    "order_by_uploaded_date_desc"   => "Uploaded Date DESC",
    "order_by_filesize_asc"         => "Filesize ASC",
    "order_by_filesize_desc"        => "Filesize DESC",
    "order_by_deleted_date_desc"   => "Deleted Date DESC",
);
$defaultSorting = "order_by_filename_asc";

// Items per page options
$perPageOptions = array ( 15, 30, 50, 100, 250 );
$defaultPerPage = 30;

// Initial headers
header ( "Expires: 0" );
header ( "Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0" );
header ( "Pragma: no-cache" );
header ( "Content-type: application/json; charset=utf-8");
http_response_code ( 200 );

// Setup session params
if ( !isset ( $_SESSION [ "search" ] ) ) {
    $_SESSION [ "search" ] = array ( );
}
if ( !isset ( $_SESSION [ "search" ] [ "perPage" ] ) ) {
    $_SESSION [ "search" ] [ "perPage" ] = $defaultPerPage;
}
if ( !isset ( $_SESSION [ "search" ] [ "filterOrderBy" ] ) ) {
    $_SESSION [ "search" ] [ "filterOrderBy" ] = $defaultSorting;
}
if ( !isset ( $_SESSION [ "browse" ] [ "viewType" ] ) ) {
    $_SESSION [ "browse" ] [ "viewType" ] = "fileManagerIcon";
}

// Setup initial params
$pageStart = ( int ) $_POST [ "pageStart" ];
$perPage = ( int ) $_POST [ "perPage" ] > 0 ? ( int ) $_POST [ "perPage" ] : $_SESSION [ "search" ] [ "perPage" ];
$filterOrderBy = ( isset ( $_POST [ "filterOrderBy" ] ) && array_key_exists ( strtolower ( $_POST [ "filterOrderBy" ] ), $sortingOptions ) ) ? strtolower ( $_POST [ "filterOrderBy" ] ) : $_SESSION [ "search" ] [ "filterOrderBy" ];
$searchTerm = isset ( $_POST [ "searchFilter" ] ) ? trim ( $_POST [ "searchFilter" ] ) : "";

// Advanced filters
$advFilters = isset ( $_POST [ "advFilters" ] ) ? $_POST [ "advFilters" ] : array ( );
$filterUploadedDateRange = ( isset ( $advFilters [ "filterUploadedDateRange" ] ) && strlen ( $advFilters [ "filterUploadedDateRange" ] ) ) ? $advFilters [ "filterUploadedDateRange" ] : null;

$searchType = null;
$foldersClause = "WHERE 1 = 1";

if ( isset ( $_POST [ "nodeId" ] ) ) {
    $nodeId = $_POST [ "nodeId" ];
    switch ( $nodeId ) {
        case "recent":
            $searchType = "recent";
            $foldersClause .= " AND 1 = 2"; // disable
            break;
        case "trash":
            $searchType = "trash";
            $foldersClause .= " AND 1 = 2"; // disable
            break;
        case "all":
            $searchType = "all";
            $foldersClause .= " AND 1 = 2"; // disable
            break;
        case "-1":
            $searchType = "root";
            $foldersClause .= " AND fol_parent = ( SELECT fol_id FROM folder WHERE fol_parent IS NULL AND fol_usr_id = " . ( int ) $user->id . " ) AND fol_usr_id = " . ( int ) $user->id;
            break;
        default:
            $searchType = "folder";
            $foldersClause .= " AND fol_parent = " . ( int ) $nodeId;
            break;
    }
} elseif ( isset ( $_POST [ "searchType" ] ) ) {
    if ( in_array ( $_POST [ "searchType" ], array ( "browserecent" ) ) ) {
        $searchType = $_POST [ "searchType" ];
    }
    $foldersClause .= " AND 1 = 2"; // disable
}

$disableFilterButton = false;
$originalFilterOrderBy = $filterOrderBy;
if ( $searchType == "recent" || $searchType == "browserecent" || $searchType == "recent" ) {
    $disableFilterButton = true;
    $filterOrderBy = "order_by_uploaded_date_desc";
} elseif ( $searchType == "trash" ) {
    $disableFilterButton = true;
    $filterOrderBy = "order_by_deleted_date_desc";
}

// Save session params
$_SESSION [ "search" ] [ "perPage" ] = $perPage;
$_SESSION [ "search" ] [ "filterOrderBy" ] = $filterOrderBy;

// Setup paging JS
if ( $searchType == "browserecent" ) {
    $pagingJs = "loadBrowsePageRecentImages ( \"" . str_replace ( array ( "\"", "'", "\\" ), "", $searchTerm ) . "\", ";
    $updatePagingJs = "updateRecentImagesPerPage (";
    $updateSortingJs = "updateRecentImagesSorting (";
} else {
    $pagingJs = "loadImages ( \"" . $nodeId . "\", ";
    $updatePagingJs = "updatePerPage (";
    $updateSortingJs = "updateSorting (";
}

// Setup page title
$pageTitle = "File Manager";
$pageUrl = "";
$folder = null;
$folderId = null;
if ( $searchType == "folder" ) {
    $folder = FileFolder::loadById ( $nodeId );
    if ( $folder ) {
        $pageTitle = $folder->fol_name;
        $pageUrl = $folder->getFolderUrl();
        $folderId = $folder->fol_id;

        if ( !$user->isLogged ( ) || $folder->fol_usr_id != $user->id ) {
            $rs [ "html" ] = "<div class='ajax-error-image'><!-- --></div>";
            $rs [ "javascript" ] = "showErrorNotification ( 'Error', 'You have no access permission to this folder!' );";
            $rs [ "page_title" ] = "Error";
            echo json_encode ( $rs );
            exit ( );
        }
    }
} elseif ( $searchType == "recent" ) {
    $pageTitle = "Recent Files";
    $pageUrl = WEB_ROOT . "/index.html";
} elseif ( $searchType == "root" ) {
    $pageTitle = "File Manager";
    $pageUrl = WEB_ROOT . "/index.html";
} elseif ( $searchType == "all" ) {
    $pageTitle = "All Files";
    $pageUrl = WEB_ROOT . "/index.html";
} elseif ( $searchType == "trash" ) {
    $pageTitle = "Trash Can";
    $pageUrl = WEB_ROOT . "/index.html";
} elseif ( $searchType == "browserecent" ) {
    if ( strlen ( $searchTerm ) ) {
        $pageTitle = "File Search Results";
    } else {
        $rs [ "html" ] = "<div class='ajax-error-image'><!-- --></div>";
        $rs [ "javascript" ] = "showErrorNotification ( 'Error', 'No search criteria submitted!' );";
        $rs [ "page_title" ] = "Error";
        echo json_encode ( $rs );
        exit ( );
    }
}

// Setup files query
$filesClause = "WHERE 1 = 1";
$filesClause .= " AND usr_id = " . ( int ) $user->id;

if ( $searchType == "root" ) {
    $filesClause .= " AND fil_fol_id = ( SELECT fol_id FROM folder WHERE fol_parent IS NULL AND fol_usr_id = " . ( int ) $user->id . " )";
}

if ( $searchType == "folder" ) {
    $filesClause .= " AND fil_fol_id = " . ( int ) $nodeId;
}

if ( $searchType == "recent" ) {
    $filesClause .= " AND fil_upload_date > DATE_SUB(NOW(), INTERVAL 1 MONTH)";
}

if ( $searchType == "trash" ) {
    $filesClause .= " AND fil_trash IS NOT NULL";
} else {
    $filesClause .= " AND fil_trash IS NULL";
}

if ( strlen ( $searchTerm ) ) {
    $filesClause .= " AND ( fil_name LIKE '%" . $db->escape ( $searchTerm ) . "%' OR fil_shorturl LIKE '%" . $db->escape ( $searchTerm ) . "%' )";
}

if ( $filterUploadedDateRange !== null ) {
    $expDate = explode ( "|", $filterUploadedDateRange );
    if ( COUNT ( $expDate ) == 2 ) {
        $startDate = $expDate [ 0 ];
        $endDate = $expDate [ 1 ];
    } else {
        $startDate = $expDate [ 0 ];
        $endDate = $expDate [ 0 ];
    }

    if ( Validate::validDate ( $startDate, "Y-m-d" ) && Validate::validDate ( $endDate, "Y-m-d" ) ) {
        $filesClause .= " AND DATE(fil_upload_date) >= '" . $startDate . "' AND DATE(fil_upload_date) <= '" . $endDate . "'";
    }
}

$sortColName = "fil_name";
$sortDir = "ASC";
switch ( $_SESSION [ "search" ] [ "filterOrderBy" ] ) {
    case "order_by_filename_asc":
        $sortColName = "fil_name";
        $sortDir = "ASC";
        break;
    case "order_by_filename_desc":
        $sortColName = "fil_name";
        $sortDir = "DESC";
        break;
    case "order_by_uploaded_date_asc":
        $sortColName = "fil_upload_date";
        $sortDir = "ASC";
        break;
    case "order_by_uploaded_date_desc":
        $sortColName = "fil_upload_date";
        $sortDir = "DESC";
        break;
    case "order_by_filesize_asc":
        $sortColName = "fil_size";
        $sortDir = "ASC";
        break;
    case "order_by_filesize_desc":
        $sortColName = "fil_trash";
        $sortDir = "DESC";
        break;
}

// Get files/folders counts
$db->query ( "SELECT COUNT(*) AS total_file_count, SUM(fil_size) AS total_file_size FROM file INNER JOIN folder ON fol_id = fil_fol_id INNER JOIN user ON usr_id = fol_usr_id " . $filesClause );
$allStats = $db->getRow ( );
$db->query ( "SELECT COUNT(*) AS total_folder_count FROM folder " . $foldersClause );
$allStatsFolders = $db->getRow ( );

// Load folders
$db->query ( "SELECT fol_id, fol_usr_id, fol_parent, fol_name, ( SELECT COUNT(*) FROM file WHERE fil_fol_id = fol_id AND fil_trash IS NULL ) AS file_count FROM folder " . $foldersClause . " ORDER BY fol_name ASC LIMIT " . ( ( $pageStart - 1 ) * ( int ) $_SESSION [ "search" ] [ "perPage" ] ) . ", " . ( int ) $_SESSION [ "search" ] [ "perPage" ] );
$folders = $db->getRows ( );

// Calculate folders for paging
$newStart = floor ( ( ( $pageStart - 1 ) * ( int ) $_SESSION [ "search" ] [ "perPage" ] ) - $allStatsFolders->total_folder_count );
if ( $newStart < 0 ) {
    $newStart = 0;
}
$newLimit = $_SESSION [ "search" ] [ "perPage" ] - COUNT ( $folders );
$fileLimit = " LIMIT " . $newStart . ", " . $newLimit;

// Load files
$db->query ( "SELECT usr_id, usr_username, fil_id, fil_fpe_id, fil_fol_id, fil_shorturl, fil_name, fil_size, fil_type, fil_extension, fil_upload_ip, fil_upload_date, fil_trash FROM file INNER JOIN folder ON fol_id = fil_fol_id INNER JOIN user ON usr_id = fol_usr_id " . $filesClause . " ORDER BY " . $sortColName . " " . $sortDir . " " . $fileLimit );
$files = $db->getRows ( );

// Setup HTML output
$totalText = "";
if ( ( int ) $allStats->total_file_count > 0 ) {
    $totalText = " - " . $allStats->total_file_count . " files (" . Functions::formatSize ( $allStats->total_file_size ) . ")";
}

$breadcrumbs = array ( );
if ( $user->isLogged ( ) ) {
    $breadcrumbs [ ] = "<a href='#' onClick='loadImages ( -1, 1 ); return false;' class='btn btn-white mid-item'><i class='glyphicon glyphicon-home'></i></a>";
} else {
    $breadcrumbs [ ] = "<a href='#' class='btn btn-white mid-item'><i class='glyphicon glyphicon-folder-open'></i></a>";
}

if ( $searchType == "browserecent" ) {
    $breadcrumbs [ ] = "<a href=#' onClick='loadBrowsePageRecentImages ( \"" . str_replace ( array ( "\"", "'", "\\" ), "", $searchTerm ) . "\" ); return false;' class='btn btn-white mid-item'>" . Validate::prepareOutput ( $pageTitle ) . $totalText . "</a>";
} elseif ( $searchType == "folder" ) {
    $dropdownMenu = "";
    if ( $folder != null ) {
        $dropdownMenu .= "<ul role='menu' class='dropdown-menu dropdown-white pull-left'>";
        $dropdownMenu .= "<li><a href='#' onClick='uploadFiles ( " . ( int ) $folder->fol_id . " );'><span class='context-menu-icon'><span class='glyphicon glyphicon-cloud-upload'></span></span>Upload Files</a></li>";
        $dropdownMenu .= "<li class='divider'></li>";
        $dropdownMenu .= "<li><a href='#' onClick='showAddFolderForm ( " . ( int ) $folder->fol_id . " );'><span class='context-menu-icon'><span class='glyphicon glyphicon-plus'></span></span>Add Sub Folder</a></li>";
        $dropdownMenu .= "<li><a href='#' onClick='showAddFolderForm ( null, " . ( int ) $folder->fol_id . " );'><span class='context-menu-icon'><span class='glyphicon glyphicon-pencil'></span></span>Edit</a></li>";
        $dropdownMenu .= "<li><a href='#' onClick='confirmRemoveFolder ( " . ( int ) $folder->fol_id . " );'><span class='context-menu-icon'><span class='glyphicon glyphicon-remove'></span></span>Delete</a></li>";
        $dropdownMenu .= "<li class='divider'></li>";
        $dropdownMenu .= "<li><a href='#' onClick='selectAllFiles ( );'><span class='context-menu-icon'><span class='glyphicon glyphicon-check'></span></span>Select All Files</a></li>";
        $dropdownMenu .= "<li><a href='#' onClick='clearSelected ( );'><span class='context-menu-icon'><span class='glyphicon glyphicon-unchecked'></span></span>Clear Selected</a></li>";
        $dropdownMenu .= "</ul>";

        $localFolder = $folder;
        $localBreadcrumbs = array ( );
        $first = true;
        while ( $localFolder != false ) {
            if ( !$user->isLogged ( ) || $folder == null ) {
                $first = false;
            }

            $parentId = $localFolder->fol_parent;
            $localBreadcrumbs [ ] = "<a href='#'" . ( $first == true ? " data-toggle='dropdown'" : " onClick='loadImages ( \"" . ( int ) $localFolder->fol_id . "\", 1 ); return false;'" ) . " class='btn btn-white'" . ( $first == false ? " mid-item" : "" ) . "'>" . Validate::prepareOutput ( $localFolder->fol_name ) . ( $first == true ? ( $totalText . "&nbsp;&nbsp;<i class='caret'></i>" ) : "") . "</a>" . ( $first == true ? $dropdownMenu : "" );
            $first = false;
            $localFolder = FileFolder::loadById ( $parentId );
        }

        $breadcrumbs = array_merge ( $breadcrumbs, array_reverse ( $localBreadcrumbs ) );

        $breadcrumbs [ ] = "<a class='add-sub-folder-plus-btn' href='#' onClick='showAddFolderForm ( " . ( int ) $folder->fol_id . " ); return false;' title=' data-original-title='Add Sub Folder' data-placement='bottom'' data-toggle='tooltip'><i class='glyphicon glyphicon-plus-sign'></i></a>";
    }
} elseif ( $searchType == "root" ) {
    $dropdownMenu = "<ul role='menu' class='dropdown-menu dropdown-white pull-left'>";
    $dropdownMenu .= "<li><a href='#' onClick='uploadFiles ( \"\" );'><span class='context-menu-icon'><span class='glyphicon glyphicon-cloud-upload'></span></span>Upload Files</a></li>";
    $dropdownMenu .= "<li class='divider'></li>";
    $dropdownMenu .= "<li><a href='#' onClick='showAddFolderForm ( -1 );'><span class='context-menu-icon'><span class='glyphicon glyphicon-plus'></span></span>Add Folder</a></li>";
    $dropdownMenu .= "<li class='divider'></li>";
    $dropdownMenu .= "<li><a href='#' onClick='selectAllFiles ( );'><span class='context-menu-icon'><span class='glyphicon glyphicon-check'></span></span>Select All Files</a></li>";
    $dropdownMenu .= "<li><a href='#' onClick='clearSelected ( );'><span class='context-menu-icon'><span class='glyphicon glyphicon-unchecked'></span></span>Clear Selected</a></li>";
    $dropdownMenu .= "</ul>";

    $breadcrumbs [ ] = "<a href='#' data-toggle='dropdown' class='btn btn-white'>Root Folder" . $totalText . "&nbsp;&nbsp;<i class='caret'></i></a>" . $dropdownMenu;

    $breadcrumbs [ ] = "<a class='add-sub-folder-plus-btn' href='#' onClick='showAddFolderForm ( -1 ); return false;' title='' data-original-title='Add Sub Folder' data-placement='bottom' data-toggle='tooltip'><i class='glyphicon glyphicon-plus-sign'></i></a>";
} else {
    $breadcrumbs [ ] = "<a href='#' onClick='" . $pagingJs . "1 ); return false;' class='btn btn-white'>" . Validate::prepareOutput ( $pageTitle ) . $totalText . "</a>";
}

$rs [ "html" ] .= "<div class='image-browse'>";
$rs [ "html" ] .= "<div id='fileManager' class='fileManager " . Validate::prepareOutput ( $_SESSION [ "browse" ] [ "viewType" ] ) . "'>";
$rs [ "html" ] .=
        "<div class='toolbar-container'>" .
            "<!-- toolbar -->" .
            "<div class='col-md-6 col-sm-8 clearfix'>" .
                "<!-- breadcrumbs -->" .
                "<div class='row breadcrumbs-container'>" .
                    "<div class='col-md-12 col-sm-12 clearfix'>" .
                        "<ol id='folderBreadcrumbs' class='btn-group btn-breadcrumb'>" . implode ( "", $breadcrumbs ) . "</ol>" .
                    "</div>" .
                "</div>" .
            "</div>";

if ( $files || $folders ) {
    $rs [ "html" ] .=
            "<div class='col-md-6 clearfix right-toolbar-options'>" .
                "<div class='list-inline pull-right'>" .
                    "<div class='btn-toolbar pull-right' role='toolbar'>";

    $rs [ "html" ] .= "<div class='btn-group hidden-xs'>";
    $rs [ "html" ] .= "<button class='btn btn-white disabled fileActionLinks' type='button' title='' data-original-title='Links' data-placement='bottom' data-toggle='tooltip' onclick='viewFileLinks ( ); return false;'><i class='entypo-link'></i></button>";
    $rs [ "html" ] .= "<button class='btn btn-white disabled fileActionLinks' type='button' title='' data-original-title='Delete' data-placement='bottom' data-toggle='tooltip' onclick='deleteFiles ( ); return false;'><i class='entypo-cancel'></i></button>";
    $rs [ "html" ] .= "<button class='btn btn-white' type='button' title='' data-original-title='List View' data-placement='bottom' data-toggle='tooltip' onclick='toggleViewType ( ); return false;' id='viewTypeText'><i class='entypo-list'></i></button>";
    $rs [ "html" ] .= "<button class='btn btn-white' type='button' title='' data-original-title='Fullscreen' data-placement='bottom' data-toggle='tooltip' onclick='toggleFullScreenMode ( ); return false;'><i class='entypo-resize-full'></i></button>";
    $rs [ "html" ] .= "</div>";

    $rs [ "html" ] .= "<div class='btn-group'>";

    if ( $searchType != "browserecent" ) {
        $rs [ "html" ] .=
                "<div class='btn-group'>" .
                    "<button id='filterButton' data-toggle='dropdown' class='btn btn-white dropdown-toggle' type='button' " . ( $disableFilterButton ? "disabled" : "" ) . ">" .
                        Validate::prepareOutput ( $sortingOptions { $_SESSION [ "search" ] [ "filterOrderBy" ] } ) . " <i class='entypo-arrow-combo'></i>" .
                    "</button>" .
                    "<ul role='menu' class='dropdown-menu dropdown-white pull-right'>" .
                        "<li class='disabled'><a href='#'>Sort By</a></li>";

                        foreach ( $sortingOptions AS $key => $value ) {
                            if ( $key == "order_by_deleted_date_desc" ) {
                                continue;
                            }

                            $rs [ "html" ] .= "<li><a href='#' onclick='" . $updateSortingJs . "\"" . $key . "\", \"" . $value . "\", this ); return false;'>" . $value . "</a></li>";
                        }

        $rs [ "html" ] .=
                    "</ul>" .
                    "<input name='filterOrderBy' id='filterOrderBy' value='" . Validate::prepareOutput ( $_SESSION [ "search" ] [ "filterOrderBy" ] ) . "' type='hidden'>" .
				"</div>";
    }

    $rs [ "html" ] .=
            "<div class='btn-group'>" .
                "<button id='perPageButton' data-toggle='dropdown' class='btn btn-white dropdown-toggle' type='button'>" .
                    $_SESSION [ "search" ] [ "perPage" ] . " <i class='entypo-arrow-combo'></i>" .
				"</button>" .
			    "<ul role='menu' class='dropdown-menu dropdown-white pull-right per-page-menu'>" .
					"<li class='disabled'><a href='#'>Per Page</a></li>";

                    foreach ( $perPageOptions AS $perPageOption ) {
                        $rs [ "html" ] .= "<li><a href='#' onclick='" . $updatePagingJs . "\"" . ( int ) $perPageOption . "\", \"" . ( int ) $perPageOption . "\", this ); return false;'>" . $perPageOption . "</a></li>";
                    }

    $rs [ "html" ] .=
                "</ul>" .
                    "<input name='perPageElement' id='perPageElement' value='30' type='hidden'>" .
                "</div>" .
			"</div>" .
		"</div>" .
        "<ol id='folderBreadcrumbs2' class='breadcrumb bc-3 pull-right'>" .
            "<li class='active'><span id='statusText'></span></li>" .
		"</ol>" .
	"</div>" .
"</div>";

    $rs [ "html" ] .=
		    "<!-- /.navbar-collapse -->" .
            "</div>";

    $rs [ "html" ] .= "<div class='gallery-env'><div class='fileListing' id='fileListing'>";

    $thumbnailWidth = 160;
    $thumbnailHeight = 134;
    $counter = 1;

    // Output folders
    if ( $folders ) {
        foreach ( $folders AS $folder ) {
            $folderObj = FileFolder::hydrate ( $folder );
            $folderLabel = $folderObj->fol_name;

            $rs [ "html" ] .= "<div id='folderItem" . ( int ) $folderObj->fol_id .
                                "' data-clipboard-action='copy" .
                                "' data-clipboard-target='#clipboard-placeholder" .
                                "' class='fileItem folderIconLi fileIconLi col-xs-4 image-thumb owned-folder" .
                                "' onClick='loadImages ( " . ( int ) $folderObj->fol_id . " ); return false;" .
                                "' folderId='" . ( int ) $folderObj->fol_id .
                                "' sharing-url='" . $folderObj->getFolderUrl ( ) . "'>";

            $rs [ "html" ] .= "<div class='thumbIcon'>";
            $rs [ "html" ] .= "<a name='link'>";
            if ( $folderObj->file_count == 0 ) {
                $rs [ "html" ] .= "<img src='" . THEME_IMAGE_PATH . "/folder_fm_grid.png' />";
            } else {
                $rs [ "html" ] .= "<img src='" . THEME_IMAGE_PATH . "/folder_full_fm_grid.png' />";
            }
            $rs [ "html" ] .= "</a>";
            $rs [ "html" ] .= "</div>";

            $rs [ "html" ] .= "<span class='filesize'></span>";
            $rs [ "html" ] .= "<span class='fileUploadDate'>" . ( $folder->file_count > 0 ? ( $folder->file_count . " " . ( $folder->file_count == 1 ? "file" : "files" ) ) : "-" ) . "</span>";
            $rs [ "html" ] .= "<span class='thumbList'>";
            $rs [ "html" ] .= "<a name='link'>";
            if ( $folder->file_count == 0 ) {
                $rs [ "html" ] .= "<img src='" . THEME_IMAGE_PATH . "/folder_fm_list.png' />";
            } else {
                $rs [ "html" ] .= "<img src='" . THEME_IMAGE_PATH . "/folder_full_fm_list.png' />";
            }
            $rs [ "html" ] .= "</a>";
            $rs [ "html" ] .= "</span>";

            $rs [ "html" ] .= "<span class='filename'>" . Validate::prepareOutput ( $folderLabel ) . "</span>";

            $rs [ "html" ] .= "<div class='fileOptions'>";
            $rs [ "html" ] .= "<a class='fileDownload' href='#'><i class='caret'></i></a>";
            $rs [ "html" ] .= "</div>";

            $rs [ "html" ] .= "</div>";

            ++$counter;
        }
    }

    // Output files
    if ( $files ) {
        foreach ( $files AS $file ) {
            $fileObj = File::hydrate ( $file );

            $sizingMethod = "middle";
            $previewImageUrlLarge = $fileObj->getIconPreviewImageUrl ( 48 );
            $previewImageUrlMedium = $fileObj->getIconPreviewImageUrlMedium ( );

            $rs [ "html" ] .= "<div dttitle='" . Validate::prepareOutput ( $fileObj->fil_name ) .
                                "' dtsizeraw='" . Validate::prepareOutput ( $fileObj->fil_size ) .
                                "' dtuploaddate='" . Validate::prepareOutput ( $fileObj->fil_upload_date ) .
                                "' dtfullurl='" . Validate::prepareOutput ( $fileObj->getFullShortUrl ( ) ) .
                                "' dtfilename='" . Validate::prepareOutput ( $fileObj->fil_name ) .
                                "' dtstatsurl='" . Validate::prepareOutput ( $fileObj->getStatisticsUrl ( ) ) .
                                "' dturlhtmlcode='" . Validate::prepareOutput ( $fileObj->getHtmlLinkCode ( ) ) .
                                "' dturlbbcode='" . Validate::prepareOutput ( $fileObj->getForumLinkCode ( ) ) .
                                "' dtextramenuitems='" .
                                "' title='" . Validate::prepareOutput ( $fileObj->fil_name ) . " (" . Validate::prepareOutput ( Functions::formatSize ( $fileObj->fil_size ) ) .
                                ")' fileId='" . $fileObj->fil_id .
                                "' class='col-xs-4 image-thumb image-thumb-" . $sizingMethod . " fileItem" . $fileObj->fil_id . " fileIconLi " . ( $fileObj->fil_trash != null ? "fileDeletedLi" : "" ) . " owned-image'>";

            $rs [ "html" ] .= "<div class='thumbIcon'>";
            $rs [ "html" ] .= "<a name='link'><img src='" . ( substr ( $previewImageUrlLarge, 0, 4 ) == "http" ? $previewImageUrlLarge : ( THEME_IMAGE_PATH . "/trans_1x1.gif" ) ) . "' alt='' class='" . ( substr ( $previewImageUrlLarge, 0, 4 ) != "http" ? $previewImageUrlLarge : "#" ) . "' style='max-width: 100%; max-height: 100%; min-width: 30px; min-height: 30px;'></a>";
            $rs [ "html" ] .= "</div>";

            $rs [ "html" ] .= "<span class='filesize'>" . Validate::prepareOutput ( Functions::formatSize ( $fileObj->fil_size ) ) . "</span>";
            $rs [ "html" ] .= "<span class='fileUploadDate'>" . Validate::prepareOutput ( $fileObj->fil_upload_date ) . "</span>";
            $rs [ "html" ] .= "<span class='fileOwner'>" . Validate::prepareOutput ( $fileObj->usr_username ) . "</span>";
            $rs [ "html" ] .= "<span class='thumbList'>";
            $rs [ "html" ] .= "<a name='link'><img src='" . $previewImageUrlMedium . "' alt=''></a>";
            $rs [ "html" ] .= "</span>";

            $rs [ "html" ] .= "<span class='filename'>" . Validate::prepareOutput ( $fileObj->fil_name ) . "</span>";

            $rs [ "html" ] .= "<div class='fileOptions'>";
            $rs [ "html" ] .= "<a class='fileDownload' href='#'><i class='caret'></i></a>";
            $rs [ "html" ] .= "</div>";

            $rs [ "html" ] .= "</div>";

            ++$counter;
        }
    }

    $rs [ "html" ] .= "</div>";
    $rs [ "html" ] .= "</div>";

    // Paging
    $currentPage = $pageStart;
    $totalPages = ceil ( ( int ) $allStats->total_file_count / ( int ) $_SESSION [ "search" ] [ "perPage" ] );
    $rs [ "html" ] .= "<div class='paginationRow'>";
    $rs [ "html" ] .= "<div id='pagination' class='paginationWrapper col-md-12 responsiveAlign'>";
    $rs [ "html" ] .= "<ul class='pagination'>";
    $rs [ "html" ] .= "<li class='" . ( $currentPage == 1 ? "disabled" : "" ) . "'><a href='#' onClick='" . ( $currentPage > 1 ? ( $pagingJs . "1 );" ) : "" ) . " return false;'><i class='entypo-to-start'></i><span>First</span></a></li>";
    $rs [ "html" ] .= "<li class='" . ( $currentPage == 1 ? "disabled" : "" ) . "'><a href='#' onClick='" . ( $currentPage > 1 ? ( $pagingJs . ( ( int ) $currentPage - 1 ) . " );" ) : "" ) . " return false;'><i class='entypo-left-dir'></i><span>Previous</span></a></li>";

    $startPager = $currentPage - 3;
    if ( $startPager < 1 ) {
        $startPager = 1;
    }

    for ( $i = 0; $i <= 8; ++$i ) {
        $currentPager = $startPager + $i;
        if ( $currentPager > $totalPages ) {
            continue;
        }
        $rs [ "html" ] .= "<li class='" . ( $currentPager == $currentPage ? "active" : "" ) . "'><a href='#' onclick='" . $pagingJs . ( int ) $currentPager . " ); return false;'>" . $currentPager . "</a></li>";
    }

    $rs [ "html" ] .= "<li class='" . ( $currentPage == $totalPages ? "disabled" : "" ) . "'><a href='#' onClick='" . ( $currentPage != $totalPages ? ( $pagingJs . ( ( int ) $currentPage + 1 ) . " );" ) : "" ) . " return false;'><span>Next</span><i class='entypo-right-dir'></i></a></li>";
    $rs [ "html" ] .= "<li class='" . ( $currentPage == $totalPages ? "disabled" : "" ) . "'><a href='#' onClick='" . ( $currentPage != $totalPages ? ( $pagingJs . ( ( int ) $totalPages ) . " );" ) : "" ) . " return false;'><span>Last</span><i class='entypo-to-end'></i></a></li>";
    $rs [ "html" ] .= "</ul>";
    $rs [ "html" ] .= "</div>";

    $rs [ "html" ] .= "</div>";
}
else
{
    $rs [ "html" ] .= "</div>";
    $rs [ "html" ] .= "<div class='no-results-wrapper'>";
    if ( $searchType == "folder" || $searchType == "root" ) {
        if ( $user->isLogged ( ) ) {
            $html = "";
            $html .= "<div class='no-files-upload-wrapper' onClick='uploadFiles ( " . ( $folder ? $folder->fol_id : "\"\"" ) . ", true ); return false;'>";
            $html .= "<img src='" . THEME_IMAGE_PATH . "/modal_icons/upload-computer-icon.png' class='upload-icon-image' />";
            $html .= "<div class='clear'><!-- --></div>";
            if ( Functions::currentBrowserIsIE ( ) ) {
                $html .= "No files found within this folder. Click here to upload";
            } else {
                $html .= "Drag & drop files or click here to upload";
            }
            $html .= "</div>";

            $rs [ "html" ] .= $html;
        }
        else
        {
            $rs [ "html" ] .= "<div class='alert alert-warning'><i class='entypo-attention'></i> No files found within this folder.</div>";
        }
    }
    else
    {
        $rs [ "html"] .= "<div class='alert alert-warning'><i class='entypo-attention'></i> No files found within search criteria.</div>";
    }
    $rs [ "html" ] .= "</div>";
}

// Set filter order back to the original value
if ( $searchType == "recent" || $searchType == "browserecent" || $searchType == "recent" || $searchType == "trash" ) {
    $_SESSION [ "search" ] [ "filterOrderBy" ] = $originalFilterOrderBy;
}

// Set vars to return json
$rs [ "page_title" ] = $pageTitle;
$rs [ "page_url" ] = $pageUrl;

// Output response
echo json_encode ( $rs );
exit ( );
