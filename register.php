<?php
// 外部ファイル読み込み
require_once(__DIR__ . '/db.php');

// formから送られてきたpostデータを取得
$username = $_POST['username'];
$password = $_POST['password'];
$password_check = $_POST['password_check'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 入力値が空でないかチェック
    if (!empty($username) && !empty($password) && !empty($password_check)) {
        // パスワードとパスワード確認が一致するかチェック
        if ($password === $password_check) {
            try {
                // データベース接続 
                $dbh = new PDO('mysql:host=localhost;dbname=SongGacha;charset=utf8', $db_user, $db_pass); //接続情報
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //PDO接続実行時エラー設定

                // SQLクエリ実行
                $sql = 'SELECT * FROM users WHERE username = ?'; //同一usernameの存在を確認
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $username, PDO::PARAM_STR);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($result == null) {

                    $sql = 'INSERT INTO users (username, password) VALUES(?,?)'; //usersテーブルにusernameとpasswordを追加

                    $stmt = $dbh->prepare($sql); //queryだとbindValue取得する前に実行してしまうのでprepare
                    $stmt->bindValue(1, $username, PDO::PARAM_STR);
                    $stmt->bindValue(2, $password, PDO::PARAM_STR);
                    $stmt->execute(); //アカウント生成するだけなのでRESULT文の必要がない
                    $dbh = null;
                    echo 'ユーザー登録が完了しました。<br><a href="index.html">ログイン</a>';
                } else {
                    echo 'このIDは他のユーザが使用しています。別のIDで登録してください<br>';
                    echo '<a href="index.html">トップページに戻る</a>';
                }
                // データベース接続クローズ
                $dbh = null;
            } catch (PDOException $e) {
                echo 'エラー発生' . htmlspecialchars($e->getMessage(), ENT_QUOTES) . '<br>';
                exit;
            }
        } else {
            echo 'パスワードが一致していません';
        }
    } else {
        echo '入力欄に空欄があります';
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>REGISTER</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
    <h2>新規会員登録ページ</h2>
    <form class="form_normal" method="post">
        <label>ID:</label>
        <input class="input_normal" type="text" name="username" required><br>
        <label>PASS:</label>
        <input class="input_normal" type="password" name="password" required><br>
        <label>PASS:</label>
        <input class="input_normal" type="password" name="password_check" required><br>
        <button class="button_normal" type="submit">SIGN UP</button>
    </form>
    <!-- ホームページへのリンク -->
    <p><a href="index.html">TOPページ</a></p>
</body>

</html>