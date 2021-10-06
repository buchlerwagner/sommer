<?php
$file = false;
$fileName = false;

if($_GET["hash"] && $_GET["type"] && $_GET["id"]){
	require_once(__DIR__ . '/web.includes.php');

	$id = (int) $_GET['id'];

    $router = new router();

    $path = DIR_UPLOAD;
    $hash = urldecode($_GET['hash']);
    $hash = str_replace('../', '', trim($hash, '/'));
    $hash = $router->db->escapeString($hash);

    $sql = false;

    switch($_GET["type"]) {
        case 'documents':
            $path .= $router->shopId . '/documents/';
            $sql = 'SELECT doc_filename AS filename FROM documents WHERE doc_id = ' . $id . ' AND doc_hash = "' . $hash . '" AND doc_shop_id = ' . $router->shopId;
            break;
        default:
            die();
    }
    if($sql){
        $row = $router->db->getFirstRow($sql);
        if ($row) {
            $fileName = $row['filename'];
        }

        $file = $path . $hash;
    }

	if(file_exists($file)){
		$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

		switch ($ext) {
			case 'gif':
				header('Content-type: image/gif');
				break;
			case 'jpeg':
			case 'jpg':
				header('Content-type: image/jpeg');
				break;
			case 'png':
				header('Content-type: image/png');
				break;
			case 'pdf':
                header("Content-type: application/pdf");
                break;
			case 'doc':
			case 'docx':
			case 'xls':
			case 'xlsx':
			case 'txt':
			case 'html':
			default:
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				break;
		}

        if($_REQUEST['m'] == 'inline'){
            header('Content-Disposition: inline; filename=' . ($fileName ?: $hash));
        }else {
            header('Content-Disposition: attachment; filename=' . ($fileName ?: $hash));
        }

        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));

		readfile_chunked($file);
		exit();
	}
}
