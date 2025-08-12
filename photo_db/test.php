<?php
require_once __DIR__ . '/fetch_uliza_poster.php';

$client = new UlizaPosterClient();
$result = $client->fetchPoster('5113495421001');
if ($result['ok']) {
    echo $result['poster'];
} else {
    error_log($result['error']);
}
?>
