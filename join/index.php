<?php
//dbconnect.phpを読み込む
  require('../dbconnect.php');

//セッションを使うページに必ず入れる
  session_start();

//フォームからデータが送信された場合
  if (!empty($_POST)){
    //半角スペースと全角スペースの除去
    $nick_name =trim(mb_convert_kana($_POST['nick_name'],"s",'UTF-8'));
    $email =trim(mb_convert_kana($_POST['email'],"s",'UTF-8'));
    $password =trim(mb_convert_kana($_POST['password'],"s",'UTF-8'));
    //エラー項目の確認
    //ニックネームが未入力
    if($nick_name == ''){
      $error['nick_name']= 'blank';
  }
  //メールアドレスが未入力
  if ($email==''){
    $error['email']='blank';
  }
  //パスが未入力
if($password==''){
    $error['password']='blank';
  }elseif(strlen($password)<4){
    //パスが４文字より少ない
    $error['password']='length';
  }
  //画像ファイルの拡張子チェック
  $fileName= $_FILES['picture_path']['name'];
  if (!empty($fileName)) {
    $ext= substr($fileName, -3);
    $ext= strtolower($ext);
    if ($ext != 'jpg'&& $ext !='gif'&& $ext !='png'){
      $error['picture_path']= 'type';
    }
  }

//メールアドレス重複チェック
  if(empty($error)){
    $sql=sprintf('SELECT COUNT(*) AS cnt FROM `members` WHERE`email`="%s"',
      mysqli_real_escape_string($db, $email)
      );
    //SQL実行
    $record= mysqli_query($db, $sql)or die(mysqli_error($db));
    //連想配列としてSQL実行結果を受け取る
    $table= mysqli_fetch_assoc($record);
    if($table['cnt']>0){
      //同じエラーが1件以上あったらエラー
      $error['email']= 'duplicate';
    }
  }


//エラーがない場合
  if(empty($error)){
    //画像をアップロードする
    $picture_path= date('YmdHis') . $_FILES['picture_path']['name'];
    move_uploaded_file($_FILES['picture_path']['tmp_name'], '../member_picture/' . $picture_path);
    //セッションに値を保存
    $_SESSION['join']=$_POST;
    $_SESSION['join']['picture_path']= $picture_path;
//check.phpへ移動
    header('Location: check.php');
    exit();
  }
}

//書き直し  $_REQUESTは$_GETでも大丈夫、わかりやすくしてるだけ
if (isset($_REQUEST['action'])&& $_REQUEST['action']== 'rewrite'){
  $_POST= $_SESSION['join'];
  $error['rewrite']= true;
}


?>



<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SeedSNS</title>

    <!-- Bootstrap -->
    <link href="../assets/css/bootstrap.css" rel="stylesheet">
    <link href="../assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="../assets/css/form.css" rel="stylesheet">
    <link href="../assets/css/timeline.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <!--
      designフォルダ内では2つパスの位置を戻ってからcssにアクセスしていることに注意！
     -->


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
  <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
          <!-- Brand and toggle get grouped for better mobile display -->
          <div class="navbar-header page-scroll">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                  <span class="sr-only">Toggle navigation</span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="index.php"><span class="strong-title"><i class="fa fa-twitter-square"></i> Seed SNS</span></a>
          </div>
          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-6 col-md-offset-3 content-margin-top">
        <legend>会員登録</legend>
        <form method="post" action="" class="form-horizontal" role="form" enctype="multipart/form-data">
          <!-- ニックネーム -->
          <div class="form-group">
            <label class="col-sm-4 control-label">ニックネーム</label>
            <div class="col-sm-8">
            <?php if(isset($_POST['nick_name'])): ?>
              <input type="text" name="nick_name" class="form-control" placeholder="例： Seed kun" value="<?php echo htmlspecialchars($_POST['nick_name'], ENT_QUOTES, 'UTF-8');?>">
            <?php else: ?>
              <input type="text" name="nick_name" class="form-control" placeholder="例： Seed kun">
            <?php endif; ?>
            <?php if (isset($error['nick_name']) && $error['nick_name']=='blank'):?>
                <p class="error">*ニックネームを入力してください。</p>
            <?php endif; ?>
            </div>
          </div>
          <!-- メールアドレス -->
          <div class="form-group">
            <label class="col-sm-4 control-label">メールアドレス</label>
            <div class="col-sm-8">
            <?php if(isset($_POST['email'])):?>
              <input type="email" name="email" class="form-control" placeholder="例： seed@nex.com" value="<?php echo htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');?>">
            <?php else:?>
              <input type="email" name="email" class="form-control" placeholder="例： seed@nex.com">
            <?php endif;?>
            <?php if (isset($error['email']) && $error['email']=='blank'):?>
                <p class="error">*メールアドレスを入力してください。</p>
            <?php endif;?>
            <?php if (isset($error['email']) && $error['email']=='duplicate'):?>
                <p class="error">*指定されたメールアドレスは既に登録されています。</p>
            <?php endif;?>
            </div>
          </div>
          <!-- パスワード -->
          <div class="form-group">
            <label class="col-sm-4 control-label">パスワード</label>
            <div class="col-sm-8">
              <?php if (isset($_POST['password'])): ?>
                <input type="password" name="password" class="form-control" placeholder="" value="<?php echo htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');?>">
            <?php else :?>
              <input type="password" name="password" class="form-control" placeholder="">
            <?php endif ;?>
              
              <?php if (isset($error['password'])&&$error['password']=='blank'): ?>
                <p class="error">*パスワードを入力してください。</p>
              <?php endif;?>
              <?php if (isset($error['password'])&&$error['password']=='length'): ?>
                <p class="error">*パスワードは４文字以上で入力してください。</p>
              <?php endif;?>
            </div>
          </div>
          <!-- プロフィール写真 -->
          <div class="form-group">
            <label class="col-sm-4 control-label">プロフィール写真</label>
            <div class="col-sm-8">
            <input type="file" name="picture_path" class="form-control">
            <?php if (isset($error['picture_path']) && $error['picture_path']=='type'):?>
              <p class= "error">写真などは「.gif」か「.jpg」か「.png」の画像を指定してください。</p>
            <?php endif;?>
            <?php if (!empty($error)):?>
              <p class= "error">恐れ入りますが、画像を改めて指定してください。</p>
            <?php endif;?>
              
            </div>
          </div>

          <input type="submit" class="btn btn-default" value="確認画面へ">
        </form>
      </div>
    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
