<?php
use Abraham\TwitterOAuth\TwitterOAuth;
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
        $twitter->profile_image = $user_info->profile_image;
        $twitter->zikosyokai = $user_info->description;
        //var_dump($user_connection->get("account/verify_credentials"));
    }
    public function apliAction()
    {

    }

}

