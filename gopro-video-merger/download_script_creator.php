<?php

$ids = file_get_contents('ids.csv');
$ids = explode(',', $ids);

$output = '';
$idsForBatch = array();
$offset = 0;

$access_token = '';

foreach ($ids as $id) {
    if ($offset === 20) {
        $fileName = md5(implode('', $idsForBatch)) . '.zip';
        $output .= 'wget "https://api.gopro.com/media/x/zip/source?ids=' . implode(',', $idsForBatch) . '&access_token=' . $access_token . '" -O "' . $fileName . '"' . PHP_EOL;
        $offset = 0;
        $idsForBatch = array();
    }

    $idsForBatch[] = $id;
    $offset += 1;
}

if (count($idsForBatch) !== 0) {
    $fileName = md5(implode('', $idsForBatch)) . '.zip';
    $output .= 'wget "https://api.gopro.com/media/x/zip/source?ids=' . implode(',', $idsForBatch) . '&access_token=' . $access_token . '" -O "' . $fileName . '"' . PHP_EOL;
    $offset = 0;
    $idsForBatch = array();
}

file_put_contents('download_script.sh', $output);

