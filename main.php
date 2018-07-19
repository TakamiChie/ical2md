<?php
require_once 'vendor/autoload.php';
use Sabre\VObject;
const MATCH_HASHTAG = '/#([A-Za-z][A-Za-z0-9]+)/';
const MATCH_META = '/^(\w+):\s*(.*)$/m';
function puts($str) {
	echo $str . PHP_EOL;
}
$tz = new DateTimeZone("Asia/Tokyo");
$now = new DateTime();

$ini = parse_ini_file("setting.ini");
$keywords = explode(" ", $ini["scantags"]);
puts("> loading ics");
$ctx = stream_context_create([
  'ssl' => [
      'crypto_method' => STREAM_CRYPTO_METHOD_TLS_CLIENT,
  ],
]);
$file = file_get_contents($ini["ics_url"], false, $ctx);
$vcalendar = VObject\Reader::read($file);
puts("complete");

puts("> loading twig");
$loader = new Twig_Loader_Filesystem("./template/");
$twig = new Twig_Environment($loader);
puts("complete");

// outputフォルダ掃除
$dir = glob('./output/*.md');
 
foreach ($dir as $file){
  unlink($file);
}

puts("output start.");
puts("");
// スキャン開始
foreach ($vcalendar->VEVENT as $event) {
  $dts = $event->DTSTART->getDateTime()->setTimeZone($tz);
  if($dts > $now && preg_match_all(MATCH_HASHTAG, $event->SUMMARY->getValue(), $m) && 
    count(array_intersect($m[1], $keywords)) > 0){ 
    $meta = [];
    $description = $event->DESCRIPTION->getValue();
    // DESCRIPTION中のメタデータを収集
    if(preg_match_all(MATCH_META, $description, $metas)){
      for ($i=0; $i < count($metas[0]) ; $i++) { 
        $meta[strtolower($metas[1][$i])] = $metas[2][$i];
        $description = str_replace($metas[0][$i], "", $description);
      }
    }
    // データ格納
    $data = [
      "dtstart" => $dts,
      "dtend" => $event->DTEND->getDateTime()->setTimeZone($tz),
      "dtstamp" => $event->DTSTAMP->getDateTime()->setTimeZone($tz),
      "uid" => $event->UID->getValue(),
      "created" => $event->CREATED->getDateTime()->setTimeZone($tz),
      "description" => trim($description),
      "lastmodified" => $event->{"LAST-MODIFIED"}->getDateTime()->setTimeZone($tz),
      "location" => $event->LOCATION->getValue(),
      "title" => trim(preg_replace(MATCH_HASHTAG, "", $event->SUMMARY->getValue())),
      "tags" => $m[1],
    ];
    if($event->CLASS != null){
      $data["class"] = $event->CLASS->getValue();
    }
    // 出力
    $fn = "output/" . $dts->format("YmdH") . implode("", $m[1]) . ".md";
    puts("> " . $fn);
    puts($event->SUMMARY->getValue());
    file_put_contents($fn, $twig->render('template.twig', array_merge($data, $meta)));
    puts("OK.");
  }
}