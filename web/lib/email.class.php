<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class email extends ancestor {
	public $id;
	public $userId;
	public $language;
	public $tag;
	public $host;

	/*
	 * Email containing parameters:
	 * ----------------------------
	 * $this->to = 'whoto@example.com';
	 * OR
	 * $this->to = ['whoto@example.com', 'other@example.com'];
	 * OR
	 * $this->to = [
	 *	'John Doe' => 'whoto@example.com',
	 *  'other@example.com'
	 * ];
	 */
	public $from;
	public $replyto;
	public $to;
	public $cc;
	public $bcc;

	public $subject;
	public $body;

    private $mailId;
	private $attachments = [];

	public function init() {
		$this->id = null;
		$this->userId = $this->owner->user->id;
		$this->language = $this->owner->language;
		$this->host = $this->owner->domain;
		$this->from = '';
		$this->replyto = '';
		$this->to = '';
		$this->cc = '';
		$this->bcc = '';
		$this->subject = '';
		$this->body = '';
		$this->mailId = uuid::v4();
		$this->attachments = [];
	}

	public function addAttachment($file, $fileName = false) {
        $savePath = DIR_CACHE . 'emails/' . $this->mailId . '/';
        if(!is_dir($savePath)){
            @mkdir($savePath, 0777, true);
            @chmod($savePath, 0777);
        }

        if(!$fileName){
            $fileName = basename($file);
        }

        $key = sha1_file($file);
        if(copy($file, $savePath . $key)) {
            $this->attachments[$key] = $fileName;
        }
	}

	public function save() {
        $status = false;

		if (empty($this->id)) {
			$insert_values = [
				'em_userid' => $this->userId,
				'em_language' => $this->language,
				'em_tag' => $this->tag,
				'em_from' => $this->from,
				'em_replyto' => $this->replyto,
				'em_to' => $this->to,
				'em_cc' => $this->cc,
				'em_bcc' => $this->bcc,
				'em_subject' => $this->subject,
				'em_body' => $this->body,
				'em_mailid' => $this->mailId,
				'em_attachments' => $this->attachments,
				'em_status' => 0,
				'em_domain' => $this->host
			];
			foreach($insert_values as $key => $val) {
				if (is_array($val)) {
					$insert_values[$key] = serialize($val);
				}
			}
			$this->owner->db->sqlQuery(
				$this->owner->db->genSQLInsert(
					DB_NAME_WEB . '.emails',
					$insert_values
				)
			);
			$this->id = $this->owner->db->getInsertRecordId();

			if(defined('EMAIL_INSTANT_SEND') && EMAIL_INSTANT_SEND){
				$status = $this->send($this->id);
			}
		}

		return $status;
	}

	public function load($id) {
		$this->init();
		if (!empty($id)) {
			$res = $this->owner->db->getFirstRow(
				"SELECT * FROM " . DB_NAME_WEB . ".emails WHERE em_id = '" . $this->owner->db->escapeString($id) . "'"
			);
			if (!empty($res)) {
				foreach($res as $key => $val) {
					$property = substr($key, 3);
					if (property_exists($this, $property)) {
						if (!is_numeric($val)
							&& substr($val, 0, 2) == 'a:'
							&& substr($val, -1) == '}') {
							$val = unserialize($val);
						}
						$this->{$property} = $val;
					}
				}
			}
		}
	}

	/**
	 * Sending mail already contained ($id = false), or a saved mail ($id is integer)
	 * @param bool $id
	 * $status values:
	 * 0 - mail to send
	 * 1 - mail sent ok
	 * 2 - sending error
	 * 3 - mail contains errors, can not be sent
	 * @return int
	 */
	public function send($id = false) {
		if (!empty($id)) {
			$this->load($id);
		} else if (empty($this->id)) {
			$this->save();
		}

		$debugInfo = '';
		if ($check = $this->checkMail()) {
			$status = $check;
		} else {

			$mail = new PHPMailer(true);
			try {
                if(EMAIL_USE_GMAIL_SMTP){
                    $mail->IsSMTP();

                    $mail->SMTPDebug  = 0;
                    $mail->SMTPAuth   = true;
                    $mail->SMTPAutoTLS = false;
                    $mail->SMTPSecure = 'tls'; //tls or ssl
                    $mail->Port       = EMAIL_SMTP_PORT;
                    $mail->Host       = EMAIL_SMTP_HOST;
                    $mail->Username   = EMAIL_USERNAME;
                    $mail->Password   = EMAIL_PASSWORD;
                }

                $mail->IsHTML(true);
				$mail->CharSet = 'utf-8';
				$this->setParamEmail($mail, 'setFrom', $this->from);
				if($this->replyto) $this->setParamEmail($mail, 'addReplyTo', $this->replyto);
				$this->setParamEmail($mail, 'addAddress', $this->to);
				$this->setParamEmail($mail, 'addCC', $this->cc);
				$this->setParamEmail($mail, 'addBCC', $this->bcc);
				$mail->Subject = $this->subject;

				$mail->msgHTML($this->body, DOC_ROOT);
				if (!empty($this->attachments)) {
					foreach($this->attachments as $key => $filename) {
						$mail->addStringAttachment( $this->getAttachment($key), $filename );
					}
				}

				if (!$mail->send()) {
					$status = 2;
				} else {
					$status = 1;
				}

			} catch (Exception $e) {
				$status = 100;
                $debugInfo = $mail->ErrorInfo;
			}
		}

		if (!empty($this->id)) {
			$this->owner->db->sqlQuery(
				$this->owner->db->genSQLUpdate(
					DB_NAME_WEB . ".emails",
					[
						'em_status' => $status,
						'em_sent' => date("Y-m-d H:i:s"),
						'em_debug' => $debugInfo,
					],
					[
						'em_id' => $this->id
					]
				)
			);
		}

		return $status;
	}

	private function checkMail() {
		if (empty($this->from)) return 3;
		if (empty($this->to)) return 4;
		if (empty($this->subject)) return 5;
		if (empty($this->body)) return 6;
		return false;
	}

	private function setParamEmail(PHPMailer &$mail, $function, $emails) {
		if (is_array($emails)) {
			foreach($emails as $name => $email) {
				if (checkEmail($email, false)) {
					if (is_numeric($name)) {
						call_user_func( [$mail, $function], $email );
					} else {
						call_user_func( [$mail, $function], $email, $name );
					}
				}
			}
		} else if (!empty($emails)) {
			if (checkEmail($emails, false)) {
				call_user_func( [$mail, $function], $emails );
			}
		}
	}

	public function prepareEmail($template, $to, $data = [], $from = false, $cc = [], $bcc = [], $attachments = []){
		$sent = false;

		$this->init();
		$this->loadMailTemplate($template);

		if(!$from){
			$from = $this->getEmailSender();
		}
		$this->from = [$from['name'] => $from['email']];

    	$to = $this->owner->user->getUserProfile($to);

		if($to['email']) {
			if ($to['firstname']) $data['firstName'] = $to['firstname'];
			if ($to['lastname']) $data['lastName'] = $to['lastname'];
			if (!$to['name'] && $to['firstName'] && $to['lastname']) $to['name'] = localizeName($to['firstname'], $to['lastname'], $this->owner->language);

			if($to['name']) {
				$data['name'] = $to['name'];
				$this->to = [$to['name'] => $to['email']];
			}else {
				$this->to = $to['email'];
			}

			if ($cc) {
				if (!is_array($cc)) {
					$tmp = $cc;
					$cc = [];
					$cc[] = $tmp;
				}

				foreach ($cc AS $userId) {
					$user = $this->owner->user->getUserProfile($userId);
					$this->cc[$user['name']] = $user['email'];
				}
			}

			if ($bcc) {
				if (!is_array($bcc)) {
					$tmp = $bcc;
					$bcc = [];
					$bcc[] = $tmp;
				}

				foreach ($bcc AS $userId) {
					$user = $this->owner->user->getUserProfile($userId);
					$this->bcc[$user['name']] = $user['email'];
				}
			}

            if($attachments){
                foreach($attachments AS $filename => $file){
                    if(is_numeric($filename)) {
                        $filename = basename($file);
                    }
                    $this->addAttachment($file, $filename);
                }
            }

			$this->fillMailTemplate($data);
			$sent = $this->save();
		}

		return $sent;
	}

	private function loadMailTemplate($templateName){
        $template = $this->owner->lib->getTemplate($templateName);
		if($template) {
			$this->subject = $template['title'];
			$this->body = $template['text'];
			$this->tag = $template['tag'];
		}
	}

	private function fillMailTemplate($data){
        $this->subject = $this->owner->lib->replaceValues($this->subject, $data);
        $this->body = $this->owner->lib->replaceValues($this->body, $data);

		$this->body = $this->owner->view->renderContent('mail', ['contentstring' => $this->body, 'domain' => $this->host, 'heroImg' => $data['heroImg']], false);
	}

	private function getEmailSender(){
		return [
			'name'  => ($this->owner->settings['emailSenderName'] ?: EMAIL_SENDER_NAME),
			'email' => ($this->owner->settings['outgoingEmail'] ?:  EMAIL_SENDER_EMAIL),
		];
	}

	private function getAttachment($file){
        $filePath = DIR_CACHE . 'emails/' . $this->mailId . '/' . $file;
        if(file_exists($filePath)){
            return file_get_contents($filePath);
        }else{
            return false;
        }
    }
}
