<?php
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;


//로그인 검사
if(!isLogin()){
        
    //20 토큰이 있으면 인증서버에서 정보 받아오기
    //외부 파라미터 받기
    $REQ["access_token"] = reqGetString("access_token",100);
    $REQ["refresh_token"] = reqGetString("refresh_token",100);

    //요청값이 없으면 세션에서 정보 얻기.
    if($REQ["access_token"] =="")$REQ["access_token"] = getAccessToken();
    if($REQ["refresh_token"] =="")$REQ["refresh_token"] = getRefreshToken();

    alog("(incLoginCheckOauth) REQ.access_token = ". $REQ["access_token"]);
    alog("(incLoginCheckOauth) REQ.refresh_token = ". $REQ["refresh_token"]);

    if($REQ["access_token"] == ""){
        JsonMsg("500","100","로그인 후 이용해 주세요.(check oauth)");
    }else if($CFG["CFG_OAUTH_HOST"] =="" || $CFG["CFG_OAUTH_PORT"] == ""){
        JsonMsg("500","110","인증서버 정보가 없습니다.(CFG_OAUTH_HOST,CFG_OAUTH_PORT)");
    }else{
        //30 인증서버에서 인증정보 받기

        // Create a client with a base URI
        $client = new GuzzleHttp\Client();
        // Send a request to https://foo.com/api/test
        //     'body' => 'grant_type=password&client_id=demoapp&client_secret=demopass&username=demouser&password=testpass'

        $resJsonStr = "";
        try{
            $fullUrl = "http://" . $CFG["CFG_OAUTH_HOST"] . ":" .  $CFG["CFG_OAUTH_PORT"] . "/o.s/os2ctl.php";

            $res = $client->request('GET', $fullUrl, [
                'timeout' => 1,
                'connect_timeout' => 1,
                'read_timeout' => 2,
                'query' => [
                    'CTL' => "getResource"
                    ,'access_token' => $REQ["access_token"]
                ]
            ]);
            
            alog("(incLoginCheckOauth) res code : " . $res->getStatusCode());
            //alog("res header : " . $res->getHeader('content-type')[0]);
            alog("(incLoginCheckOauth) res body : " . $res->getBody());

            //상태 코드 확인하기
            if(trim($res->getStatusCode()) != "200"){
                JsonMsg("500","120","인증 정보를 요청 결과 오류가 발생했습니다.(rescode : " . $res->getStatusCode() . ")");                
            }

            $resJsonStr = $res->getBody();
            $resArr = json_decode($resJsonStr,true);//true : stdclass가 아닌 그냥 배열로
            //var_dump($resArr);

        }catch(ClientException $e) {
            alog("(incLoginCheckOauth) ClientException : " . $e->getMessage());
            //echo $e->getMessage() . "\n";
            //echo $e->getRequest()->getMethod();
        }catch(GuzzleException $e) {
            alog("(incLoginCheckOauth) GuzzleException : " . $e->getMessage());
            //echo $e->getMessage() . "\n";
            //echo $e->getRequest()->getMethod();
        }catch(Exception $e) {
            alog("(incLoginCheckOauth) Exception : " . $e->getMessage());
            //echo $e->getMessage() . "\n";
            //echo $e->getRequest()->getMethod();
        }

        //사용자 정보 세팅
        $REQ["USR_SEQ"] = $resArr["RTN_DATA"]["USER_INFO"]["USR_SEQ"];
        $REQ["USR_ID"] = $resArr["RTN_DATA"]["USER_INFO"]["USR_ID"];
        $REQ["USR_NM"] = $resArr["RTN_DATA"]["USER_INFO"]["USR_NM"];

        if(!is_numeric($REQ["USR_SEQ"])){
            JsonMsg("500","130","인증서버로 부터 인증을 실패했습니다.");
        }else{
            //정상 리턴받은 경우 인증세션 삽입

            //세션부여
            //var_dump($REQ);
            setUserSeq($REQ["USR_SEQ"]);
            setUserId($REQ["USR_ID"]);
            setUserNm($REQ["USR_NM"]);     

            //oauth
            setAccessToken($REQ["access_token"]);
            setRefreshToken($REQ["refresh_token"]);

            alog("success login check oauth.");
        }

    }

}
?>