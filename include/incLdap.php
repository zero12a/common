<?php


class ldapClass{

    private $server; //ldap 서버 연결시 서버 host 또는 dns
    private $ldap; //연결성공시 ldap connection 오브젝트
    private $domain; // 로그인시 ID앞에 붙는 프리픽스 도메인
    private $base; //사용자 조회시 DC
    private $id;
    private $pw;

	//생성자
	function __construct(){
       
	}
	//파괴자
	function __destruct(){
        if($this->ldap)@ldap_close($this->ldap);
    }

    //연결
    function connect($server){
        $this->server = $server;
        $adServer = "ldap://" . $server;
        $this->ldap = ldap_connect($adServer) ;

        ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($this->ldap, LDAP_OPT_NETWORK_TIMEOUT, 1);

        return $this->ldap;
    }

    //로그인
    function login($domain,$id,$pw){
        $this->domain = $domain;
        $this->id = $id;
        $this->pw = $pw;

        $ldaprdn = $this->domain . "\\" . $id;

        ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0);

        $bind = ldap_bind($this->ldap, $ldaprdn, $pw);//true=성공, false=실패

        return $bind;
    }

    //사용자 정보 조회
    function getUserInfo($baseDomain){ //base : ex> abc.global
        $this->base = $baseDomain;

        $rtnArray = array();
                
        $dcArray = explode('.', $baseDomain);
        $baseDC = "";
        for($i=0;$i<count($dcArray);$i++){
            $baseDC .= ($baseDC=="")?"DC=" .$dcArray[$i]:", DC=" . $dcArray[$i];
        }

        $sr = ldap_search($this->ldap, $baseDC, "cn=" . $this->id); //base : ex> DC=abc, DC=global

        $info = ldap_get_entries($this->ldap, $sr);

        $rtnArray["department"] = $info[0]["department"][0];
        $rtnArray["company"] = $info[0]["company"][0];
        $rtnArray["givenname"] = $info[0]["givenname"][0];
        $rtnArray["title"] = $info[0]["title"][0];
        $rtnArray["mail"] = $info[0]["mail"][0];
        $rtnArray["departmentnumber"] = $info[0]["departmentnumber"][0];
        $rtnArray["division"] = $info[0]["division"][0];
        $rtnArray["dn"] = $info[0]["dn"];
        $rtnArray["mobile"] = $info[0]["mobile"][0];
        
        return $rtnArray;
        /*
        echo "<br>--------------------------------" . PHP_EOL;
        echo "<br> department = " . $info[0]["department"][0] . PHP_EOL;
        echo "<br> company = " . $info[0]["company"][0] . PHP_EOL;
        echo "<br> givenname = " . $info[0]["givenname"][0] . PHP_EOL;
        echo "<br> title = " . $info[0]["title"][0] . PHP_EOL;
        echo "<br> mail = " . $info[0]["mail"][0] . PHP_EOL;
        //echo "<br> distinguishedname = " . $info[0]["distinguishedname"][0] . PHP_EOL;
        echo "<br> departmentnumber = " . $info[0]["departmentnumber"][0] . PHP_EOL;
        echo "<br> division = " . $info[0]["division"][0] . PHP_EOL;
        echo "<br> dn = " . $info[0]["dn"] . PHP_EOL;
        echo "<br> mobile = " . $info[0]["mobile"][0] . PHP_EOL;
        */
    }

    //연결종료
    function close(){
        if($this->ldap)@ldap_close($this->ldap);
    }

    //에러출력
    function fetchErrors()
    {
        $lastErrorCode = ldap_errno($this->ldap);
        $lastErrorMessage = ldap_error($this->ldap);
        if ($lastErrorCode !== 0) {
            echo "Error [$lastErrorCode]: $lastErrorMessage";
            exit(1);
        }
    }
}


?>