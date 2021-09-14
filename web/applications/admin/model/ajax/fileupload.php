<?php
/**
 * @var $this router
 */

$this->output = OUTPUT_JSON;
$this->data = [];

$action = $this->params[1];
switch($action) {
	case 'upload-profile-img':
		if ($_FILES["picture"]) {
            $userid = $this->user->id;

            include DOC_ROOT . 'web/plugins/thumbnail/ThumbLib.inc.php';
            $path = WEB_ROOT . 'uploads/profiles/';

            $sql = "SELECT us_img FROM " . DB_NAME_WEB . ".users WHERE us_id='" . $userid . "'";
            $row = $this->db->getFirstRow($sql);
            if ($row['us_img'] && file_exists($path . $row['us_img'])) {
                @unlink($path . $row['us_img']);
            }

            $filename = $userid . '-' . md5($_FILES["picture"]['name'] . microtime()) . strrchr($_FILES["picture"]['name'], '.');
            $filename = strtolower($filename);
            move_uploaded_file($_FILES["picture"]['tmp_name'], $path . $filename);

            try {
                if ($thumb = PhpThumbFactory::create($path . $filename)) {
                    $thumb->adaptiveResize(PROFILE_IMG_SIZE, PROFILE_IMG_SIZE);
                    $thumb->cropFromCenter(PROFILE_IMG_SIZE, PROFILE_IMG_SIZE);
                    $thumb->save($path . $filename);
                }
            } catch (Exception $e) {
                // handle error here however you'd like
            }

            $this->db->sqlQuery(
                $this->db->genSQLUpdate(
                    DB_NAME_WEB . '.users',
                    [
                        'us_img' => $filename
                    ],
                    [
                        'us_id' => $userid
                    ]
                )
            );

            $img = FOLDER_UPLOAD . '/profiles/' . $filename;

            $this->data = [];
            $this->user->clearUserDataCache($userid);
            $this->user->changeUserSessionData('img', $img);

            $this->data['.user-profile-img']['attr']['src'] = $img;
            $this->data['.delete-profile-img']['show'] = true;
            $this->data['#us_img']['value'] = $filename;
        }

		break;

	case 'delete-profile-img':
        $userid = $this->user->id;
        if($this->user->hasPageAccess('users') && isset($_REQUEST['usid'])){
            $userid = (int) $_REQUEST['usid'];
        }

        $path = WEB_ROOT . 'uploads/profiles/';
		$sql = "SELECT us_img, us_title FROM " . DB_NAME_WEB . ".users WHERE us_id='" . $userid . "'";
		$row = $this->db->getFirstRow($sql);
		if($row['us_img'] && file_exists($path . $row['us_img'])){
			@unlink($path . $row['us_img']);
		}

		$this->db->sqlQuery(
			$this->db->genSQLUpdate(
				DB_NAME_WEB . '.users',
				[
					'us_img' => ''
				],
				[
					'us_id' => $userid
				]
			)
		);

		$img = '/images/' . strtolower($row['us_title']) . '.svg';
		$this->user->clearUserDataCache($userid);
        $this->user->changeUserSessionData('img', $img);

        $this->data['.user-profile-img']['attr']['src'] = $img;
        $this->data['.delete-profile-img']['show'] = false;
        $this->data['#us_img']['value'] = '';

        break;
}
