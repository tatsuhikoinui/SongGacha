<?php
session_start();

// セッションでユーザー情報が保存されていない場合、ログインページにリダイレクト
if (!isset($_SESSION['user_id'])) {
    header("Location:login.php");
    exit;
}

// 外部ファイル読み込み
require_once(__DIR__ . '/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ユーザーIDを取得
    $user_id = $_SESSION['user_id'];

    // 入力されたプレイリスト名を取得
    $new_playlist_name = $_POST['new_playlist_name'];

    // プレイリスト名が空でないかチェック
    if (!empty($new_playlist_name)) {
        try {
            // データベース接続
            $dbh = new PDO("mysql:host=localhost;dbname=SongGacha;charset=utf8", $db_user, $db_pass);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 同じプレイリスト名が既に存在するかチェック
            $sql = 'SELECT * FROM playlists WHERE user_id = ? AND playlist_name = ?';
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
            $stmt->bindValue(2, $new_playlist_name, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                // 新しいプレイリストを作成
                $sql = 'INSERT INTO playlists (user_id, playlist_name) VALUES (?, ?)';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
                $stmt->bindValue(2, $new_playlist_name, PDO::PARAM_STR);
                $stmt->execute();

                echo '新しいプレイリストが作成されました。';
                echo '<a href="edit.php">戻る</a>';
            } else {
                echo '同じ名前のプレイリストが既に存在します。別の名前を選択してください。';
                echo '<a href="edit.php">戻る</a>';
            }

            // データベース接続クローズ
            $dbh = null;
        } catch (PDOException $e) {
            echo 'エラー発生' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
            exit;
        }
    } else {
        echo 'プレイリスト名を入力してください。';
        echo '<a href="edit.php">戻る</a>';
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>プレイリスト作成</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
</body>

</html>