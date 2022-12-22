<?php
  // セッションの開始
  session_start();


  // ファイルの読み込み
  require_once('../inc/config.php');
  require_once('../inc/functions.php');

  // GETパラメータのチェック
  if ( !isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    // $_GET['id'] が 存在していない、または空、または数値ではないときの場合
    header('Location: index.php');
    exit();
  }

  // CSRF対策 ・・・ トークンの生成
  set_token();

  try {
    // データベースへ接続
    $dbh = new PDO(DSN, DB_USER, DB_PASSWORD);

    // エラー発生時に「PDOException」という例外を投げる設定に変更　エラーが起きたと言うボールを投げる！
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL文の作成　記事IDと一致するレコードの抽出　SQLインジェクションの脆弱性対策
    $sql = 'SELECT * FROM posts WHERE id = ?';

    // ステートメント用意　?は後から変数を埋め込むマークだと知らせる
    $stmt = $dbh->prepare($sql);

    // プレースホルダーに値をガッチャンコ
    $stmt->bindValue(1, (int)$_GET['id'] , PDO::PARAM_INT);

    // ステートメントを実行　ガッチャンコしたときはexecuteで実行する　queryメソッドはできない
    $stmt->execute();

    // 実行結果を連想配列として取得　fetchとfetchAllがある 1件なのでfetchでOK
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    // echo '<pre>';
    // print_r($result);
    // echo '<pre>';

    // SQL文の作成 カテゴリの全レコードを抽出
    $sql = 'SELECT * FROM categories';

    // クエリの実行
    $stmt = $dbh->query($sql);

    // 実行結果を全件連想配列として取得 1件じゃないのでfetchAllで取得する
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // echo '<pre>';
    // print_r($categories);
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
  <title>記事の編集</title>
</head>
<body>
  <h1>記事の編集</h1>
  <form action="update.php" method="post" enctype="multipart/form-data">
    <dl>
      <dt><label for="title">記事のタイトル</label></dt>
      <dd>
        <input type="text" id="title" name="title" value="<?php echo h($result["title"]); ?>">
      </dd>
      <dt><label for="category_id">カテゴリー</label></dt>
      <dd>
      <select name="category_id" id="category_id">
        <?php foreach($categories as $category) : ?>
        <?php
          $selected = '';
          // ループで出力するカテゴリと、データベースから取得したカテゴリが一致するかチェック
          if ($category['id'] == $result['category_id']) {
            // 一致した場合
            $selected = ' selected';
          }
        ?>
        <option value="<?php echo h($category['id']); ?>"<?php echo h($selected); ?>><?php echo h($category['category_name']); ?></option>
        <?php endforeach; ?>
      </select>
      </dd>
      <dt><label for="content">記事の内容</label></dt>
      <dd>
        <textarea name="content" id="content" cols="30" rows="10"><?php echo h($result["content"]); ?></textarea>
      </dd>
      <dt><label for="post_image">画像</label></dt>
      <dd>
      <?php if( !empty($result['post_image']) ) : ?>
        <img src="../upload/<?php echo h($result['post_image']); ?>" width="240" alt="<?php echo h($result['title']); ?>"><br>
      <?php endif; ?>
        <input type="file" id="post_image" name="post_image">
      </dd>
    </dl>
    <p><input type="hidden" name="id" value="<?php echo h($result['id']); ?>"></p>
    <p><input type="hidden" name="post_image" value="<?php echo h($result['post_image']); ?>"></p>
    <p><input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>"></p>
    <p><input type="submit" value="変更"></p>
  </form>
</body>
</html>
