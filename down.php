<?php

// 看看豆瓣的书籍价值
// 价值 = 打分人数 * 分数

define('BOOKS_FILE', 'books.txt'); // <id> "\t" <name> "\t" <remark> "\t" <people> "\t" <is_novel>

$books = get_books();
foreach (file('tags.txt') as $tag_line) {
    $url = "https://book.douban.com$tag_line";
    echo $url,PHP_EOL;
    $code = download_page($url);
    if (preg_match_all('#https://book.douban.com/subject/(\d+)/#', $code, $m)) {
        foreach ($m[0] as $i => $book_url) {
            do_book(download_page($url), $m[1][$i]);
        }
    }
}
$values = [];
foreach ($books as $id => $a) {
    $values[$a[0]] = $a[1] * $a[2];
}
asort($values);
print_r($values);

function do_book($code, $id)
{
    global $books;
    if (isset($books[$id])) return;
    $books[$id] = $a = parse_book($code);
    echo "$id\t", implode(" ", $a),PHP_EOL;
}
function get_books()
{
    $a = file(BOOKS_FILE);
    $ret = [];
    foreach ($a as $line) {
        $aa = explode("\t", trim($line));
        $id = array_shift($aa);
        $ret[$id] = $aa;
    }
    return $ret;
}
function set_books($books)
{
    $f = fopen(BOOKS_FILE, "w");
    foreach ($books as $id => $b) {
        fwrite($f, "$id\t$b[0]\t$b[1]\t$b[2]\t$b[3]\n", 4096);
    }
    fclose($f);
}
function parse_book($code)
{
    $ret = [];
    // 名称
    if (preg_match('#<title>(.+) \(豆瓣\)</title>#u', $code, $m)) {
        $ret[] = $m[1];
    }
    // 评分
    if (preg_match('#<strong class="ll rating_num " property="v:average"> ([\d.]+) </strong>#', $code, $m)) {
        $ret[] = floatval($m[1]);
    }
    // 多少人评价
    if (preg_match('#(\d+)</span>人评价#u', $code, $m)) {
        $ret[] = intval($m[1]);
    }
    // 是否小说
    $ret[] = intval(preg_match('#href="/tag/小说"#u', $code, $m));
    return $ret;
}
function download_book($id)
{
    $url = "https://book.douban.com/subject/$id/?icn=index-topchart-subject";
    return download_page($url);
}
function download_page($url)
{
    // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

    $headers = array();
    $headers[] = 'Connection: keep-alive';
    $headers[] = 'Cache-Control: max-age=0';
    $headers[] = 'Upgrade-Insecure-Requests: 1';
    $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36';
    $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8';
    $headers[] = 'Referer: https://book.douban.com/';
    $headers[] = 'Accept-Encoding: gzip, deflate, br';
    $headers[] = 'Accept-Language: zh-CN,zh;q=0.9,zh-TW;q=0.8';
    $headers[] = 'Cookie: ll=\"108296\"; bid=3plYmfbXzGA; __utma=30149280.1936290155.1549798218.1549798218.1549798218.1; __utmc=30149280; __utmz=30149280.1549798218.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); ap_v=0,6.0; __utma=81379588.867767646.1549798222.1549798222.1549798222.1; __utmc=81379588; __utmz=81379588.1549798222.1.1.utmcsr=douban.com|utmccn=(referral)|utmcmd=referral|utmcct=/; _pk_ref.100001.3ac3=%5B%22%22%2C%22%22%2C1549798222%2C%22https%3A%2F%2Fwww.douban.com%2F%22%5D; _pk_ses.100001.3ac3=*; gr_user_id=42269aba-4f48-479f-98fb-62d724dc7641; gr_session_id_22c937bbd8ebd703f2d8e9445f7dfd03=d0e18f04-61c7-4b5a-8c22-7f1ebc577dfb; gr_cs1_d0e18f04-61c7-4b5a-8c22-7f1ebc577dfb=user_id%3A0; gr_session_id_22c937bbd8ebd703f2d8e9445f7dfd03_d0e18f04-61c7-4b5a-8c22-7f1ebc577dfb=true; __yadk_uid=IZv8BAMls8621KZ1vy3SqQdfbBuSXrck; _vwo_uuid_v2=DDD39897C3D741661F0AB3DBCA5CCAA04|ccf970930b91633129f179b53756ce25; Hm_lvt_6e5dcf7c287704f738c7febc2283cf0c=1549798229; Hm_lpvt_6e5dcf7c287704f738c7febc2283cf0c=1549798229; viewed=\"30325325_30431098\"; _pk_id.100001.3ac3=f279f025f526f11c.1549798222.1.1549798304.1549798222.; __utmb=30149280.7.10.1549798218; __utmb=81379588.6.10.1549798222; ct=y';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
        return false;
    }
    curl_close($ch);

    return $result;
}
