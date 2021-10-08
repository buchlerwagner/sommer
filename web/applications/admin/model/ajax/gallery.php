<?php
include_once WEB_ROOT . '../lib/fileuploader.class.php';

/**
 * @var $this router
 */

$uploadDir = FOLDER_UPLOAD . $this->shopId . '/gallery/';
$realUploadDir = DIR_UPLOAD . $this->shopId  . '/gallery/';

if (!file_exists($realUploadDir)) {
	@mkdir($realUploadDir, 0777, true);
	@chmod($realUploadDir, 0777);
}

$_action = isset($_GET['type']) ? $_GET['type'] : '';

function getRealFile($file) {
	global $uploadDir, $realUploadDir;

	return str_replace($uploadDir, $realUploadDir, $file);
}

// upload
if ($_action == 'upload') {
	$id = false;
	$title = 'name';

	// if after editing
	if (isset($_POST['id']) && isset($_POST['name']) && isset($_POST['_editor'])) {
		$_id = $this->db->escapeString($_POST['id']);

		$row = $this->db->getFirstRow("SELECT g_file FROM " . DB_NAME_WEB . ".gallery WHERE id = '$_id' AND g_shop_id='" . $this->shopId . "'");
		if ($row) {
			$id = $_id;
			$pathinfo = pathinfo($row['g_file']);

			$realUploadDir = getRealFile($pathinfo['dirname'] . '/');
			$title = $pathinfo['filename'];
		} else {
			exit;
		}
	}

	// initialize FileUploader
	$FileUploader = new FileUploader('files', array(
		'limit' => 1,
		'fileMaxSize' => 20,
		'extensions' => array('image/*', 'video/*', 'audio/*'),
		'uploadDir' => $realUploadDir,

		'required' => true,
		'title' => $title,
		'replace' => $id,
		'editor' => array(
			'maxWidth' => 1980,
			'maxHeight' => 1980,
			'crop' => false,
			'quality' => 90
		)
	));

	$upload = $FileUploader->upload();

	if (count($upload['files']) == 1) {
		$item = $upload['files'][0];
		//$title = $this->db->escapeString($item['name']);
		$title = '';
		$type = $this->db->escapeString($item['type']);
		$size = $this->db->escapeString($item['size']);
		$file = $this->db->escapeString($uploadDir . $item['name']);

		if (!$id) {
            $this->db->sqlQuery("INSERT INTO " . DB_NAME_WEB . ".gallery(`g_shop_id`, `g_title`, `g_file`, `g_type`, `g_size`, `g_index`, `g_date`) VALUES('" . $this->shopId . "', '$title', '$file', '$type', '$size', 1 + (SELECT IFNULL((SELECT MAX(`g_index`) FROM " . DB_NAME_WEB . ".gallery g WHERE g_shop_id=" . $this->shopId . "), -1)), NOW())");
            $id = $this->db->getInsertRecordId();
        }else {
            $this->db->sqlQuery("UPDATE " . DB_NAME_WEB . ".gallery SET `g_size` = '$size' WHERE g_id = '$id' AND g_shop_id='" . $this->shopId . "'");
        }

		if ($id) {
            $parts = pathinfo($realUploadDir . $item['name']);
            $newFileName = $parts['filename'];

            if($GLOBALS['IMAGE_SIZES']){
                foreach($GLOBALS['IMAGE_SIZES'] AS $postfix => $size){
                    FileUploader::resize(
                        $realUploadDir . $item['name'],
                        $size['width'],
                        $size['height'],
                        $realUploadDir . $newFileName . ($postfix != 'default' ? '_' . $postfix : '') . '.' . $parts['extension'],
                        ($size['crop'] ?: false),
                        ($size['quality'] ?: 97),
                        ($size['rotation'] ?: 0)
                    );
                }
            }

			$upload['files'][0] = array(
				'title' => $item['title'],
				'name' => $item['name'],
				'size' => $item['size'],
				'size2' => $item['size2'],
				'url' => $file,
				'id' => $id ?: $this->db->getInsertRecordId()
			);
		} else {
			if (is_file($item['file']))
				@unlink($item['file']);
			unset($upload['files'][0]);
			$upload['hasWarnings'] = true;
			$upload['warnings'][] = 'An error occured.';
		}
	}

	echo json_encode($upload);
	exit;
}

// preload
if ($_action == 'preload') {
	$preloadedFiles = array();

	$result = $this->db->getRows("SELECT * FROM " . DB_NAME_WEB . ".gallery WHERE g_shop_id='" . $this->shopId . "' ORDER BY `g_index` ASC");
	if ($result) {
		foreach($result AS $row) {
			$preloadedFiles[] = array(
				'name' => ($row['g_title'] ?: basename($row['g_file'])),
				'type' => $row['g_type'],
				'size' => $row['g_size'],
				'file' => $row['g_file'],
				'data' => array(
					'readerForce' => true,
					'url' => $row['g_file'],
					'date' => $row['g_date'],
					'isMain' => $row['g_main'],
					'listProps' => array(
						'id' => $row['g_id'],
					)
				),
			);
		}
	}

	echo json_encode($preloadedFiles);
	exit;
}

// resize
if ($_action == 'resize') {
	if (isset($_POST['id']) && isset($_POST['name']) && isset($_POST['_editor'])) {
		$id = $this->db->escapeString($_POST['id']);
		$editor = json_decode($_POST['_editor'], true);

		$row = $this->db->getFirstRow("SELECT g_file FROM " . DB_NAME_WEB . ".gallery WHERE id = '$id' AND g_shop_id='" . $this->shopId . "'");
		if ($row) {
			$file = getRealFile($row['g_file']);

			if (is_file($file)) {
				$info = Fileuploader::resize($file, null, null, null, (isset($editor['crop']) ? $editor['crop'] : null), 100, (isset($editor['rotation']) ? $editor['rotation'] : null));
				$size = filesize($file);

				$this->db->sqlQuery("UPDATE " . DB_NAME_WEB . ".gallery SET `g_size` = '$size' WHERE g_id = '$id' AND g_shop_id='" . $this->shopId . "'");
			}
		}
	}

	exit;
}

// sort
if ($_action == 'sort') {
	if (isset($_POST['list'])) {
		$list = json_decode($_POST['list'], true);

		$index = 0;
		foreach($list as $val) {
			if (!isset($val['id']) || !isset($val['name']) || !isset($val['index']))
				break;

			$id = $this->db->escapeString($val['id']);
			$this->db->sqlQuery("UPDATE " . DB_NAME_WEB . ".gallery SET `g_index` = '$index' WHERE g_id = '$id' AND g_shop_id='" . $this->shopId . "'");
			$index++;
		}

        $this->mem->delete(CACHE_GALLERY . $this->shopId);
    }
	exit;
}

// rename
if ($_action == 'rename') {
	if (isset($_POST['id']) && isset($_POST['name']) && isset($_POST['title'])) {
		$id = $this->db->escapeString($_POST['id']);
        $this->db->sqlQuery("UPDATE " . DB_NAME_WEB . ".gallery SET `g_title` = '" . $this->db->escapeString($_POST['title']) . "' WHERE g_id = '$id' AND g_shop_id='" . $this->shopId . "'");

        $this->mem->delete(CACHE_GALLERY . $this->shopId);

        echo json_encode([
            'title' => $_POST['title'],
            'file' => $_POST['title'],
        ]);

        /*
		$title = substr(FileUploader::filterFilename($_POST['title']), 0, 200);

		$row = $this->db->getFirstRow("SELECT g_file FROM " . DB_NAME_WEB . ".gallery WHERE g_id = '$id' AND g_shop_id='" . $this->shopId . "'");
		if ($row) {
			$file = $row['g_file'];

			$pathinfo = pathinfo($file);
			$newName = $title . (isset($pathinfo['extension']) ? '.' . $pathinfo['extension'] : '');
			$newFile = $pathinfo['dirname'] . '/' . $newName;

			$realFile = str_replace($uploadDir, $realUploadDir, $file);
			$newRealFile = str_replace($uploadDir, $realUploadDir, $newFile);

			if (!file_exists($newRealFile) && rename($realFile, $newRealFile)) {
				$query = $this->db->sqlQuery("UPDATE " . DB_NAME_WEB . ".gallery SET `g_title` = '" . $this->db->escapeString($newName) . "', `g_file` = '" . $this->db->escapeString($newFile) . "' WHERE g_id = '$id' AND g_shop_id='" . $this->shopId . "'");
				if ($query) {
					echo json_encode([
						'title' => $title,
						'file' => $newFile,
						'url' => $newFile
					]);
				}
			}
		}

        */
	}
	exit;
}

// asmain
if ($_action == 'asmain') {
	if (isset($_POST['id']) && isset($_POST['name'])) {
		$id = $this->db->escapeString($_POST['id']);

		//$this->db->sqlQuery("UPDATE " . DB_NAME_WEB . ".gallery SET g_main = 0 AND g_shop_id='" . $this->shopId . "'");
		$this->db->sqlQuery("UPDATE " . DB_NAME_WEB . ".gallery SET g_main = NOT g_main WHERE g_id = '$id' AND g_shop_id='" . $this->shopId . "'");
        $row = $this->db->getFirstRow("SELECT g_main FROM " . DB_NAME_WEB . ".gallery WHERE g_id = '$id' AND g_shop_id='" . $this->shopId . "'");
        if($row) {
            $out['selected'] = (int)$row['g_main'];
        }else{
            $out['selected'] = 0;
        }

        $this->mem->delete(CACHE_GALLERY . $this->shopId);

        echo json_encode($out);
	}
	exit;
}

// remove
if ($_action == 'remove') {
	if (isset($_POST['id']) && isset($_POST['name'])) {
		$id = $this->db->escapeString($_POST['id']);
		$row = $this->db->getFirstRow("SELECT g_file FROM " . DB_NAME_WEB . ".gallery WHERE g_id = '$id' AND g_shop_id='" . $this->shopId . "'");

		if ($row) {
            $this->mem->delete(CACHE_GALLERY . $this->shopId);

            $file = str_replace($uploadDir, $realUploadDir, $row['g_file']);

			$this->db->sqlQuery("DELETE FROM " . DB_NAME_WEB . ".gallery WHERE g_id = '${id}' AND g_shop_id='" . $this->shopId . "'");
			if (is_file($file))
				unlink($file);
		}
	}
	exit;
}