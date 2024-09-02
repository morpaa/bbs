<?php

const BAN_DIR = 'ban';
const RES_INTERVAL = 10;
const THREAD_INTERVAL = 15;
const DAT_DIR = 'dat';
const CNF_DIR = 'cnf';

$referer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
if (strpos($referer, $_SERVER['HTTP_HOST']) === false) {
  back('refererが変', 'refererが変です。');
}

$port = $_SERVER['REMOTE_PORT'];
if ($port < 49152) {
  back('portおかしい', 'proxyのうたがいがあります。(' . $port . ')');
}

if (!isset($_POST['NAME']) || !isset($_POST['MAIL']) || !isset($_POST['MESSAGE'])) {
  back('たりない', 'なにかが欠落しています。');
} elseif (!isset($_POST['KEY']) && !isset($_POST['SUBJECT'])) {
  back('意味不明', 'もしかして、書き込みたくない？');
}

$name = $_POST['NAME'];
if (mbstrlen($name) > 30) {
  back('名前が長い', '名前が長いです、もっと短くしてきてください。。');
}

$mail = $_POST['MAIL'];
if (mbstrlen($mail) > 50) {
  back('E-mail長い', 'E-mailが長いです。。');
}

$message = $_POST['MESSAGE'];
if (strlen(mbtrim($message)) === 0) {
  back('本文ない', '本文がありません。');
} elseif (mbstrlen($message) > 1000) {
  back('本文長すぎ', '本文が冗長というか、長いです');
} elseif (substr_count($message, "\r\n") + substr_count($message, "\r") + substr_count($message, "\n") > 30) {
  back('行数が多い', '行数が多いです、なぜ？');
}

$microtime = microtime(true);
$nowtime = (int)$microtime;

if (isset($_POST['KEY'])) {
  $key = $_POST['KEY'];
  $subject = false;
} else {
  $key = $nowtime;
  $subject = $_POST['SUBJECT'];
}

$name = strtr(h(brdel($name)), array('◆' => '◇', '★' => '☆'));
if (substr($mail, 0, 1) === '#') {
  $cappass = substr($mail, 1);
  if ($cappass === 'idonotk') {
    $name = 'かんりびと ★';
  }
  $mail = '#';
}

if (strpos($name, '#') !== false) {
  $tripkey = substr(strstr($name, '#'), 1);
  if ($tripkey !== false) {
    $trip = generate_tripcode($tripkey);
    $name = str_replace('#' . $tripkey, '', $name) . $trip;
  }
}

$mail = brdel($mail);

$message = str_replace(array("\r\n", "\r", "\n"), '<br>', h($message));
if (preg_match_all('/&gt;&gt;(([1-9]\d*)?-[1-9]\d*n?|([1-9]\d*,)+[1-9]\d*n?|[1-9]\d*n?-n?|[1-9]\d*n?)/', $message, $messages)) {
  foreach ($messages[0] as $from) {
    $to = '<a href="./read.php/' . $key . '/' . substr($from, 8) . '">' . $from . '</a>';
    $replace_pairs[$from] = $to;
  }
  $message = strtr($message, $replace_pairs);
}

if (preg_match('/&lt;special:saikoro([1-5])&gt;/', $message, $messages)) {
  $message = preg_replace('/&lt;special:saikoro([1-5])&gt;/', '', $message, 1);
  $dice = array('1&#9856;', '2&#9857;', '3&#9858;', '4&#9859;', '5&#9860;', '6&#9861;');
  $dices = '';
  $loopcount = (int)$messages[1];
  $i = 0;
  while ($i !== $loopcount) {
    $dices .= $dice[mt_rand(0, 5)] . ' ';
    ++$i;
  }
  $message .= '<hr><b>' . rtrim($dices) . '</b>';
}

if (preg_match_all('/&lt;c:([#0-9a-z]+)&gt;/', $message, $messages)) {
  $replace_pairs = array();
  $i = 0;
  foreach ($messages[0] as $from) {
    $to = ($i !== 0) ? '</font><font color="' . $messages[1][$i] . '">' : '<font color="' . $messages[1][$i] . '">';
    $replace_pairs[$from] = $to;
    ++$i;
  }
  $message = strtr($message, $replace_pairs) . '</font>';
}

if (preg_match('/&lt;special:ua&gt;/', $message)) {
  $ua = (isset($_SERVER['HTTP_USER_AGENT'])) ? h(substr($_SERVER['HTTP_USER_AGENT'], 0, 300)) : '';
  $message = preg_replace('/&lt;special:ua&gt;/i', '', $message, 1);
  $add = date('H:i:s', $nowtime) . str_pad(substr($microtime, 10), 5, '0');
  $add = (strpos($message, '<hr>') !== false) ? '<br>' . $add . ' UA:<b>' . $ua . '</b>' : '<hr>' . $add . ' UA:<b>' . $ua . '</b>';
  $message .= $add;
}

$regex = '`https?+:(?://(?:(?:[-.0-9_a-z~]|%[0-9a-f][0-9a-f]|[!$&-,:;=])*+@)?+(?:\[(?:(?:[0-9a-f]{1,4}:){6}(?:' .
  '[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25' .
  '[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5]))|::(?:[0-9a-f' .
  ']{1,4}:){5}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?:\d|[1-9]\d|1\d{' .
  '2}|2[0-4]\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])' .
  ')|(?:[0-9a-f]{1,4})?+::(?:[0-9a-f]{1,4}:){4}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:\d|[1-9]\d|1\d{2}|2[0-' .
  '4]\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?:\d' .
  '|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5]))|(?:(?:[0-9a-f]{1,4}:)?+[0-9a-f]{1,4})?+::(?:[0-9a-f]{1,4}:){3}(?:' .
  '[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25' .
  '[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5]))|(?:(?:[0-9a-' .
  'f]{1,4}:){0,2}[0-9a-f]{1,4})?+::(?:[0-9a-f]{1,4}:){2}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:\d|[1-9]\d|1\\' .
  'd{2}|2[0-4]\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5' .
  '])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5]))|(?:(?:[0-9a-f]{1,4}:){0,3}[0-9a-f]{1,4})?+::[0-9a-f]{1,4' .
  '}:(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]' .
  '\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5]))|(?:(?:[' .
  '0-9a-f]{1,4}:){0,4}[0-9a-f]{1,4})?+::(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25' .
  '[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?:\d|[1-9]\\' .
  'd|1\d{2}|2[0-4]\d|25[0-5]))|(?:(?:[0-9a-f]{1,4}:){0,5}[0-9a-f]{1,4})?+::[0-9a-f]{1,4}|(?:(?:[0-9a-f]' .
  '{1,4}:){0,6}[0-9a-f]{1,4})?+::|v[0-9a-f]++\.[!$&-.0-;=_a-z~]++)\]|(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0' .
  '-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?:\\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?:\d|[1-9]\d|' .
  '1\d{2}|2[0-4]\d|25[0-5])|(?:[-.0-9_a-z~]|%[0-9a-f][0-9a-f]|[!$&-,;=])*+)(?::\d*+)?+(?:/(?:[-.0-9_a-z' .
  '~]|%[0-9a-f][0-9a-f]|[!$&-,:;=@])*+)*+|/(?:(?:[-.0-9_a-z~]|%[0-9a-f][0-9a-f]|[!$&-,:;=@])++(?:/(?:[-' .
  '.0-9_a-z~]|%[0-9a-f][0-9a-f]|[!$&-,:;=@])*+)*+)?+|(?:[-.0-9_a-z~]|%[0-9a-f][0-9a-f]|[!$&-,:;=@])++(?' .
  ':/(?:[-.0-9_a-z~]|%[0-9a-f][0-9a-f]|[!$&-,:;=@])*+)*+)?+(?:\?+(?:[-.0-9_a-z~]|%[0-9a-f][0-9a-f]|[!$&' .
  '-,/:;=?+@])*+)?+(?:#(?:[-.0-9_a-z~]|%[0-9a-f][0-9a-f]|[!$&-,/:;=?+@])*+)?+`i';

$message = preg_replace($regex, '<a href="$0">$0</a>', $message);

if ($mail === '') {
  $salt = (string)((int)strrev(str_replace('.', '', $_SERVER['REMOTE_ADDR'])) + (int)floor(($nowtime + 32400) / 86400));
  $id = substr(crypt($_SERVER['REMOTE_ADDR'], $salt), -8);
} else {
  $id = '???';
}

if ($subject) {
  if (strlen($subject) === 0) {
    back('タイトルが空', 'タイトルなんにもないですが、何しますか？');
  } elseif (mbstrlen($subject) > 100) {
    back('いいぜ', 'そんなにタイトル長くして、どうかしましたか？');
  }
  $subject = h($subject);
}

$point = str_pad(substr($microtime, 10, 3), 3, '0');
$week = array('日', '月', '火', '水', '木', '金', '土');
$date = date('Y/m/d', $nowtime) . '(' . $week[date('w', $nowtime)] . ') ' . date('H:i:s', $nowtime) . $point;

$output = $name . '<>' . $mail . '<>' . $date . ' ID:' . $id . '<>' . $message . '<>' . "\n";
$datname = DAT_DIR . '/' . $key . '.dat';

if (!file_exists(DAT_DIR)) {
  if (!mkdir(DAT_DIR)) {
    die('error');
  }
}
chifable(DAT_DIR, 'w', 0705);

if (!$subject) {
  $filename = BAN_DIR . '/' . str_replace('/', '', crypt($_SERVER['REMOTE_ADDR'], md5($_SERVER['REMOTE_ADDR'])));
  if (!file_exists(BAN_DIR) && !mkdir(BAN_DIR)) {
    die('error');
  }
  chifable(BAN_DIR, 'r', 0705);
  if (file_exists($filename)) {
    chifable($filename, 'r');
    $file = RES_INTERVAL + (int)file_get_contents($filename, false, null, 0, 10);
    if ($nowtime < $file) {
      back('連投規制', '連投規制中！(あと' . ($file - $_SERVER['REQUEST_TIME']) . '秒待つと書けます)');
    }
  }
  chifable(BAN_DIR, 'w', 0705);
  if (file_exists($filename)) {
    chifable($filename, 'w');
  }
  file_put_contents($filename, $nowtime, LOCK_EX);

  if (!is_readable($datname) || !is_writable($datname)) {
    back('存在していない？', 'そのdatは無いか、書き込めない状態になってます。');
  }

  $sglsttxt = CNF_DIR . '/sglst.txt';
  if (is_readable($sglsttxt)) {
    $sglst = file_get_contents($sglsttxt);
    if ($sglst !== '') {
      $sglst = explode('<>', $sglst);
      foreach ($sglst as $sg) {
        if ($sg === $key) {
          $mod = filemtime($datname);
        }
      }
    }
  }

  if (preg_match('/sage|さげ|↓/i', $mail)) {
    $mod = filemtime($datname);
  }

  file_put_contents($datname, $output, FILE_APPEND | LOCK_EX);

  if (isset($mod)) {
    touch($datname, $mod);
  }
} else {
  $filename = CNF_DIR . '/ban_t.txt';
  if (!file_exists(CNF_DIR) && !mkdir(CNF_DIR)) {
    die('error');
  }
  chifable(CNF_DIR, 'r', 0705);
  if (!file_exists($filename) && !touch($filename)) {
    die('error');
  }
  chifable($filename, 'r');
  $file = THREAD_INTERVAL + (int)file_get_contents($filename, false, null, 0, 10);
  if ($nowtime < $file) {
    back('連投規制', '連投規制中！(あと' . ($file - $nowtime) . '秒待つと書けます)');
  }
  chifable(CNF_DIR, 'w', 0705);
  chifable($filename, 'w');
  file_put_contents($filename, $nowtime, LOCK_EX);

  file_put_contents($datname, $subject . $output, LOCK_EX);
}

setcookie('name', $_POST['NAME'], $_SERVER['REQUEST_TIME'] + 86400, '/');
setcookie('mail', $_POST['MAIL'], $_SERVER['REQUEST_TIME'] + 86400, '/');

showheader();
echo '<meta http-equiv="refresh" content="10;url=../../">',
'<title>書き込み完了 | 墓場</title>',
'<style type="text/css">',
'body{background:silver;color:black;}',
'hr{border:1px gray solid;}',
'</style>',
'</head>',
'<body>',
'<p>書き込みました。</p>',
'<hr>',
'<a href="../../">■掲示板に戻る■</a> | <a href="../../read.php/' . $key . '/">書き込んだスレッドに移動</a>',
'</body>',
'</html>';
die();

function brdel($str)
{
  return str_replace(array("\r\n", "\r", "\n"), '', $str);
}

function mbtrim($str)
{
  return str_replace(array(' ', "\t", "\r", "\0", "\x0B", '　'), '', $str);
}

function h($str)
{
  return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function mbstrlen($str)
{
  return mb_strlen($str, 'UTF-8');
}

function showheader()
{
  echo '<!DOCTYPE html>',
  '<html lang="ja">',
  '<head>',
  '<meta charset="utf-8">',
  '<link rel="icon" href="./favicon.ico">';
}

function chifable($filename, $flag = 'a', $permis = 0600)
{
  if ($flag === 'r') {
    if (!is_readable($filename) && !chmod($filename, $permis)) {
      die('error');
    }
  } elseif ($flag === 'w') {
    if (!is_writable($filename) && !chmod($filename, $permis)) {
      die('error');
    }
  } else {
    if (!is_readable($filename) || !is_writable($filename) and !chmod($filename, $permis)) {
      die('error');
    }
  }
}

function back($title, $str)
{
  die(showheader() . '<title>' . $title . ' | 墓場</title>' .
    '<style type="text/css">' .
    'body{background:silver;color:black;}' .
    'hr{border:1px gray solid;}' .
    'a:hover{color:red;}' .
    '</style>' .
    '</head>' .
    '<body>' .
    '<b>ERROR!!</b>' .
    '<p>' . $str . '</p>' .
    '<hr>' .
    '<a href="../../">■掲示板に戻る■</a>' .
    '</body>' .
    '</html>');
}

function generate_tripcode($key)
{
  $key = mb_convert_encoding($key, 'SJIS', 'UTF-8');
  if (strlen($key) >= 12) {
    $mark = substr($key, 0, 1);
    if ($mark === '#' || $mark === '$') {
      if (preg_match('|^#([[:xdigit:]]{16})([./0-9A-Za-z]{0,2})$|', $key, $str)) {
        $trip = substr(crypt(pack('H*', $str[1]), "$str[2].."), -10);
      } else {
        $trip = '???';
      }
    } else {
      $trip = str_replace('+', '.', substr(base64_encode(sha1($key, true)), 0, 12));
    }
  } else {
    $salt = strtr(preg_replace('/[^\.-z]/', '.', substr($key . 'H.', 1, 2)), ':;<=>?@[\\]^_`', 'ABCDEFGabcdef');
    $trip = substr(crypt($key, $salt), -10);
  }
  return '◆' . mb_convert_encoding($trip, 'UTF-8', 'SJIS');
}
