<?php

// receive varables
$fileToEmail = trim($_POST['fileToEmail']);
$filePassword = trim($_POST['filePassword']);
$fileCategory = (int)$_POST['fileCategory'];
$fileFolder = (int)$_POST['fileFolder'];
$fileDeleteHashes = $_POST['fileDeleteHashes'];
$fileShortUrls = $_POST['fileShortUrls'];

// make sure we have some items
if(COUNT($fileDeleteHashes) == 0)
{
    exit;
}

if(COUNT($fileDeleteHashes) != COUNT($fileShortUrls))
{
    exit;
}

// loop items, load from the database and create email content/set password
$fullUrls = array();
foreach($fileDeleteHashes AS $id=>$fileDeleteHash)
{
    // get short url
    $shortUrl = $fileShortUrls[$id];
    
    // load file
    $file = File::loadByShortUrl($shortUrl);
    if(!$file)
    {
        // failed lookup of file
        continue;
    }
    
    // make sure it matches the delete hash
    if($file->fil_delete_hash != $fileDeleteHash)
    {
        continue;
    }
    
    // update folder
    if(($user->isLogged()) && ($fileFolder > 0))
    {
        // make sure folder is within their account
        $folders = FileFolder::loadAllForSelect($Auth->id);
        if(isset($folders[$fileFolder]))
        {
            $file->updateFolder($fileFolder);
        }
    }
}
