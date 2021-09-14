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
        case 'document':
            $path .= 'fleet/' . $id . '/';
            $sql = 'SELECT fp_filename AS filename FROM fleet_properties WHERE fp_hash = "' . $hash . '"';
            break;
        case 'contract':
            $path .= 'leases/' . $id . '/';
            $sql = 'SELECT ld_filename AS filename FROM leasing_documents WHERE ld_hash = "' . $hash . '"';
            break;
        case 'invoice_incoming':
            $path .= 'invoices/incoming/' . $id . '/';
            $sql = 'SELECT ivi_filename AS filename FROM invoices_incoming WHERE ivi_hash = "' . $hash . '"';
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
