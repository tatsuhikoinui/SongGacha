<?php
session_start();

// セッション情報を削除してログアウト
$_SESSION = array(); // セッションの配列を空にする
session_destroy(); // セッションを破棄

// ログアウト後にトップページにリダイレクト
header("Location: index.html");
exit;