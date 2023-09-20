<?php
session_start();

// セッションでユーザー情報が保存されていない場合、ログインページにリダイレクト
if (!isset($_SESSION['user_id'])) {
    header("Location:login.php");
    exit;
}

// ログインユーザーのIDを取得
$user_id = $_SESSION['user_id'];

// 外部ファイル読み込み
require_once(__DIR__ . '/db.php');

try {
    // データベース接続
    $dbh = new PDO("mysql:host=localhost;dbname=SongGacha;charset=utf8", $db_user, $db_pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //PDO接続実行時エラー設定

    // SQLクエリ実行
    $sql = 'SELECT * FROM playlists WHERE user_id = ?';
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // フォームが送信された場合かつプレイリストとアクションが選択されている場合に処理を実行
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['playlist_choice']) && isset($_POST['action_choice'])) {
        $playlist_choice = $_POST['playlist_choice']; // 選択されたプレイリストのIDを取得
        $action_choice = $_POST['action_choice']; // 選択されたアクションを取得

        // 選択されたアクションに基づいてリダイレクト先を設定
        if ($action_choice == 'edit') {
            $redirect_url = "edit.php?playlist_id=$playlist_choice"; // 編集ページへのURL
        } elseif ($action_choice == 'gacha') {
            $redirect_url = "gacha.php?playlist_id=$playlist_choice"; // ガチャページへのURL
        }

        // リダイレクト処理を実行
        header("Location: $redirect_url");
        exit; // リダイレクト後にスクリプトの実行を終了
    }

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
    <title>Main Page</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
    <h2>ホーム</h2>

    <form class="form_normal" method="post">
        <?php if ($result != null) : ?>
            <!-- プレイリストが有る場合 -->
            <label>プレイリストを選んでください</label>
            <select name="playlist_choice">
                <?php foreach ($result as $row) : ?>
                    <option value="<?php echo $row['playlist_id']; ?>">
                        <?php echo $row['playlist_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select><br>

            <label>どちらか選んでください:</label><br>
            編集する:<input class="input_normal" type="radio" name="action_choice" value="edit" checked>
            ガチャを回す:<input class="input_normal" type="radio" name="action_choice" value="gacha"><br>
            <button class="button_normal" type="submit">確定</button>
        <?php else : ?>
            <!-- プレイリストが無い場合 -->
            プレイリストを作成すると項目が表示されます
        <?php endif; ?>

    </form>

    <!-- 新規プレイリスト作成フォーム -->
    <h3>プレイリストを作成する</h3>
    <form class="form_normal" method="post" action="create_playlist.php">
        <label>新規プレイリスト名:</label>
        <input class="input_normal" type="text" name="new_playlist_name" required>
        <button class="button_normal" type="submit">作成</button>
    </form>

    <footer>
        <!-- ログアウトリンク -->
        <a href="logout.php">ログアウト</a>
        <!-- 退会リンク -->
        <a href="delete_account.php">退会</a>
    </footer>
</body>

</html>