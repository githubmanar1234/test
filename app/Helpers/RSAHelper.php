<?php


namespace App\Helpers;


class RSAHelper
{
    public function createKeyPairs(){


        $rsa = new RSA();
        $rsa->loadKey($publickey);
        $rsa->setEncryptionMode(2);
        $data = 'Your String';
        $output = $rsa->encrypt($data);
        echo base64_encode($output);
    }
}