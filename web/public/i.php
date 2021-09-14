<?php
$file = false;
$fileName = false;

if($_GET['src']){
	require_once(__DIR__ . '/web.includes.php');
    $folder = DIR_UPLOAD . '/projects/';
    $filePath = urldecode($_GET['src']);

    if(file_exists($folder . $filePath)){
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        switch ($extension) {
            case 'gif':
                header('Content-type: image/gif');
                break;
            case 'jpeg':
            case 'jpg':
                header('Content-type: image/jpeg');
                break;
            case 'png':
            default:
                header('Content-type: image/png');
                break;
        }

        print file_get_contents($folder . $filePath);
    }else{
        // File not found
    }
}

exit();