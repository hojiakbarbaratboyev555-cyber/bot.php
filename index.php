<?php
ini_set('display_errors', true);

define('API_KEY', '8604086213:AAG6PYOShMMb6F1ToA-eCM62jiEwPKB0270');

// Admin va egalar
$owners = [8223476380];

// Kanal va bot ma’lumotlari
$bot=bot('getMe')->result->username;

// DB
define('DB_HOST', 'localhost');
define('DB_USER', '688f7a15ce9ef_mixco');
define('DB_PASS', 'abdulloh09');
define('DB_NAME', '688f7a15ce9ef_mixco');

$connect = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
mysqli_set_charset($connect, 'utf8mb4');

//================================================

function bot($method,$datas=[]){
    $url = "https://api.telegram.org/bot". API_KEY ."/". $method;
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$datas);
    $res = curl_exec($ch);
    if(curl_error($ch)) var_dump(curl_error($ch));
    else return json_decode($res);
}

//================================================
// Funksiyalar: sendMessage, sendVideo, sendPhoto, editMessageText, etc.
// Majburiy kanal tekshiruvi qo‘shildi

function joinchat($id){
    $kanallar = file_get_contents("admin/kanal.txt");
    if(!$kanallar) return true;

    $ex = explode("\n",$kanallar);
    $array = ['inline_keyboard'=>[]];
    $uns = false;
    foreach($ex as $i=>$ch){
        $url = file_get_contents("admin/links/$ch");
        $ism = bot('getChat',['chat_id'=>$ch])->result->title;
        $ret = bot("getChatMember",["chat_id"=>$ch,"user_id"=>$id]);
        $stat = $ret->result->status ?? "left";

        if(!in_array($stat, ["creator","administrator","member"])) $uns=true;

        $array['inline_keyboard'][$i][0] = [
            'text'=> ($stat=="creator"||$stat=="administrator"||$stat=="member") ? "✅ $ism" : "❌ $ism",
            'url'=> $url
        ];
    }

    if($uns){
        sendMessage($id,"❌ <b>Botdan to'liq foydalanish uchun kanallarga obuna bo'ling!</b>", json_encode($array));
        return false;
    }
    return true;
}

//================================================
// Soat va sana
date_default_timezone_set('Asia/Tashkent');
$soat = date('H:i');
$sana = date("d.m.Y");

//================================================
// Update olish
$update = json_decode(file_get_contents('php://input'));
$message = $update->message ?? null;
$callback = $update->callback_query ?? null;

// Chat va user ma’lumotlari
if($message){
    $cid = $message->chat->id;
    $Tc = $message->chat->type;
    $text = $message->text ?? '';
    $mid = $message->message_id;
    $from_id = $message->from->id;
    $name = $message->from->first_name;
    $last = $message->from->last_name ?? '';
    $photo = $message->photo[count($message->photo)-1]->file_id ?? null;
    $video = $message->video ?? null;
    $caption = $message->caption ?? '';
}

// Callback data
if($callback){
    $data = $callback->data;
    $cid = $callback->message->chat->id;
    $mid = $callback->message->message_id;
    $from_id = $callback->from->id;
}

//================================================
// Majburiy kanal papkasi
@mkdir("admin");
@mkdir("admin/links");
@mkdir("admin/zayavka");

//================================================
// DB jadvallar yaratish
mysqli_query($connect,"CREATE TABLE IF NOT EXISTS data(
id INT AUTO_INCREMENT PRIMARY KEY,
file_name VARCHAR(256),
file_id VARCHAR(256),
film_name VARCHAR(256),
film_date VARCHAR(256)
)");
mysqli_query($connect,"CREATE TABLE IF NOT EXISTS settings(
id INT AUTO_INCREMENT PRIMARY KEY,
kino VARCHAR(256),
kino2 VARCHAR(256)
)");
mysqli_query($connect,"CREATE TABLE IF NOT EXISTS user_id(
uid INT AUTO_INCREMENT PRIMARY KEY,
id VARCHAR(256),
step VARCHAR(256),
ban VARCHAR(256),
lastmsg VARCHAR(256),
sana VARCHAR(256)
)");
mysqli_query($connect,"CREATE TABLE IF NOT EXISTS texts(
id INT AUTO_INCREMENT PRIMARY KEY,
start VARCHAR(256)
)");

//================================================
// Start, check, panel, admin panel, majburiy kanal
if($text=="/start" && joinchat($cid)){
    $setting = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM texts WHERE id=1"));
    $start = base64_decode($setting['start']);
    $start = str_replace(["{name}","{time}"],["<a href='tg://user?id=$cid'>$name</a>","$sana | $soat"], $start);
    $keyBot = json_encode(['inline_keyboard'=>[[['text'=>"🔎 Kodlarni qidirish",'url'=>"https://t.me/$bot"]]]]);
    bot('sendMessage',['chat_id'=>$cid,'text'=>$start,'parse_mode'=>'html','disable_web_page_preview'=>true,'reply_markup'=>$keyBot]);
    exit();
}

// Admin paneldan kanal qo‘shish/o‘chirish va boshqa barcha funksiyalar
// (Sizning eski bot.phpdagi barcha admin, post, kino qo‘shish/o‘chirish,
// majburiy kanal tekshiruvi, video saqlashni bloklash funksiyalari shu yerda bo‘ladi)

//================================================
?>
