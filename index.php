<?php

error_reporting(0);
date_default_timezone_set('Asia/Tashkent');

define('API_KEY','8604086213:AAG6PYOShMMb6F1ToA-eCM62jiEwPKB0270');
$admin = 8223476380;

/* FILES */
if(!file_exists("data.json")) file_put_contents("data.json","{}");
if(!file_exists("channels.txt")) file_put_contents("channels.txt","");
if(!file_exists("step.txt")) file_put_contents("step.txt","none");

$data = json_decode(file_get_contents("data.json"),true);
$step = file_get_contents("step.txt");

function save($arr){
file_put_contents("data.json",json_encode($arr));
}

function bot($method,$datas=[]){
$url = "https://api.telegram.org/bot".API_KEY."/".$method;
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_POSTFIELDS,$datas);
$res = curl_exec($ch);
return json_decode($res);
}

function sendMessage($cid,$text,$key=null){
return bot('sendMessage',[
'chat_id'=>$cid,
'text'=>$text,
'parse_mode'=>"HTML",
'reply_markup'=>$key
]);
}

function sendVideo($cid,$file,$cap=null){
return bot('sendVideo',[
'chat_id'=>$cid,
'video'=>$file,
'caption'=>$cap,
'parse_mode'=>"HTML",
'protect_content'=>true
]);
}

/* JOIN CHECK */
function joinCheck($user){
$channels = file("channels.txt", FILE_IGNORE_NEW_LINES);

foreach($channels as $ch){
if(trim($ch)=="") continue;

$get = bot('getChatMember',[
'chat_id'=>$ch,
'user_id'=>$user
]);

$status = $get->result->status;

if($status!="member" and $status!="creator" and $status!="administrator"){
return false;
}
}
return true;
}

/* UPDATE */
$update = json_decode(file_get_contents('php://input'));

$message = $update->message;
$callback = $update->callback_query;

$cid = $message->chat->id;
$text = $message->text;
$video = $message->video;

$data_cb = $callback->data;
$cid_cb = $callback->message->chat->id;
$qid = $callback->id;

/* PANEL */
$panel = json_encode([
'keyboard'=>[
[['text'=>"🎬 Kino qo'shish"]],
[['text'=>"📊 Statistika"]],
[['text'=>"➕ Kanal qo'shish"],['text'=>"➖ Kanal o'chirish"]]
],
'resize_keyboard'=>true
]);

/* START */
if($text=="/start"){

if(!joinCheck($cid)){
$channels = file("channels.txt", FILE_IGNORE_NEW_LINES);
$btn = [];

foreach($channels as $ch){
$btn[][]=['text'=>$ch,'url'=>"https://t.me/".str_replace("@","",$ch)];
}

$btn[][]=['text'=>"✅ Tekshirish",'callback_data'=>"check"];

sendMessage($cid,"❗ Kanallarga obuna bo‘ling",json_encode(['inline_keyboard'=>$btn]));
exit();
}

sendMessage($cid,"🎬 Kino kod yuboring");
}

/* CHECK */
if($data_cb=="check"){
if(joinCheck($cid_cb)){
bot('answerCallbackQuery',[
'callback_query_id'=>$qid,
'text'=>"✅ Tasdiqlandi"
]);
sendMessage($cid_cb,"🎬 Kod yuboring");
}else{
bot('answerCallbackQuery',[
'callback_query_id'=>$qid,
'text'=>"❌ Obuna bo‘ling",
'show_alert'=>true
]);
}
}

/* ADMIN PANEL */
if($text=="/panel" and $cid==$admin){
sendMessage($cid,"Admin panel",$panel);
}

/* ADD CHANNEL */
if($text=="➕ Kanal qo'shish" and $cid==$admin){
file_put_contents("step.txt","add_channel");
sendMessage($cid,"Kanal username yuboring (@kanal)");
}

if($step=="add_channel" and $cid==$admin){
file_put_contents("channels.txt",$text."\n",FILE_APPEND);
file_put_contents("step.txt","none");
sendMessage($cid,"✅ Kanal qo‘shildi");
}

/* DELETE CHANNEL */
if($text=="➖ Kanal o'chirish" and $cid==$admin){
$channels = file("channels.txt");
$list = "Kanallar:\n";

foreach($channels as $ch){
$list .= $ch;
}

sendMessage($cid,$list."\nO'chirish uchun username yuboring");
file_put_contents("step.txt","del_channel");
}

if($step=="del_channel" and $cid==$admin){
$channels = file("channels.txt", FILE_IGNORE_NEW_LINES);
$new = [];

foreach($channels as $ch){
if(trim($ch)!=trim($text)){
$new[]=$ch;
}
}

file_put_contents("channels.txt",implode("\n",$new));
file_put_contents("step.txt","none");

sendMessage($cid,"❌ Kanal o‘chirildi");
}

/* ADD MOVIE */
if($text=="🎬 Kino qo'shish" and $cid==$admin){
file_put_contents("step.txt","video");
sendMessage($cid,"Video yuboring");
}

if($video and $step=="video" and $cid==$admin){
file_put_contents("video.txt",$video->file_id);
file_put_contents("step.txt","caption");
sendMessage($cid,"Kino nomini yuboring");
}

if($step=="caption" and $cid==$admin and $text){
$file = file_get_contents("video.txt");
$code = count($data)+1;

$data[$code]=[
"file"=>$file,
"name"=>$text
];

save($data);
file_put_contents("step.txt","none");

sendMessage($cid,"✅ Saqlandi

Kod: <code>$code</code>");
}

/* USER REQUEST */
if(is_numeric($text)){

if(!joinCheck($cid)){
sendMessage($cid,"❗ Avval kanallarga obuna bo‘ling");
exit();
}

if(isset($data[$text])){
sendVideo($cid,$data[$text]['file'],"🎬 ".$data[$text]['name']);
}else{
sendMessage($cid,"❌ Topilmadi");
}
}

/* STATS */
if($text=="📊 Statistika" and $cid==$admin){
$count = count($data);
sendMessage($cid,"🎬 Kinolar soni: $count");
}
