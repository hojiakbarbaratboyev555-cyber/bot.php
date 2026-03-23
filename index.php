<?php

define('API_KEY','8604086213:AAGO5ZpbLl8KJ_HdzcX4szXgILy-Yy6uvrw');

function bot($method,$data=[]){
$url = "https://api.telegram.org/bot".API_KEY."/".$method;
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
$res = curl_exec($ch);
return json_decode($res);
}

// ====== SOZLAMALAR ======
$admin = 8223476380;
$kino_channel = "-1003729047115";
$join_channel = "-1003874159067";

// ====== JSON BAZA ======
if(!file_exists("data.json")){
file_put_contents("data.json", json_encode([
"users"=>[],
"movies"=>[]
]));
}

$data = json_decode(file_get_contents("data.json"), true);

// ====== UPDATE ======
$update = json_decode(file_get_contents("php://input"),true);
$message = $update["message"];
$text = $message["text"] ?? "";
$cid = $message["chat"]["id"] ?? "";
$mid = $message["message_id"] ?? "";

// ====== OBUNA TEKSHIRISH ======
function joinCheck($cid){
global $join_channel;
$res = bot("getChatMember",[
"chat_id"=>$join_channel,
"user_id"=>$cid
]);
$status = $res->result->status;
return ($status=="member" or $status=="administrator" or $status=="creator");
}

// ====== START ======
if($text == "/start"){
if(!joinCheck($cid)){
$kb = json_encode([
"inline_keyboard"=>[
[
["text"=>"📢 Obuna bo‘lish","url"=>"https://t.me/c/".str_replace("-100","",$join_channel)]
],
[
["text"=>"✅ Tekshirish","callback_data"=>"check"]
]
]
]);
bot("sendMessage",[
"chat_id"=>$cid,
"text"=>"❗ Avval kanalga obuna bo‘ling!",
"reply_markup"=>$kb
]);
exit;
}

bot("sendMessage",[
"chat_id"=>$cid,
"text"=>"🎬 Kino kodini yuboring:"
]);
}

// ====== KINO QO‘SHISH (ADMIN) ======
if($cid == $admin and $text == "/add"){
bot("sendMessage",[
"chat_id"=>$cid,
"text"=>"🎬 Kino yubor (video)"
]);
file_put_contents("step.txt","video");
exit;
}

$step = file_get_contents("step.txt");

if($cid == $admin and $step == "video" and isset($message["video"])){
$file_id = $message["video"]["file_id"];
file_put_contents("video.txt",$file_id);

bot("sendMessage",[
"chat_id"=>$cid,
"text"=>"✍️ Kino nomini yoz:"
]);

file_put_contents("step.txt","name");
exit;
}

if($cid == $admin and $step == "name"){
$name = $text;
$video = file_get_contents("video.txt");

$id = rand(1000,9999);

$data["movies"][$id] = [
"name"=>$name,
"file_id"=>$video
];

file_put_contents("data.json", json_encode($data));

bot("sendMessage",[
"chat_id"=>$cid,
"text"=>"✅ Qo‘shildi!\nKod: $id"
]);

bot("sendVideo",[
"chat_id"=>$kino_channel,
"video"=>$video,
"caption"=>"🎬 Kino kodi: $id"
]);

file_put_contents("step.txt","");
exit;
}

// ====== KINO QIDIRISH ======
if(is_numeric($text)){
if(isset($data["movies"][$text])){
$movie = $data["movies"][$text];

bot("sendVideo",[
"chat_id"=>$cid,
"video"=>$movie["file_id"],
"caption"=>"🎬 ".$movie["name"]
]);
}else{
bot("sendMessage",[
"chat_id"=>$cid,
"text"=>"❌ Bunday kino topilmadi"
]);
}
}
