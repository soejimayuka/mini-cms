<?php
  // ファイルの読み込み
  require_once('inc/config.php'); //設定ファイル
  require_once('inc/functions.php'); //独自関数ファイル

  try {
    // データベースへ接続
    $dbh = new PDO(DSN, DB_USER, DB_PASSWORD);

    // エラー発生時に「PDOException」という例外を投げる設定に変更
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL文の作成
    $sql = 'SELECT * FROM posts ORDER BY created DESC';

    // SQLを実行
    $stmt = $dbh->query($sql);

    // 実行結果を連想配列として取得
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // echo '<pre>';
    // print_r($result);
    // echo '<pre>';

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
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>新着記事一覧</title>
</head>
<body>
  <h1>新着情報</h1>
  <dl>
    <?php foreach($result as $row) : ?>
      <dt><time datetime="<?php echo h($row['created']); ?>"><?php echo h(date('Y年m月d日', strtotime($row['created']))); ?></time></dt>
      <dd>
        <a href="detail.php?id=<?php echo h($row['id']); ?>">
          <?php echo h($row['title']); ?>
        </a>
      </dd>
    <?php endforeach; ?>
  </dl>
</body>
</html>
