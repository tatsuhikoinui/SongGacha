<?php
session_start();

// セッションでユーザー情報が保存されていない場合、ログインページにリダイレクト
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// URLパラメータから選択されたプレイリストIDを取得
if (!isset($_GET['playlist_id'])) {
    header("Location: main.php");
    exit;
}
$playlist_id = $_GET['playlist_id'];

// 外部ファイル読み込み
require_once(__DIR__ . '/db.php');

try {
    // データベースに接続
    $dbh = new PDO("mysql:host=localhost;dbname=SongGacha;charset=utf8", $db_user, $db_pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //PDO接続実行時エラー設定

    // プレイリスト名を取得
    $playlist_sql = 'SELECT playlist_name FROM playlists WHERE playlist_id = ?';
    $playlist_stmt = $dbh->prepare($playlist_sql);
    $playlist_stmt->bindValue(1, $playlist_id, PDO::PARAM_INT);
    $playlist_stmt->execute();
    $playlist_result = $playlist_stmt->fetch(PDO::FETCH_ASSOC);
    $playlist_name = $playlist_result['playlist_name'];

    // 選択されたプレイリストに関連する曲の情報を取得
    $songs_sql = 'SELECT * FROM songs WHERE playlist_id = ?';
    $songs_stmt = $dbh->prepare($songs_sql);
    $songs_stmt->bindValue(1, $playlist_id, PDO::PARAM_INT);
    $songs_stmt->execute();
    $songs_result = $songs_stmt->fetchAll(PDO::FETCH_ASSOC);

    // データベース接続クローズ
    $dbh = null;
} catch (PDOException $e) {
    echo 'エラー発生' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Page</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
    <h2>プレイリスト編集</h2>

    <!-- 選択されたプレイリスト名を表示 -->
    <p>プレイリスト名: <?php echo $playlist_name; ?></p>

    <h3>登録されている曲一覧</h3>

    <?php if ($songs_result != null) : ?>
        <!-- プレイリスト内の曲情報を一覧表示 -->
        <table>
            <tr>
                <th>曲名</th>
                <th></th>
                <th>歌手名</th>
                <th>曲を削除</th>
            </tr>
            <?php foreach ($songs_result as $row) : ?>
                <tr>
                    <!-- 曲の削除ボタン。ボタンがクリックされた時に削除処理を行う -->


                    <td><?php echo $row['song_name']; ?></td>
                    <td>-</td>
                    <td><?php echo $row['artist_name']; ?></td>
                    <td>
                        <form class="form_delete" method="POST" action="delete_song.php">
                            <input type="hidden" name="song_id" value="<?php echo $row['song_id']; ?>">
                            <button class="button_delete" type="submit">削除</button>
                        </form>
                    </td>


                </tr>
            <?php endforeach; ?>
        </table>
    <?php else : ?>
        <!-- プレイリストに曲が存在しない場合のメッセージ -->
        <p>現在は曲が登録されていません</p>
    <?php endif; ?>

    <!-- 曲を追加するフォーム -->
    <h3>プレイリストに曲を追加する</h3>
    <form class="form_normal" method="post" action="add_song.php">
        <input class="input_normal" type="hidden" name="playlist_id" value="<?php echo $playlist_id; ?>">
        <label>曲名：</label>
        <input class="input_normal" type="text" name="song_name" required><br>
        <label>アーティスト名：</label>
        <input class="input_normal" type="text" name="artist_name" required><br>
        <button class="button_normal" type="submit">追加</button>
    </form>

    <!-- プレイリスト削除フォーム -->
    <h3>プレイリストを削除する</h3>
    <form class="form_delete" method="POST" action="delete_playlist.php">
        <input type="hidden" name="playlist_id" value="<?php echo $playlist_id ?>">
        <button class="button_delete" type="submit">削除</button>
    </form>

    <footer>
        <!-- メインページへのリンク -->
        <p><a href="main.php">戻る</a></p>
    </footer>
</body>

</html>