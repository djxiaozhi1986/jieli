<?php
return [
    'debug'  => false,
   // 'app_id' => 'wx67a9da4172e72b4b',
   // 'secret' => '4c1b4673d384c499232f387f01fe1c76',
   // 'token'=>'testmp',
    'app_id' => 'wx7866d0da0d180929',
    'secret' => '0d74c87d78e6ede4ca8fa0e69b468736',
    'token'  => 'ncai_auth_wechat_2017',
    'aes_key' => 'zxhWOKIrmjigaderaTb8vEkOmwfwLZ8c4xRckruVPqc', // 可选
    'oauth' => [
        'scopes'   => ['snsapi_userinfo'],
        'callback' => '/oauth/wechat/oauth_callback',
    ]
];
