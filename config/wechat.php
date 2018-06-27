<?php
return [
    'debug'  => false,
    'app_id' => 'wxa9d31128927be30d',
    'secret' => '3662c06b4d552c316b63ce54a7b9567c',
    'token'  => 'jl_auth_wechat_2018',
    'aes_key' => 'GCe5UVmfEenL475IOEHoMvIpMbEW2IEO5WnkCryPotm', // 可选
    'oauth' => [
        'scopes'   => ['snsapi_userinfo'],
        'callback' => '/oauth/wechat/oauth_callback',
    ]
];
