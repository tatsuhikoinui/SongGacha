<?php
session_start();

// セッションでユーザー情報が保存されていない場合、ログインページにリダイレクト
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// POSTデータを取得
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['playlist_id'], $_POST['song_name'], $_POST['artist_name'])) {
    $playlist_id = $_POST['playlist_id'];
    $song_name = $_POST['song_name'];
    $artist_name = $_POST['artist_name'];

    // 外部ファイル読み込み
    require_once(__DIR__ . '/db.php');

    try {
        // データベース接続
        $dbh = new PDO("mysql:host=localhost;dbname=SongGacha;charset=utf8", $db_user, $db_pass);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // PDO接続実行時エラー設定

        // 曲をプレイリストに追加するクエリ実行
        $sql = 'INSERT INTO songs (playlist_id, song_name, artist_name) VALUES (?, ?, ?)';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $playlist_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $song_name, PDO::PARAM_STR);
        $stmt->bindValue(3, $artist_name, PDO::PARAM_STR);
        $stmt->execute();

        // データベース接続クローズ
        $dbh = null;

        // edit.phpにリダイレクト
        header("Location: edit.php?playlist_id=$playlist_id");
        exit;
    } catch (PDOException $e) {
        echo 'エラー発生' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
        exit;
    }
} else {
    // POSTデータが不足している場合はエラーメッセージを表示
    echo '曲を追加するための情報が不足しています。';
    echo '<a href="edit.php">戻る</a>';
}
