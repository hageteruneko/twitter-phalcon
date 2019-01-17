<?php
use Abraham\TwitterOAuth\TwitterOAuth;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
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
        $oauth_token=$_GET['oauth_token'];
        $oauth_token_secret=$_GET['oauth_verifier'];

	    $connection = new TwitterOAuth(CK, CKS, $oauth_token, $oauth_token_secret);
	    $access_token = $connection->oauth('oauth/access_token', array('oauth_verifier' => $_GET['oauth_verifier'], 'oauth_token'=> $_GET['oauth_token']));
        
        //取得したアクセストークンでユーザ情報を取得
        $user_connection = new TwitterOAuth(CK, CKS, $access_token['oauth_token'], $access_token['oauth_token_secret']);
        $this->view->connection = $user_connection;
        $user_info = $user_connection->get('account/verify_credentials');

        $twitter = new twitter();
        $twitter->access_token = $access_token;
        $twitter->id = $user_info->id;
        $twitter->name = $user_info->name;
        $twitter->screen_name = $user_info->screen_name;
        $twitter->profile_image = $user_info->profile_image_url_https;
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
    }
    public function apliAction()
    {
        $this->view->id = $this->persistent->id;
        $this->view->name = $this->persistent->name;
        $this->view->image = $this->persistent->profile_image;

        $this->view->kezi = Keziban::find();

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

        if ($this->request->hasFiles()){
            //アップロードファイルがあるかどうかをチェックします。
            $dir_path = BASE_PATH.'\public\img\/';
                foreach ($this->request->getUploadedFiles() as $file) {
                    $file->moveTo($dir_path. DIRECTORY_SEPARATOR . $file->getName());
                    $img_file = $dir_path."\/".$file->getName();
                    $this->view->file = $img_file;
                    //header("Location: " . './apli');
                }
            header("Location: " . './apli');
        }
        if($this->request->isPost()){
            header("Location: " . './apli');
        }
    }

}

