<?php
$no_img = false;
$fileName = '';

if($_GET['src']){
    $src = urldecode($_GET['src']);
    $cropMode = (int) $_GET["m"];
    $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION));

    if(Empty($_GET['w'])) $_GET['w'] = 'max';
    if(Empty($_GET['h'])) $_GET['h'] = 'max';

    require_once(__DIR__ . '/web.includes.php');

    if(IMG_CACHE_ENABLED){
        if (is_numeric($src)) {
            $ext = 'jpg';
        }

        $dir_level = 4;
        if(!$cropMode) $cropMode = 0;
        $file_hash = md5($src) . '.' . $ext;
        $file_path = DIR_CACHE . 'thumbnails/' . $cropMode . '/' . $_GET["w"] . 'x' . $_GET["h"] . '/';

        for($i=0; $i<$dir_level; $i++){
            $file_path .= $file_hash[$i].'/';
        }

        if (file_exists($file_path . $file_hash)) {
            switch ($ext) {
                case 'svg':
                    header('Content-type: image/svg+xml');
                    break;
                case 'gif':
                    header('Content-type: image/gif');
                    break;
                case 'jpg':
                    header('Content-type: image/jpeg');
                    break;
                case 'png':
                default:
                    header('Content-type: image/png');
                    break;
            }

            print file_get_contents($file_path . $file_hash);
            exit();
        }
    }

    if(file_exists(DIR_UPLOAD . $src)){
        $fileName = DIR_UPLOAD . $src;

        if($ext == 'svg'){
            header("Content-type: image/svg+xml");
            print @file_get_contents($fileName);
            exit();
        }

        if($_GET['w']=='max' OR $_GET['h'] == 'max') {

            $imageinfo = getimagesize( $fileName );
            if ($imageinfo[2] == 1) {
                $imagetype = "gif" ;
            } elseif ($imageinfo[2] == 2) {
                $imagetype = "jpeg" ;
            } elseif ($imageinfo[2] == 3) {
                $imagetype = "png" ;
            } else {
                $no_img = true;
            }

            if($imageinfo) {
                $binimg = @file_get_contents($fileName);
                if($binimg) {
                    if (IMG_CACHE_ENABLED) {
                        //Image saving
                        @mkdir($file_path, 0777, true);
                        @chmod($file_path, 0777);

                        @file_put_contents($file_path . $file_hash, $binimg);
                    }

                    header("Content-type: image/$imagetype");
                    print $binimg;
                    exit();
                }else{
                    $no_img = true;
                }
            }
        }else {
            $thumb = NULL;
            require_once( __DIR__ . '/../plugins/thumbnail/ThumbLib.inc.php');

            try {
                $thumb = PhpThumbFactory::create($fileName);
            } catch (Exception $e) {
                // handle error here however you'd like
                $tmp = parse_url($fileName);
                if (strtolower($tmp['scheme']) == 'https') {
                    $fileName = 'http://' . $tmp['host'] . $tmp['path'];
                    try {
                        $thumb = PhpThumbFactory::create($fileName);
                    } catch (Exception $e) {
                        exit;
                    }
                }
            }

            if ($thumb) {
                if (Empty($_GET["m"])) {
                    // Simple resize
                    $thumb->resize($_GET["w"], $_GET["h"]);
                } elseif ($_GET["m"] == 1) {
                    // Adaptive Resizing
                    $thumb->setOptions(['resizeUp' => true]);
                    $thumb->adaptiveResize($_GET["w"], $_GET["h"]);
                } elseif ($_GET["m"] == 2) {
                    // Crop From Center
                    $thumb->cropFromCenter($_GET["w"], $_GET["h"]);
                } elseif ($_GET["m"] == 3) {
                    // Resize by Percentage
                    $thumb->resizePercent($_GET["p"]);
                }

                if (IMG_CACHE_ENABLED) {
                    //Image saving
                    @mkdir($file_path, 0777, true);
                    @chmod($file_path, 0777);

                    $thumb->save($file_path . $file_hash);
                }

                $thumb->show();
                exit();

            } else {
                $no_img = true;
            }
        }

    }else{
        $no_img = true;
    }
}

if($no_img){
    header('Content-type: image/gif');
    print file_get_contents(__DIR__ . '/images/blank.gif');
}
