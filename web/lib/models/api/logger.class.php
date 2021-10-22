<?php
class logger extends ancestor {
    public function saveRequest(){
        if(API_LOG_REQUESTS) {
            $url = $this->getUrl();

            $data = $_SERVER['SERVER_PROTOCOL'] . "\n";
            $data .= $_SERVER['REQUEST_METHOD'] . ' ' . $url['url'] . "\n\n";

            $header = $this->owner->getRequestHeader();

            foreach ($header as $key => $value) {
                $data .= $key . ': ' . $value . "\n";
            }
            $data .= "Client IP: " . $this->owner->getClientIP() . "\n";
            $data .= "\n";

            if ($_SERVER['QUERY_STRING']) {
                $data .= "Query string:\n";
                $data .= "--------------------------------------------------------------------------------\n";
                parse_str($_SERVER['QUERY_STRING'], $query);
                foreach ($query as $key => $value) {
                    if (is_array($value)) {
                        $data .= str_replace('Array', $key . '=', print_r($value, true));
                    } else {
                        $data .= $key . '=' . $value . "\n";
                    }
                }
                $data .= "--------------------------------------------------------------------------------\n";
            }

            $body = file_get_contents('php://input');
            if ($body) {
                $data .= "Request body:\n";
                $data .= "--------------------------------------------------------------------------------\n";
                $data .= $body;
                $data .= "\n--------------------------------------------------------------------------------\n";
            }

            $this->saveLog($data, $url['service'], $url['action'], API_REQUEST);
        }
    }

    public function saveResponse($data, $fileFormat){
        if(API_LOG_RESPONSES) {
            $this->saveLog($data, $this->owner->getServiceName(), $this->owner->getActionName(), API_RESPONSE, $fileFormat);
        }
    }

    private function saveLog($data, $service, $action, $method, $ext = 'txt') {
        if ( defined('DIR_LOG') ) {
            $service = str_replace('_', '', $service);
            $action = str_replace('_', '', $action);

            $file_name    = (microtime(true) * 10000) . '_' . $service . '_' . $action . '_' . $this->owner->getMessageId() . '_'. SERVER_ID . '_' . $method;
            $file_name	 .= '.' . $ext;
            $folder_name  = DIR_LOG . 'api/' . date( 'Ym' ) . '/' . date( 'd' ) . '/';

            if(!is_dir($folder_name)){
                @mkdir($folder_name, 0777, true);
                @chmod($folder_name, 0777);
            }

            @file_put_contents( $folder_name . '/' . $file_name, $data, FILE_APPEND );
            return $folder_name . '/' . $file_name;
        } else {
            return false;
        }
    }

    private function getUrl(){
        $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $path = explode('/', trim(parse_url ( $url, PHP_URL_PATH ), '/'));
        array_shift($path);

        return [
            'url' => $url,
            'path' => $path,
            'service' => $this->owner->getServiceName(),
            'action' => $this->owner->getActionName(),
            'version' => $this->owner->getVersion()
        ];
    }
}