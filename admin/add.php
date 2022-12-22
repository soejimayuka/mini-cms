<?php
  // セッションの開始
  session_start();

  // ファイルの読み込み
  require_once('../inc/config.php');
  require_once('../inc/functions.php');

  // 投稿ボタンが押されたかをチェック
  if ( $_SERVER['REQUEST_METHOD'] !== 'POST') {
    // ダイレクトでアクセスされた時
    header('Location: post.php');
    exit();
  }

  // CSRF対策 ・・・ トークンのチェック
  check_token();

  //添付ファイルの配列を出力
  // print_r($_FILES["post_image"]);

  // ファイル名の初期値
  $file_name　= '';

  // 添付ファイルが存在し、かつエラーが無いかをチェックする
  if ( isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK ) {
    // 添付ファイルがあり、エラーもない時

    // ファイルタイプを取得
    $file_type = exif_imagetype($_FILES['post_image']['tmp_name']);

    // 画像ファイルかをチェック
    if ($file_type != IMAGETYPE_GIF && $file_type != IMAGETYPE_JPEG && $file_type != IMAGETYPE_PNG) {
      // 許可していないファイル形式ならエラー
      echo '画像は「gif」、「jpeg」、「png」形式で指定して下さい。';
      exit();
    }

    // 画像のアップロード先
    $file_dir = '../upload/';

    // ファイル名を生成 （ファイル名の前に現在の日時を付加）
    $file_name = date('YmdHis') . '-' . $_FILES['post_image']['name'];

    // ファイルを一時フォルダから移動  関数：move_uploaded_file(一時ファイル, 移動先)
    move_uploaded_file($_FILES['post_image']['tmp_name'], $file_dir. $file_name);
  }


  try {
    // データベースへ接続
    $dbh = new PDO(DSN, DB_USER, DB_PASSWORD);

    // エラー発生時に「PDOException」という例外を投げる設定に変更
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL文の作成
    $sql = 'INSERT INTO posts (title, category_id, content, post_image, created) VALUES(?, ?, ?, ?, now())';

    // ステートメント用意
    $stmt = $dbh->prepare($sql);

    // プレースホルダーに値をガッチャンコ
    $stmt->bindValue(1, $_POST['title'], PDO::PARAM_STR);
    $stmt->bindValue(2, (int)$_POST['category_id'], PDO::PARAM_INT);
    $stmt->bindValue(3, $_POST['content'], PDO::PARAM_STR);
    $stmt->bindValue(4, $file_name, PDO::PARAM_STR);

    // ステートメントを実行
    $stmt->execute();


    // データベースとの接続を終了
    $dbh = null;

  } catch (PDOException $e) {
    //　例外発生時の処理
    echo 'エラー' . h($e->getMessage());
    exit();
  }
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>登録完了</title>
</head>
<body>
  <h1>登録完了</h1>
  <p>新着情報を登録しました。</p>
</body>
</html>
