<?php
header("Content-type: text/html; charset=UTF-8;");
$db = new mysqli("localhost","root","","quran");
$db->set_charset('utf8');
$result = $db->query("SELECT * FROM `quran_ar_en` WHERE `sura` = '113';");
$arr = $result->fetch_all(MYSQLI_ASSOC);
$result->free();

$result2 = $db->query("SELECT * FROM `quran_tr` WHERE `sura` = '113';");
$arr2 = $result2->fetch_all(MYSQLI_ASSOC);
$result2->free();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quranic Font Test</title>
    <link rel="stylesheet" href="style.css">
    <style>
    p{
        font-family: "Open Sans";
    }
    p[direction="rtl"]{
        font-family: _PDMS_Saleem_QuranFont;
        font-size: 32px;
    }
    </style>
</head>
<body>
    <?php
    foreach ($arr as $k=>$v) {
        $vv = $arr2[$k];
    ?>
        <p direction="rtl" style="direction:rtl;">
            <?=$v['text']?>
        </p>
        <p>
            <?=$vv['text']?>
        </p>
    <?php
    }
    ?>
</body>
</html>