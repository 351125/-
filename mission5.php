<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>DB掲示板</title>
</head>
<body>
    <?php 
        // DB接続設定
    	$dsn = 'データベース名';
    	$user = 'ユーザー名';
    	$password = 'パスワード';
    	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    	

    	//テーブルを作成
        //登録項目（カラム）は6つ
        //作成時刻、更新時刻は自動で記録。
    	$sql = "CREATE TABLE IF NOT EXISTS 501a
    	(id INT AUTO_INCREMENT PRIMARY KEY,
    	name char(32),
    	comment TEXT,
    	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        password char(32)
    	);";
    	$stmt = $pdo->query($sql);
    
        
    //編集機能第一段階
    if(!empty($_POST['edit'])){//編集番号を受信
        if(!empty($_POST['epass'])){//パスワードを受信
            $sql='SELECT * FROM 501a where id=:id limit 1';
                $edit=$_POST['edit'];//編集希望番号
                $id = $edit;
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $item = $stmt-> fetchall(PDO :: FETCH_ASSOC);
                foreach($item as $line){
                    $epass=$_POST['epass'];//編集フォームから受信したパスワード
                    if($line['password'] == $epass){//パスワードがデータと一致した時
                        $num=$edit;//隠されたフォームに、編集する投稿番号を入力する。→62行目
                        $message="投稿番号".$edit."の編集を受け付けます。投稿フォームより内容を
                        入力してください。再度パスワードを入力していただく必要はございません".'<br>';
                    }else{$message="パスワードが違います。<br>";}//パスワードが違う時
                }
        }else{$message="パスワードを入力してください。<br>";} //パスワード未入力時
    }

    ?>

    <h1>
        掲示板（編集機能）
    </h1>
    <form action="" method=POST>
        name:<input type="text" name="name"><br>
        comment:<input type="text" name="comment"><br>
        password:<input type="text" name="pass"><br>
        <input type="hidden" name="enumber" 
        value="<?php if(isset($num)){echo $num;}?>">
        <input type="submit" value="送信"><br><br>

        delete:<input type="number" name="delete" placeholder="削除希望の投稿番号を入力"><br>
        password:<input type="text" name="dpass">
        <input type="submit" value="削除"><br><br>

        edit:<input type="number" name="edit" placeholder="編集希望の投稿番号を入力"><br>
        password:<input type="text" name="epass">
        <input type="submit" value="編集"><br> 
    </form>

    
	<?php		
        //書き込み（新規投稿モード）
            if(!empty($_POST['name']) && !empty($_POST['comment'])){//名前とコメントを受信
                if(empty($_POST['enumber'])){//編集番号を投稿フォームから受信しなかったとき。
                    if(!empty($_POST['pass'])){//パスワードを受信
                        $sql = $pdo -> prepare('INSERT INTO 501a (name,comment,password)
                        VALUES (:name,:comment,:password)');
                        $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                        //１個目で :name のようにさっき与えたパラメータを指定。
                        //２個目に、それに入れる変数を指定。bindParam には直接数値を入れられない。変数のみ。
                        //３個目で型を指定。PDO::PARAM_STR は「文字列だよ」って事。
                        $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                        $sql -> bindParam(':password',$password,PDO::PARAM_STR);
                        $name = $_POST['name'];
                        $comment = $_POST['comment']; 
                        $password = $_POST['pass'];
                        $sql -> execute();
                        //execute は、命令などを「実行する」「遂行する」って意味。
                        //query はそのまま実行しちゃう。prepare は後で execute が必要。
                        //prepare文内パラメータは(:name, :value) のように「'」はいらない。
                        //bindParamなどでパラメータを指定する時は(':name', $name) のように「':name'」とする。
                        //bindParamの引数名（:name など）はテーブルのカラム名に併せるとミスが少なくなる。
                        echo "投稿を追加しました。".'<br>';
                        echo "<hr>";
                    }else{echo "パスワードを入力してください。<br>";echo "<hr>";}    
        //書き込み（編集機能）
                }else{//編集番号を投稿フォームから受信した時。
                        $enumber=$_POST['enumber'];//隠されたフォームから受信した編集番号
                        $id = $enumber; //編集する投稿番号
                        $name = $_POST['name'];//新たに受信した名前
                        $comment = $_POST['comment']; //新たに受信した内容
                        $sql = 'UPDATE 501a SET name=:name,comment=:comment WHERE id=:id limit 1';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                        $stmt->execute();
                }
            }
    	
        
        //削除機能
        if(!empty($_POST['delete'])){//削除希望番号を受信
            if(!empty($_POST['dpass'])){//パスワードを受信
                $delete = $_POST['delete'];//削除希望番号
                $dpass=$_POST['dpass'];//削除フォームから受信したパスワード
                $sql='SELECT * FROM 501a where id=:id limit 1';
                $id = $delete;//削除する投稿番号
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $item = $stmt-> fetchall(PDO :: FETCH_ASSOC);
                //PDO::FETCH_ASSOC: は、結果セットに 返された際のカラム名で添字を付けた配列を返す
                foreach($item as $line){
                    if($line['password'] == $dpass){
                    //データベース上のパスワードと、受信したパスワードが一致
                        $sql = 'DELETE from 501a where id=:id limit 1';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                        $stmt->execute();
                        echo "投稿番号".$delete."を削除しました。".'<br>';
                    }else{echo "パスワードが違います。<br>";}
                }
            }else{echo "パスワードを入力してください。<br>";}
        }
        

        //掲示板を表示
            echo "<br>";
            if(isset($message)){echo $message;}else{echo "";}//編集機能第一段階後の文章（45.47.49行目記載）
            $sql = 'SELECT * FROM 501a';
            $stmt = $pdo->query($sql);//変更を加えず表示するだけだからqueryで足りる。
            $results = $stmt->fetchAll();
            //PDOStatement->fetchAll() — 全ての結果行を含む配列を返す、値を取り出す（fetchメソッド）
            foreach($results as $row){
                //$rowの中にはテーブルのカラム名が入る
                echo $row['id'].',';
                echo $row['name'].',';
                echo $row['comment'].',';
                echo $row['created_at'].',';
                //更新日時が投稿日時と違う時（一度以上編集済みの時。）。
                if(isset($row['updated_at']) && $row['created_at']!=$row['updated_at'])
                //更新日時を表示
                {echo $row['updated_at'].'<br>';}
                else{echo "<br>";}
                echo "<hr>";
            };
    	
    ?>

</body>
</html>
