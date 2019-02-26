<?php
use Abraham\TwitterOAuth\TwitterOAuth;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
require __DIR__ .'\..\..\..\..\..\vendor\autoload.php';

class IndexController extends ControllerBase
{

    public function indexAction()
    {

    }
    public function loginAction()
    {
        $connection = new TwitterOAuth(CK, CKS);
        $request_token = $connection->oauth("oauth/request_token", array("oauth_callback" => CBURL));
        
        //リクエストトークンはcallback.phpでも利用するのでセッションに保存する
        $_SESSION['oauth_token'] = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
         
        // Twitterの認証画面へリダイレクト
        $url = $connection->url("oauth/authorize", array("oauth_token" => $request_token['oauth_token']));
        header('Location: ' . $url);
    }
    public function callbackAction()
    {
        if(isset($_GET['oauth_token'])) {
        $oauth_token=$_GET['oauth_token'];
        $oauth_token_secret=$_GET['oauth_verifier'];

	    $connection = new TwitterOAuth(CK, CKS, $oauth_token, $oauth_token_secret);
	    $access_token = $connection->oauth('oauth/access_token', array('oauth_verifier' => $_GET['oauth_verifier'], 'oauth_token'=> $_GET['oauth_token']));
        $this->persistent->access_token = $access_token;
        //取得したアクセストークンでユーザ情報を取得
        $user_connection = new TwitterOAuth(CK, CKS, $access_token['oauth_token'], $access_token['oauth_token_secret']);
        $this->view->connection = $user_connection;
        $user_info = $user_connection->get('account/verify_credentials');

        $twitter = twitter::findFirstByid($user_info->$id);

        $twitter = new twitter();
        $twitter->access_token = $access_token;
        $twitter->id = $user_info->id;
        $twitter->name = $user_info->name;
        $twitter->screen_name = $user_info->screen_name;

        $url = $user_info->profile_image_url_https;
        $data = file_get_contents($url);
        $dir_path = BASE_PATH.'\public\img\/';
        $img_name = date("YmdHis").".jpg";
        $img_file = $dir_path.$img_name;
        
        file_put_contents($img_file,$data);
        $twitter->profile_image = $img_name;
        

        $twitter->zikosyokai = $user_info->description;

        $this->persistent->id = $twitter->id;
        $this->persistent->name = $twitter->name;
        $this->persistent->profile_image = $twitter->profile_image;
        $this->persistent->zikosyokai = $twitter->zikosyokai;
        $this->persistent->connection = $user_connection;

        //var_dump($user_connection->get("account/verify_credentials"));
        if ($twitter->save() === false) {
            echo "できなぁい：\n";
        
            $messages = $twitter->getMessages();
        
            foreach ($messages as $message) {
                echo $message, "\n";
            }
        } else {
            echo 'セーブに成功した';
        }
        }else{
        header("Location: ".'./index.phtml');
        exit();
        }
    }
    public function apliAction()
    {
        $this->view->id = $this->persistent->id;
        $this->view->name = $this->persistent->name;
        $this->view->image = $this->persistent->profile_image;

        $this->view->kezi = Keziban::find();

        //データベース追加
        if($this->request->isPost()){
            $keziban = new keziban();
            $keziban->id = $this->persistent->id;
            $keziban->name = $this->persistent->name;
            $keziban->image = $this->persistent->profile_image;
            $keziban->comment = $this->request->getPost("comment");

            if ($keziban->save() === false) {
                echo "できなぁい：\n";
            
                $messages = $keziban->getMessages();
            
                foreach ($messages as $message) {
                    echo $message, "\n";
                }
            } else {
                echo 'セーブに成功した';
            }

            $this->view->test = $this->request->getPost("comment");
            header("Location: " . './apli');
        }
    }
    public function profileAction()
    {
        $this->view->id = $this->persistent->id;
        $this->view->name = $this->persistent->name;
        $this->view->image = $this->persistent->profile_image;
        $this->view->zikosyokai = $this->persistent->zikosyokai;
        $this->view->connection = $this->persistent->connection;

        //プロフィール変更
        if ($this->request->hasFiles()){
            $dir_path = BASE_PATH.'\public\img\/';
            //アップロードファイルがあるかどうかをチェックします
                foreach ($this->request->getUploadedFiles() as $file) {
                    $file->moveTo($dir_path. DIRECTORY_SEPARATOR . $file->getName());
                    $img_file = $dir_path."\/".$file->getName();

                    $this->persistent->profile_image = $file->getName();
                    $this->view->image = $file->getName();

                    header("Location: " . './apli');
                }
        }
    }
    public function logoutAction()
    {
        header("Content-type: text/html; charset=utf-8");
 
        //セッション変数を全て解除
        $_SESSION = array();
         
        //セッションクッキーの削除
        if (isset($_COOKIE["PHPSESSID"])) {
            setcookie("PHPSESSID", '', time() - 1800, '/');
        }
    }
}

