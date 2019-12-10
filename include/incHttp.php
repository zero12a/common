<?php


//if(!include_once '../include/classCurl.php')	echo "include fail(5)";
//if(!include_once '../lib/incUtil.php')	echo "include fail(5)";

//goTest();



function goTest(){


    $F_FULL_URL = "www.ssg.com";
    $objCurl = new Curl();
    $objCurl->URL = $F_FULL_URL;

    echo '
    <html>
    <head>
        <title>My Page</title>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="cache-control" content="no-cache">
        <meta http-equiv="pragma" content="no-cache">

    </head>
    <body>
    ';

    $objCurl = getUrlInfo($objCurl);
    echo "<BR> getURLTitle : " . $objCurl->PAGE_TITLE;

    echo "<BR> getTitle : " . getTitle($F_FULL_URL);

    echo "<BR> getURLTitle2 : " . getURLTitle2($F_URLL_URL);

    echo "<BR> getUrlOld : " . getUrlOld($F_URLL_URL);



    //echo "<BR> getSiteFavicon : " . getSiteFavicon($F_URLL_URL);
}

function saveSiteFavicon($url, $folder, $filename)
{
    if(!file_exists($folder)){
        mkdir($folder,766);
    }

    /* Drop the TLD from the url */
    $fp = fopen ($folder . "/" . $filename.'.png', 'w+');
    $ch = curl_init('http://www.google.com/s2/favicons?domain='.$url);

    curl_setopt($ch, CURLOPT_TIMEOUT, 6);

    /* Save the returned data to a file */
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
}



function getURLTitle2($url){

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $content = curl_exec($ch);

    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $charset = '';

    if($contentType && preg_match('/\bcharset=(.+)\b/i', $contentType, $matches)){
        $charset = $matches[1];
    }

    curl_close($ch);

    if(strlen($content) > 0 && preg_match('/\<title\b.*\>(.*)\<\/title\>/i', $content, $matches)){
        $title = $matches[1];

        if(!$charset && preg_match_all('/\<meta\b.*\>/i', $content, $matches)){
            //order:
            //http header content-type
            //meta http-equiv content-type
            //meta charset
            foreach($matches as $match){
                $match = strtolower($match);
                if(strpos($match, 'content-type') && preg_match('/\bcharset=(.+)\b/', $match, $ms)){
                    $charset = $ms[1];
                    break;
                }
            }

            if(!$charset){
                //meta charset=utf-8
                //meta charset='utf-8'
                foreach($matches as $match){
                    $match = strtolower($match);
                    if(preg_match('/\bcharset=([\'"])?(.+)\1?/', $match, $ms)){
                        $charset = $ms[1];
                        break;
                    }
                }
            }
        }

        return $charset ? iconv($charset, 'utf-8', $title) : $title;
    }

    return $url;
}




function getTitle($url)
{
    // get html via url
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.71 Safari/537.36");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $html = curl_exec($ch);
    curl_close($ch);

    // get title
    preg_match('/(?<=<title>).+(?=<\/title>)/iU', $html, $match);
    $title = empty($match[0]) ? 'Untitled' : $match[0];
    $title = trim($title);

    // convert title to utf-8 character encoding
    if ($title != 'Untitled') {
        preg_match('/(?<=charset\=).+(?=\")/iU', $html, $match);
        if (!empty($match[0])) {
            $charset = str_replace('"', '', $match[0]);
            $charset = str_replace("'", '', $charset);
            $charset = strtolower( trim($charset) );
            if ($charset != 'utf-8') {
                $title = iconv($charset, 'utf-8', $title);
            }
        }
    }

    return $title;
}


function getUrlInfo($objCurl){
    // 해당 URL 정보 가져오기
    //$F_FULL_URL = "http://www.naver.com";
    $RtnVal = "";

    //001 세션쓰기 금지 curl lock(무한루프)방지
    session_write_close();

    $tuCurl = curl_init($objCurl->URL);
    curl_setopt($tuCurl, CURLOPT_CONNECTTIMEOUT, 15); //ssg는 10초이상 걸
    curl_setopt($tuCurl, CURLOPT_TIMEOUT, 15);
    curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, 1); // exec는 기본으로 결과를 화면에 출력함, 이거 지정시 변수로 받음
    //002 세션쓰기 금지 curl lock(무한루프)방지
    $strCookie = 'PHPSESSID=' . session_id() . '; path=/';
    curl_setopt($tuCurl, CURLOPT_COOKIE, $strCookie );
    curl_setopt($tuCurl, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.117 Safari/537.36");
    curl_setopt($tuCurl, CURLOPT_FOLLOWLOCATION, true); //리다이렉트 처
    curl_setopt($tuCurl, CURLOPT_ENCODING, ""); //'identity,gzip,deflate', ""는 허용가능 포멧모두허용

    $tuData = curl_exec($tuCurl);

    if(!curl_errno($tuCurl)){
        $info = curl_getinfo($tuCurl);
        //echo "<BR> total_time : " . $info['total_time'] ;
        //echo "<BR> url : " . $info['url'];
        //echo "<BR> http_code : " . $info['http_code'] ;

        // parse the html into a DOMDocument
        $dom = new DOMDocument();

        $dom->recover = true;
        $dom->strictErrorChecking = false;

        //echo $tuData;

        $dom->loadHTML($tuData);

        // title 이름 찾기
        $nodes_title = $dom->getElementsByTagName('title');
        //get and display what you need:
        $title = trim($nodes_title->item(0)->nodeValue);

        //돔으로 추출안될경우 정규식 파싱
        $title = "";
        //echo "<BR>돔파싱 : [" . $title . "]";
        if($title == ""){
            // get title
            //echo "<BR>정규식 파싱 :";
            preg_match('/<title>(\n*\r*.+\n*\r*)<\/title>/siU', $tuData, $match);
            //preg_match('/(?<=<title>).+(?=<\/title>)/iU', $tuData, $match);
            $title = empty($match[1]) ? '' : $match[1];
            //for($j=0;$j<count($match);$j++){
            //    echo "<BR>j = " . $match[$j] ;
            //}
            $title = trim($title);
        }
        if ($title != "") {
            preg_match('/(?<=charset\=).+(?=\")/iU', $tuData, $match);
            if (!empty($match[0])) {
                $charset = str_replace('"', '', $match[0]);
                $charset = str_replace("'", '', $charset);
                $charset = strtolower( trim($charset) );
                if ($charset != 'utf-8') {
                    //echo "<BR> 다른케릭터셋이라 변경 : " . $charset;
                    $title = iconv($charset, 'utf-8', $title);
                }
            }else{
                //echo "<BR> 케릭터셋 미정의로 임의 변경 ";
                $title = iconv("euc-kr", 'utf-8', $title);
            }
        }

        //타이틀
        $objCurl->PAGE_TITLE = $title;


        //echo "<BR> title : " . $title;

        //아이콘 가져오기
        $favicon = "";
        $items = $dom->getElementsByTagName('link');
        foreach ($items as $item)
        {
            $rel = $item->getAttribute('rel');
            if ($rel == 'icon' or $rel == 'shortcut icon')
            {
                $favicon = $item->getAttribute('href');
            }
            if($favicon != "" )break;
        }

        //단축아이콘
        $objCurl->PAGE_FAVICON = $favicon;

        //echo "<BR>Curl errno: " . curl_errno($tuCurl);
        //echo "<BR>Curl error: " . curl_error($tuCurl);

        curl_close($tuCurl);

    } else {
        //echo "<BR>Curl errno: " . curl_errno($tuCurl);
        //echo "<BR>Curl error: " . curl_error($tuCurl);

        //curl_erron 6 = Could not resolve host 도메인명
        if(curl_errno($tuCurl) == 6){
            $objCurl->PAGE_TITLE = $objCurl->getDomain();
            $objCurl->PAGE_FAVICON = "";
            curl_close($tuCurl);
            //throw new Exception("도메인 오류 : " . $objCurl->getDomain());
        }else{
            curl_close($tuCurl);
            JsonMsg("500","incHttp - getUrlInfo fail",$tuData);
        }


    }


    //echo $tuData;  responseData
    return $objCurl;
}



function getUrlOld($F_FULL_URL){
    // 해당 URL 정보 가져오기
    //$F_FULL_URL = "http://www.naver.com";

    $tuCurl = curl_init($F_FULL_URL);
    curl_setopt($tuCurl, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($tuCurl, CURLOPT_TIMEOUT, 5);
    curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, 1); // exec는 기본으로 결과를 화면에 출력함, 이거 지정시 변수로 받음

    $tuData = curl_exec($tuCurl);

    if(!curl_errno($tuCurl)){
        $info = curl_getinfo($tuCurl);
        //echo "<BR> total_time : " . $info['total_time'] ;
        //echo "<BR> url : " . $info['url'];
        //echo "<BR> http_code : " . $info['http_code'] ;



        // parse the html into a DOMDocument
        $dom = new DOMDocument();

        $dom->recover = true;
        $dom->strictErrorChecking = false;

        $dom->loadHTML($tuData);

        // title 이름 찾기
        $nodes_title = $dom->getElementsByTagName('title');
        //get and display what you need:
        $title = $nodes_title->item(0)->nodeValue;
        echo "<BR> title : " . $title;

         // icon url 찾기
        $domxml = simplexml_import_dom($dom);

        if ( $domxml->xpath('//link[@rel="shortcut icon"]') ) {
            $path = $domxml->xpath('//link[@rel="shortcut icon"]');
            $faviconURL = $path[0]['href'];
        //check for the HTML5 rel="icon"
        }else if ( $domxml->xpath('//link[@rel="icon"]') ) {
            $path = $domxml->xpath('//link[@rel="icon"]');
            $faviconURL = $path[0]['href'];
        }
        echo "<BR> faviconURL : " . $faviconURL;

         ?>
         <BR><img src="<?=$faviconURL?>"><?=$title?>
         <?php

    } else {
      echo "<BR>Curl error: " . curl_error($tuCurl);
    }

    curl_close($tuCurl);
    //echo $tuData;  responseData
}



//http://board.phpbuilder.com/showthread.php?10362271-Getting-a-sites-Favicon-with-PHP
function getFavicon($url)
{
    $href = false;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($ch);
    if (!empty($content))
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($content);
        $items = $dom->getElementsByTagName('link');
        foreach ($items as $item)
        {
            $rel = $item->getAttribute('rel');
            if ($rel == 'icon' or $rel == 'shortcut icon')
            {
                $href = $item->getAttribute('href');
                break;
            }
        }
    }
    return $href;
}


function getHttpBody($url){
    alog("getHttpBody()...................start url=" . $url);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    //curl_setopt($ch, CURLOPT_NOBODY, TRUE); // remove body
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1000); // 1초
    
    $resData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($resData, 0, $header_size);
    $body = substr($resData, $header_size);
    
    alog("getHttpBody()...................end");
    return $body;
}
?>