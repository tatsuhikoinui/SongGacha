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

$playlist_name = ''; // 変数の初期化

try {
    // データベースに接続
    $dbh = new PDO("mysql:host=localhost;dbname=SongGacha;charset=utf8", $db_user, $db_pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // PDO接続実行時エラー設定

    // プレイリスト名を取得
    $playlist_sql = 'SELECT playlist_name FROM playlists WHERE playlist_id = ?';
    $playlist_stmt = $dbh->prepare($playlist_sql);
    $playlist_stmt->bindValue(1, $playlist_id, PDO::PARAM_INT);
    $playlist_stmt->execute();
    $playlist_result = $playlist_stmt->fetch(PDO::FETCH_ASSOC);
    $playlist_name = $playlist_result['playlist_name'];

    // 未再生の曲を取得
    $unplayed_songs_sql = 'SELECT song_name, artist_name FROM songs WHERE playlist_id = ? AND played = 0';
    $unplayed_songs_stmt = $dbh->prepare($unplayed_songs_sql);
    $unplayed_songs_stmt->bindValue(1, $playlist_id, PDO::PARAM_INT);
    $unplayed_songs_stmt->execute();
    $unplayed_songs = $unplayed_songs_stmt->fetchAll(PDO::FETCH_ASSOC);

    $random_song = null; // 変数の初期化
    $no_songs = false; // プレイリスト内の曲がなくなったかのフラグ(ある場合)

    // 未再生の曲がある場合にランダムに曲を選ぶ
    if ($unplayed_songs) {
        shuffle($unplayed_songs);
        $random_song = array_shift($unplayed_songs); // 配列の先頭から要素を取り出し、その要素を変数に代入

        // 選んだ曲を再生済みとしてplayedにマーク
        $mark_played_sql = 'UPDATE songs SET played = 1 WHERE playlist_id = ? AND song_name = ?';
        $mark_played_stmt = $dbh->prepare($mark_played_sql);
        $mark_played_stmt->bindValue(1, $playlist_id, PDO::PARAM_INT);
        $mark_played_stmt->bindValue(2, $random_song['song_name'], PDO::PARAM_STR);
        $mark_played_stmt->execute();
    } else {
        // すべての曲を再生し終えた場合、playedをリセットする
        $reset_played_sql = 'UPDATE songs SET played = 0 WHERE playlist_id = ?';
        $reset_played_stmt = $dbh->prepare($reset_played_sql);
        $reset_played_stmt->bindValue(1, $playlist_id, PDO::PARAM_INT);
        $reset_played_stmt->execute();

        // プレイリスト内の曲がなくなったときのフラグを設定
        $no_songs = true;
    }

    $dbh = null;
} catch (PDOException $e) {
    echo 'エラー発生' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
    exit;
}


?>

<!DOCTYPE html>
<html>

<head>
    <title>Gacha Page</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
    <!-- 背景動画を表示するためのコンテナ -->
    <div class="video-background">
        <video>
            <!-- 動画のソースを指定（MP4形式の場合） -->
            <source src="background-video.mp4" type="video/mp4">
            <!-- ブラウザが<video>要素に対応していない場合に表示されるメッセージ -->
            Your browser does not support the video tag.
        </video>
    </div>
    
    <!-- ガチャの結果表示 -->
    <div class="gacha-result">
        <?php if (!$no_songs): ?>
            <?php if ($random_song != null): ?>
                <!-- 選ばれた曲情報を表示 -->
                <?php echo $songName = htmlspecialchars($random_song['song_name']); ?>
                <p>-</p>
                <?php echo $artistName = htmlspecialchars($random_song['artist_name']); ?>

                <!-- YouTube動画を表示する部分のコードを追加 -->
                <div class="youtube-video">
                    <!-- ここにYouTubeの埋め込みコードを記述 -->
                    <!-- 例: <iframe width="560" height="315" src="https://www.youtube.com/embed/動画ID" frameborder="0" allowfullscreen></iframe> -->
                </div>
            <?php endif; ?>

            <!-- ガチャを回すボタン -->
            <form method="GET" action="gacha.php">
                <input type="hidden" name="playlist_id" value="<?php echo $playlist_id; ?>">
                <button type="submit">ガチャを回す</button>
            </form>

        <?php else: ?>
            <!-- プレイリストに曲が存在しない場合のメッセージとボタン -->
            <p>プレイリスト内の曲がすべてなくなりました。</p>
            <form class="form_gacha" method="GET" action="gacha.php">
                <input type="hidden" name="playlist_id" value="<?php echo $playlist_id; ?>">
                <button class="button_gacha" type="submit">再度ガチャを回す</button>
            </form>
        <?php endif; ?>
    </div>

    <footer>
        <!-- メインページへのリンク -->
        <p><a href="main.php">ホームへ戻る</a></p>
    </footer>
</body>

</html>