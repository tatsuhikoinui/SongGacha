<!-- delete_account.php -->

<?php
session_start();

// セッションでユーザー情報が保存されていない場合、ログインページにリダイレクト
if (!isset($_SESSION['user_id'])) {
    header("Location:login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POSTリクエストの場合、アカウントを削除
    if (isset($_POST['confirm_yes'])) {
        // 外部ファイル読み込み
        require_once(__DIR__ . '/db.php');

        // ログインユーザーのIDを取得
        $user_id = $_SESSION['user_id'];

        try {
            // データベース接続
            $dbh = new PDO("mysql:host=localhost;dbname=SongGacha;charset=utf8", $db_user, $db_pass);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //PDO接続実行時エラー設定

            // ユーザーに関連するプレイリストを取得
            $playlists_sql = 'SELECT playlist_id FROM playlists WHERE user_id = ?';
            $playlists_stmt = $dbh->prepare($playlists_sql);
            $playlists_stmt->bindValue(1, $user_id, PDO::PARAM_INT);
            $playlists_stmt->execute();
            $playlists_result = $playlists_stmt->fetchAll(PDO::FETCH_ASSOC);

            // プレイリストごとに関連する曲を削除
            foreach ($playlists_result as $playlist) {
                $playlist_id = $playlist['playlist_id'];

                // プレイリストに関連する曲を削除
                $delete_songs_sql = 'DELETE FROM songs WHERE playlist_id = ?';
                $delete_songs_stmt = $dbh->prepare($delete_songs_sql);
                $delete_songs_stmt->bindValue(1, $playlist_id, PDO::PARAM_INT);
                $delete_songs_stmt->execute();
            }

            // ユーザーに関連するプレイリストを削除
            $delete_playlists_sql = 'DELETE FROM playlists WHERE user_id = ?';
            $delete_playlists_stmt = $dbh->prepare($delete_playlists_sql);
            $delete_playlists_stmt->bindValue(1, $user_id, PDO::PARAM_INT);
            $delete_playlists_stmt->execute();

            // ユーザー情報を削除
            $delete_user_sql = 'DELETE FROM users WHERE user_id = ?';
            $delete_user_stmt = $dbh->prepare($delete_user_sql);
            $delete_user_stmt->bindValue(1, $user_id, PDO::PARAM_INT);
            $delete_user_stmt->execute();

            // セッションを破棄し、ログアウト状態にする
            session_destroy();

            // インデックスページにリダイレクト
            header("Location: index.html");
            exit;
        } catch (PDOException $e) {
            echo 'エラー発生' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
            exit;
        }
    } else {
        // 「いいえ」が選択された場合、メインページに戻る
        header("Location: main.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>アカウント削除</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
    <h2>アカウント削除</h2>
    <p>本当に退会してもよろしいですか？</p>
    <form class="form_normal" method="POST" action="delete_account.php">
        <button class="button_normal" type="submit" name="confirm_yes">はい</button>
        <button class="button_normal" type="submit" name="confirm_no">いいえ</button>
    </form>
</body>

</html>
