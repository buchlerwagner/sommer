<?php
require_once(__DIR__ . '/../web.includes.php');

$ibe = new router();
$ibe->init();

$sql = "SELECT ca_cm_id AS cm_id, ca_filename, crm_mails.cm_ct_id AS ct_id, crm_mails.cm_date 
            FROM " . DB_NAME_WEB . ".crm_mail_attachments
                LEFT JOIN " . DB_NAME_WEB . ".crm_mails ON (ca_cm_id = cm_id)";
$result = $ibe->db->getRows($sql);
if($result){
    $sourceFolder = DIR_UPLOAD . 'admin.clevergreen.hu/attachments/';

	foreach($result AS $row){
        $cmid = (int) $row['cm_id'];
        $ctid = (int) $row['ct_id'];

        $srcFile = $sourceFolder . $cmid . '/' . $row['ca_filename'];
        $destFolder = DIR_UPLOAD . '/attachments/' . $ctid . '/' . $cmid . '/';

        if(!is_dir($destFolder)){
            @mkdir($destFolder, 0777, true);
            @chmod($destFolder, 0777);
        }

        $ext = strtolower(pathinfo($row['ca_filename'], PATHINFO_EXTENSION));
        $hash = hash_file('md5', $srcFile) . '-' . md5(strtotime($row['cm_date']));
        $newFile = $hash . '.' . $ext;

        if(copy($srcFile, $destFolder . $newFile)) {
            $ibe->db->sqlQuery(
                $ibe->db->genSQLInsert(
                    DB_NAME_WEB . ".project_documents",
                    [
                        'pd_p_id' => 0,
                        'pd_ct_id' => $ctid,
                        'pd_cm_id' => $cmid,
                        'pd_hash' => $hash,
                        'pd_orig_filename' => safeFileName($row['ca_filename']),
                        'pd_filename' => $newFile,
                        'pd_extension' => $ext,
                        'pd_size' => filesize($destFolder . $newFile),
                        'pd_mime_type' => mime_content_type($destFolder . $newFile),
                        'pd_type' => DOCUMENT_TYPE_ATTACHMENT,
                        'pd_uploaded' => $row['cm_date'],
                    ],
                    [
                        'pd_p_id',
                        'pd_ct_id',
                        'pd_cm_id',
                        'pd_hash'
                    ]
                )
            );
        }
    }
}