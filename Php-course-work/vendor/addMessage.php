<?php
session_start();
require_once 'connect.php';

$text = $_POST["sms"];

preg_match_all('/#\w+/', $text, $matches);
$hashtags = $matches[0];

$text_without_hashtags = preg_replace('/#\w+/', '', $text);

$hashtag_ids = array();
foreach ($hashtags as $hashtag) {
    $hashtag_name = str_replace('#', '', $hashtag);

    $check_hashtag = mysqli_query($connect, "SELECT id FROM hashtag WHERE name = '$hashtag_name'");
    if ($check_hashtag->num_rows) {
        $hashtag_data = mysqli_fetch_assoc($check_hashtag);
        $hashtag_ids[] = $hashtag_data['id'];
    } else {
        $sql = "INSERT INTO hashtag (name) VALUES ('$hashtag_name')";
        if ($connect->query($sql) === TRUE) {
            $hashtag_ids[] = $connect->insert_id;
        } else {
            echo "Error: " . $sql . "<br>" . $connect->error;
        }
    }
}

$sql = "INSERT INTO sms (`description`) VALUES ('$text_without_hashtags')";
if ($connect->query($sql) === TRUE) {
    $sms_id = $connect->insert_id;
    foreach ($hashtag_ids as $hashtag_id) {
        mysqli_query($connect, "INSERT INTO sms_hashtag (sms_id, hashtag_id) VALUES ('$sms_id', '$hashtag_id')");
    }
} else {
    echo "Error: " . $sql . "<br>" . $connect->error;
}

$_SESSION['message'] = 'Пост успешно добавлен!';
header('Location: ../addpost.php');
?>