<?php
$fields = [
    'u_name' => 'u_name',
//        'tranid' => 'tranid',
//        'formid' => 'formid',
    'date_to' => 'date_to',
    'u_sname' => 'u_sname',
    'u_phone' => 'u_phone',
    'u_email' => 'u_email',
    'u_email_' => 'u_email',
    'date_from' => 'date_from',
    'u_surname' => 'u_surname',
    'tourist_count' => 'tourist_count',
    'note' => 'requirements_note',
    'payment' => 'payment',
    'formname' => 'formname',
    'requirements_note' => 'requirements_note',
    'u_snam' => 'u_sname',
    'extended_fields_45808' => 'extended_fields_97189',
    'extended_fields_67642' => 'extended_fields_97189',
    'extended_fields_431334679' => 'extended_fields_97189',
];

$data = json_decode(file_get_contents('php://input'), 1);
$key = $_REQUEST['key'];

$url = 'https://2019.u-on.ru/api/tilda_incoming.php?key=' . $key;
define("WT_BASE_PATH", __DIR__ . '/');


function send($url, $data = [])
{

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $data,
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}


$send = [];
foreach ($data as $k => $v) {
    if ($k == 'date_to' or $k == 'date_from') {
        $d = DateTime::createFromFormat('d/m/Y', $v);
        if (!$d) {
            $d = DateTime::createFromFormat('d-m-Y', $v);
        }
        if (!$d) {
            $d = DateTime::createFromFormat('Y/m/d', $v);
        }
        $v = $d->format(DATE_ATOM);
    }

    if (isset($fields[$k])) {
        $send[$fields[$k]] = $v;
    } else {
        $send[$k] = $v;
    }
}
$forms = json_decode(file_get_contents(WT_BASE_PATH . 'uon/uon.json'), 1) ?: [];
if (isset($data['formid']) && !isset($forms[$data['formid']])) {
    $forms[$data['formid']] = $data['extended_fields_97189'] ?: 'неизвестно';
    $id = (string)$data['formid'];
    file_put_contents(WT_BASE_PATH . "uon/req_{$id}.json", json_encode($data, 256 | JSON_PRETTY_PRINT));

}
file_put_contents(WT_BASE_PATH . 'uon/uon.json', json_encode($forms, 256 | JSON_PRETTY_PRINT));


if ($forms[$data['formid']] !== 'неизвестно') {
    $send['extended_fields_97189'] = $forms[$data['formid']];
}

send($url, $send);
echo "OK\n";
print_r($send);
