<?php
session_start();

// ログイン済みの場合、main.phpへリダイレクト
if (isset($_SESSION['user_id'])) {
    header("Location: main.php");
    exit;
}

//formから送られてきたpostデータを取得
$username = $_POST['username'];
$password = $_POST['password'];

//外部ファイル読み込み
require_once(__DIR__ . '/db.php');

// フォームが送信された場合の処理
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 入力値が空でないかチェック
    if (!empty($username) && !empty($password)) {
        try {
            // データベース接続
            $dbh = new PDO('mysql:host=localhost;dbname=SongGacha;charset=utf8', $db_user, $db_pass);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //PDO接続実行時エラー設定

            // SQLクエリ実行
            $sql = 'SELECT * FROM users WHERE username=? AND password=?';
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(1, $username, PDO::PARAM_STR);
            $stmt->bindValue(2, $password, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($result != null) {
                // ログイン成功時の処理
                foreach ($result as $row) {
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['username'] = $row['username'];
                }
                // main.phpへダイレクト
                header('Location:main.php');
            } else {
                // ログイン失敗時の処理
                echo 'アカウント情報が見つかりません';
            }
            // データベース接続クローズ
            $dbh = null;
        } catch (PDOException $e) {
            echo 'エラー発生' . htmlspecialchars($e->getMessage(), ENT_QUOTES) . '<br>';
            exit;
        }
    } else {
        echo '入力欄に空欄があります';
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
    <h2>ログインページ</h2>
    <form class="form_normal" method="post">
        <label>ID:</label>
        <input class="input_normal" type="text" name="username" required><br>
        <label>PASS:</label>
        <input class="input_normal" type="password" name="password" required><br>
        <button class="button_normal" type="submit">LOGIN</button>
    </form>
    <!-- register.phpへのリンク -->
    <p><a href="register.php">新規会員登録</a></p>
    <!-- index.htmlへのリンク -->
    <p><a href="index.html">TOPページ</a></p>
</body>

</html>