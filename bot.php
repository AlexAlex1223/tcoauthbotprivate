<?php
declare(strict_types=1);
define('TOKEN', '8016689811:AAGxAHHII0Lc4TRtVk6fDpu_ZsG9lCOEMLk');
define('ADMIN_ID', '1064557215');

file_put_contents('bot_log.txt', date('Y-m-d H:i:s').' - '.json_encode(file_get_contents('php://input'))."\n", FILE_APPEND);

$request = json_decode(file_get_contents('php://input'), true);
if (!$request || empty($request['message'])) {
    echo "OK"; exit;
}

$text = trim($request['message']['text']);
$chat_id = (string)$request['message']['chat']['id'];
$data = json_decode(file_get_contents('keys.json') ?: '{}', true);

if ($chat_id === ADMIN_ID && preg_match('/^\/(add|del|set)\s+(\w+)(?:\s+(\d+))?$/', $text, $m)) {
    [, $cmd, $key, $dur] = array_pad($m, 4, null);
    switch ($cmd) {
        case 'add': $data[$key] = ($dur>0 ? time()+$dur*3600:0); $msg="✅ Ключ $key добавлен"; break;
        case 'del': unset($data[$key]); $msg="❌ Ключ $key удалён"; break;
        case 'set': $msg = isset($data[$key]) ? 
            ($data[$key] = time() + ($dur>0?$dur*3600:0), "🔄 Обновлён срок $key") :
            "❗ Ключ не найден"; break;
    }
    file_put_contents('keys.json', json_encode($data));
    sendMsg($chat_id, $msg);
    exit;
}

if (preg_match('/^\/auth\s+(\w+)$/', $text, $m)) {
    $key = $m[1];
    if (!isset($data[$key])) { sendMsg($chat_id,"❌ Ключ не найден"); exit; }
    if ($data[$key]>0 && time()>$data[$key]) { sendMsg($chat_id,"⏰ Ключ просрочен"); exit; }
    $ip=$_SERVER['REMOTE_ADDR']; $hwid=$_GET['hwid'] ?? 'unknown'; $device=$_GET['device'] ?? 'unknown';
    $time=date('Y-m-d H:i:s');
    sendMsg(ADMIN_ID, "✅ Авторизация\n🕒 $time\n🌍 IP: $ip\n🧠 HWID: $hwid\n📱 $device\n🔑 $key");
    sendMsg($chat_id,"✅ Авторизация успешна");
}

function sendMsg(string $chat, string $text): void {
    file_get_contents("https://api.telegram.org/bot".TOKEN."/sendMessage?chat_id={$chat}&text=".urlencode($text));
}
