<?php
/**
 * Class Test API calls
 */
class testService extends requester {

    public function init(): void {
        // TODO: Implement init() method.
    }

    public function get_Ping($id){
        return [
            'ip' => $this->owner->getClientIP(),
            'timestamp' => date(DATE_ATOM),
        ];
    }

    public function get_Exception($id){
        throw new apiException('API exception', 400, API_HTTP_BAD_REQUEST);
    }

    public function get_Wait($id){
        $time = (int) $_REQUEST['time'];

        if(!$time) {
            $time = 10;
        }

        sleep($time);

        return [
            'wait' => $time
        ];
    }

    public function post_Wait($id){
        $time = (int) $_REQUEST['time'];

        if(!$time) {
            $time = 10;
        }

        sleep($time);

        return [
            'wait' => $time
        ];
    }
}