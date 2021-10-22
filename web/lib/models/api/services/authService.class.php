<?php

class authService extends ancestor {
    private $clientId = 0;
    private $shopId = 0;
    private $client = [];

    public function getToken(){
    }

    public function checkToken(){
    }

    public function closeSession(){
    }

    public function checkApiKey($key) {
        $authenticated = false;
        if ($key){
            $client = $this->owner->db->getFirstRow(
                $this->owner->db->genSQLSelect(
                    'api_users',
                    [
                        'au_id AS id',
                        'au_shop_id AS shopId',
                        'au_last_request AS lastRequest',
                        'au_ip_whitelist AS ipWhiteList',
                        'au_services AS services',
                        'au_expiry AS expiry',
                    ],
                    [
                        'au_api_key' => $key,
                        'au_enabled' => 1
                    ]
                )
            );
            if (!empty($client)) {
                if (!empty($client['expiry']) && $client['expiry'] < date('Y-m-d')) {
                    throw new apiException('API Key expired', 9, API_HTTP_UNAUTHORIZED);
                } else {
                    $authenticated = $this->validateClient($client);
                }
            } else {
                throw new apiException('Invalid API Key provided', 8, API_HTTP_UNAUTHORIZED);
            }
        }else{
            throw new apiException('API Key is missing', 7, API_HTTP_UNAUTHORIZED);
        }

        return $authenticated;
    }

    public function authenticate($username, $password){
        $authenticated = false;

        $client = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'api_users',
                [
                    'au_id AS id',
                    'au_username AS username',
                    'au_password AS password',
                    'au_shop_id AS shopId',
                    'au_last_request AS lastRequest',
                    'au_ip_whitelist AS ipWhiteList',
                    'au_services AS services',
                    'au_failedlogins AS failedLogins',
                    'au_enabled AS enabled',
                ],
                [
                    'au_username' => $username,
                    'au_enabled' => [
                        'in' => [1,2]
                    ]
                ]
            )
        );
        if (!empty($client) && password_verify($password, $client['password']) && $client['enabled'] == 1) {
            unset($client['password'], $client['enabled'], $client['failedLogins']);

            $authenticated = $this->validateClient($client);

        }else{
            $code = 1;
            $message = 'Invalid login credentials';
            $data = [];

            // login failed
            if (!empty($client)) {
                // user exists and enabled, password was wrong
                $updateFields = [
                    'au_failedlogins' => ($client['failedLogins'] + 1)
                ];

                $data = [
                    'loginAttemptsLeft' => API_MAX_LOGIN_ATTEMPT - $updateFields['au_failedlogins']
                ];

                if ($updateFields['au_failedlogins'] >= API_MAX_LOGIN_ATTEMPT) {
                    $data = [];
                    $code = 5;
                    $updateFields['au_enabled'] = 2;
                    $message = 'Maximum login attempts exceeded, user is blocked.';
                }

                $this->owner->db->sqlQuery(
                    $this->owner->db->genSQLUpdate(
                        'api_users',
                        $updateFields,
                        [
                            'au_id' => $client['id']
                        ]
                    )
                );

            }

            throw new apiException($message, $code, API_HTTP_UNAUTHORIZED, $data);
        }

        return $authenticated;
    }

    public function getClient(){
        return $this->client;
    }

    private function validateClientIP(){
        $valid = true;

        if($this->client['ipWhiteList'] && is_array($this->client['ipWhiteList'])) {
            $ip = $this->owner->getClientIP();
            $valid = in_array($ip, $this->client['ipWhiteList']);
        }

        return $valid;
    }

    private function validateService(){
        $valid = true;

        if($this->client['services'] && is_array($this->client['services'])) {
            $valid = in_array($this->owner->getServiceName(), $this->client['services']);
        }

        return $valid;
    }

    private function validateClient(array $client){
        $validated = false;
        $this->client = $client;

        if(!Empty($client['services'])) {
            $this->client['services'] = explode('|', trim($client['services'], '|'));
        }

        if(!Empty($client['ipWhiteList'])) {
            $this->client['ipWhiteList'] = explode('|', trim($client['ipWhiteList'], '|'));
        }

        if($this->validateClientIP()){

            if($this->validateService()) {

                $this->clientId = $client['id'];
                $this->shopId = $client['shopId'];

                $this->owner->db->sqlQuery(
                    $this->owner->db->genSQLUpdate(
                        'api_users',
                        [
                            'au_failedlogins' => 0,
                            'au_last_request' => 'NOW()',
                        ],
                        [
                            'au_id' => $this->clientId
                        ]
                    )
                );

                $validated = true;
            }else{
                $this->client = [];

                throw new apiException('Service is not allowed', 6, API_HTTP_UNAUTHORIZED);
            }
        }else{
            $this->client = [];

            throw new apiException('IP address is not allowed', 4, API_HTTP_UNAUTHORIZED);
        }

        return $validated;
    }
}