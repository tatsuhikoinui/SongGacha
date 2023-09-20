<?php
session_start();

// セッションでユーザー情報が保存されていない場合、ログインページにリダイレクト
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// POSTデータから選択されたプレイリストIDを取得
if (!isset($_POST['playlist_id'])) {
    header("Location: main.php");
    exit;
}
$playlist_id = $_POST['playlist_id'];

// 外部ファイル読み込み
require_once(__DIR__ . '/db.php');

// 「はい」ボタンが押された場合
if (isset($_POST['confirm_yes'])) {
    try {
        // データベースに接続
        $dbh = new PDO("mysql:host=localhost;dbname=SongGacha;charset=utf8", $db_user, $db_pass);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // PDO接続実行時エラー設定

        // 関連する曲を削除するSQL文の実行
        $delete_songs_sql = 'DELETE FROM songs WHERE playlist_id = ?';
        $delete_songs_stmt = $dbh->prepare($delete_songs_sql);
        $delete_songs_stmt->bindValue(1, $playlist_id, PDO::PARAM_INT);
        $delete_songs_stmt->execute();

        // プレイリストを削除するSQL文の実行
        $delete_playlist_sql = 'DELETE FROM playlists WHERE playlist_id = ?';
        $delete_playlist_stmt = $dbh->prepare($delete_playlist_sql);
        $delete_playlist_stmt->bindValue(1, $playlist_id, PDO::PARAM_INT);
        $delete_playlist_stmt->execute();

        // データベース接続クローズ
        $dbh = null;

        // メインページにリダイレクト
        header("Location: main.php");
        exit;
    } catch (PDOException $e) {
        echo 'エラー発生' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
        exit;
    }
}

// 「いいえ」ボタンが押された場合
if (isset($_POST['confirm_no'])) {
    // edit.phpにリダイレクト
    header("Location: edit.php?playlist_id=$playlist_id");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Delete Confirmation</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
    <h2>プレイリスト削除確認</h2>
    <p>本当に削除してもよろしいですか？</p>

    <!-- 「はい」ボタンが押された場合の処理 -->
    <form class="form_normal" method="POST" action="delete_playlist.php">
        <input class="input_normal" type="hidden" name="playlist_id" value="<?php echo $playlist_id ?>">
        <button class="button_normal" type="submit" name="confirm_yes">はい</button>
    </form>

    <!-- 「いいえ」ボタンが押された場合の処理 -->
    <form class="form_normal" method="POST" action="delete_playlist.php">
        <input class="input_normal" type="hidden" name="playlist_id" value="<?php echo $playlist_id ?>">
        <button class="button_normal" type="submit" name="confirm_no">いいえ</button>
    </form>

    <!-- メインページへのリンク -->
    <p><a href="main.php">戻る</a></p>
</body>

</html>