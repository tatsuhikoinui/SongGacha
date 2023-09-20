<?php
session_start();

// セッションでユーザー情報が保存されていない場合、ログインページにリダイレクト
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// POSTリクエストが送信された場合のみ処理を実行
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['song_id']) && isset($_POST['playlist_id'])) {
    $song_id = $_POST['song_id'];
    $playlist_id = $_POST['playlist_id'];

    // 外部ファイル読み込み
    require_once(__DIR__ . '/db.php');

    try {
        // データベースに接続
        $dbh = new PDO("mysql:host=localhost;dbname=SongGacha;charset=utf8", $db_user, $db_pass);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // PDO接続実行時エラー設定

        // 曲の削除
        $delete_sql = 'DELETE FROM songs WHERE song_id = ?';
        $delete_stmt = $dbh->prepare($delete_sql);
        $delete_stmt->bindValue(1, $song_id, PDO::PARAM_INT);
        $delete_stmt->execute();

        // データベース接続クローズ
        $dbh = null;

        // プレイリストの編集ページにリダイレクト
        header("Location: edit.php?playlist_id=$playlist_id");
        exit;
    } catch (PDOException $e) {
        echo 'エラー発生' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
        exit;
    }
} else {
    // POSTリクエストが正しく送信されていない場合、メインページにリダイレクト
    header("Location: main.php");
    exit;
}
