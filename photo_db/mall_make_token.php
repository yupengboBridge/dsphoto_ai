<?php
function generateToken($secretKey)
{
    $timestamp = time();
    $token = hash_hmac('sha256', $timestamp, $secretKey);
    return array(
        'timestamp' => $timestamp,
        'token' => $token
    );
}
$secretKey = '1eoAeF7dGxxBvZZMX3qezmV9UULNcx';
$tokenData = generateToken($secretKey);
echo "Timestamp: " . $tokenData['timestamp'] . "\n";
echo "Token: " . $tokenData['token'] . "\n";
?>