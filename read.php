<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <link rel="icon" href="./favicon.ico">
  <?php
  $_COOKIE['name'] = isset($_COOKIE['name']) ? ' value=' . $_COOKIE['name'] : '';
  $_COOKIE['mail'] = isset($_COOKIE['mail']) ? ' value=' . $_COOKIE['mail'] : '';
  $folder = 'dat';
  if (preg_match('{/([1-9]\d{9})}', $uri = $_SERVER['REQUEST_URI'], $match)) {
    $threadnumber = $match[1];
    $dat = $folder . '/' . $threadnumber . '.dat';
    if (!is_readable($dat)) comeback('dat読めない', 'datよめないです。なぜでしょう＞＜');
    $data = file($dat, FILE_IGNORE_NEW_LINES);
    echo '<title>', $data[0], ' | 墓場</title>',
    '<style type="text/css">',
    'body{background:silver;color:black;}',
    'hr{border:1px gray solid;}',
    'h1{color:red;font-size:larger;font-weight:normal;margin:-.5em 0 0;}',
    'dd{margin-bottom:19px;}',
    'a{color:blue;text-decoration:underline;}',
    'a:link{color:blue;}',
    'a:visited{color:indigo;}',
    'a:hover{color:red;}',
    '</style>',
    '</head>',
    '<body>',
    '<a id="up"></a>',
    '<a href="../../">■掲示板に戻る■</a> <a href="../', $threadnumber, '/">全部</a> <a href="../', $threadnumber, '/-100">1-100</a> <a href="../', $threadnumber, '/l30">最新30</a> <a href="../', $threadnumber, '/l50">最新50</a> <a href="../', $threadnumber, '/#down">↓</a>',
    '<hr style="background-color:#888;color:#888;border-width:0;height:1px;position:relative;top:-.4em">',
    '<h1>', $data[0], '</h1>',
    '<dl>';
    array_shift($data);
    $i = 0;
    foreach ($data as $value) {
      $data[$i] = ($i + 1) . ' ：<>' . $value;
      ++$i;
    }
    if (strpos($uri, 'n') !== false) {
      $flg = true;
    } else {
      $flg = false;
    }
    if (preg_match('/l([1-9]\d*)/', $uri, $match)) {
      $n = $match[1];
      if ($n <= $i) {
        $add[0] = $data[0];
      } else {
        $add = array();
      }
      if ($n > $i) $n = $i;
      $data = array_merge($add, array_splice($data, -$n));
    } elseif (preg_match('{\d/(([1-9]\d*)?-[1-9]\d*|([1-9]\d*,)+[1-9]\d*|[1-9]\d*(n)?-|[1-9]\d*)(n)?}', $uri, $n)) {
      $n = (int)str_replace('n', '', $n[1]);
      $pos = strpos($n, '-');
      if ($pos !== false) {
        $r = (int)substr($n, ($pos + 1));
        $l = (int)strstr($n, '-', true);
        if ($r > $i || $r === 0) $r = $i;
        if ($l === 0) {
          $l = 1;
        } elseif ($l > $i) {
          $l = $i;
        } elseif ($l > $r) {
          $l = $r;
        }
        if ($l !== 1 && $r !== 1) {
          $add[0] = $data[0];
        } else {
          $add = array();
        }
        $loop = range($l, $r);
        --$l;
        foreach ($loop as $loop) {
          $tmp[] = $data[$l];
          ++$l;
        }
        $data = array_merge($add, $tmp);
      } elseif (strpos($n, ',') !== false) {
        $split = explode(',', $n);
        $j = 0;
        foreach ($split as $value) {
          if ($value > $i) $split[$j] = $i;
          ++$j;
        }
        sort($split, SORT_NUMERIC);
        foreach ($split as $value) {
          $tmp[] = $data[$value - 1];
        }
        $add[0] = $data[0];
        $data = array_unique(array_merge($add, $tmp));
      } else {
        if ($n > $i) $n = $i;
        $tmp[0] = $data[$n - 1];
        if ($n !== 1) {
          $add[0] = $data[0];
          $data = array_merge($add, $tmp);
        } else {
          $data = $tmp;
        }
      }
    }
    if ($flg && count($data) !== 1) unset($data[0]);
    foreach ($data as $value) {
      $split = explode('<>', $value);
      if (count($split) !== 6) {
        if ($split[1] !== '') echo '<dt>ここ壊れてます</dt><dd>このレス形式おかしいから無視するよ</dd>';
        continue;
      }
      unset($split[6]);
      $pos = mb_strpos($split[1], '◆', 0, 'utf-8');
      if ($pos !== false) {
        $name = mb_substr($split[1], 0, $pos);
        $trip = ' ' . mb_substr($split[1], $pos);
      } else {
        $name = $split[1];
        $trip = '';
      }
      if ($name === '' && $trip === '') $name = '墓の中の名無しさん';
      if ($split[2] === '') {
        $name = '<font color="green"><b>' . $name . '</b>' . $trip . '</font>';
      } elseif ($split[2] === 'sage' || $split[2] === 'さげ' || $split[2] === '↓') {
        $name = '<a href="mailto:' . $split[2] . '"><b>' . $name . '</b>' . $trip . '</a>';
      } elseif (mb_strpos($name, '★', 0, 'utf-8') !== false) {
        $name = '<font color="black"><b>' . $name . '</b>' . $trip . '</font>';
      } elseif (substr($split[2], 0, 1) === '#') {
        $name = '<font color="indigo"><b>' . $name . '</b>' . $trip . '</font>';
      } else {
        $name = '<a href="mailto:' . $split[2] . '"><b>' . $name . '</b>' . $trip . '</a>';
      }
      echo '<dt>' . $split[0] . $name . '：' . $split[3] . '</dt><dd>' . $split[4] . '</dd>';
    }
    $stnd = isset($l) ? $l : 1;
    $min = $stnd - 100 < 1 ? 1 : $stnd - 100;
    $max = $stnd - 1 < 1 ? 1 : $stnd - 1;
    $for = $min . '-' . $max;
    $stnd = isset($r) ? $r : $i;
    $min = $stnd + 1 > $i ? $i : $stnd + 1;
    $max = $stnd + 100 > $i ? $i : $stnd + 100;
    $next = $min . '-' . $max;
    die('</dl>' .
      '<div style="width:57%;text-align:center"><hr><a href="../' . $threadnumber . '/' . $i . 'n-">新着レスの表示</a><hr></div>' .
      '<a href="../../">■掲示板に戻る■</a> <a href="../' . $threadnumber . '/">全部</a> <a href="../' . $threadnumber . '/' . $for . '">前100</a> <a href="../' . $threadnumber . '/' . $next . '">次100</a> <a href="../' . $threadnumber . '/l30">最新30</a> <a href="../' . $threadnumber . '/l50">最新50</a> <a href="../' . $threadnumber . '/#up">↑</a>' .
      '<form method="post" action="../../write.php/' . $threadnumber . '/">' .
      '<input type="submit" value="書き込む">' .
      '名前：<input type="text" size="19"' . $_COOKIE['name'] . ' name="NAME">' .
      'E-mail<span style="font-size:10px">(省略可)</span>：<input type="text" size="19"' . $_COOKIE['mail'] . ' name="MAIL"><br>' .
      '<textarea rows="5" cols="70" wrap="off" name="MESSAGE"></textarea>' .
      '<input type="hidden" value="' . $threadnumber . '" name="KEY">' .
      '</form>' .
      'read.php <a href="../../">墓場</a>' .
      '<a id="down"></a>' .
      '</body>' .
      '</html>');
  }
  if (!is_readable($folder)) {
    die('えらー。dat格納してるふぉるだがみつかりませんでした。' . '</body></html>');
  }
  $files = scandir($folder);
  unset($files[0]);
  unset($files[1]);
  $count = count($files);
  echo '<title>スレッド一覧 | 墓場</title>' .
    '<style type="text/css">' .
    'body{background:silver;color:black;}' .
    'h1{display:inline;font-size:18px;}' .
    'hr{border:1px gray solid;}' .
    'a:hover{color:red;}' .
    '</style>' .
    '</head>' .
    '<body>' .
    '<a href="../">■掲示板に戻る■</a>' .
    '<hr style="background-color:#888;color:#888;border-width:0;height:1px;position:relative;top:-.4em">' .
    '<div style="margin-bottom:16px"><h1>スレッド一覧</h1><small>( <span style="color:red;font-size:16px;font-weight:bold;font-family:Arial,sans-serif">', $count, '個</span> のスレッドがあるのかな )</small></div>';
  if ($count === 0) {
    die('スレッドが、ないです。ひとつも' . '</body></html>');
  }
  $continue = 0;
  foreach ($files as $value) {
    $dat = $folder . '/' . $value;
    if (!is_readable($dat)) {
      ++$continue;
      continue;
    }
    $data = file($dat, FILE_IGNORE_NEW_LINES);
    if (!isset($data[0]) || $data[1] === null) {
      ++$continue;
      continue;
    }
    $title[] = $data[0] . '(' . (count($data) - 1) . ')';
    $updatetime[] = filemtime($dat);
    $values[] = strstr($value, '.', true);
  }
  arsort($updatetime, SORT_NUMERIC);
  $no = 0;
  foreach ($updatetime as $key => $value) {
    ++$no;
    echo $no . ': <a href="./read.php/' . $values[$key] . '/l50">' . $title[$key] . '</a><br>';
  }
  echo '<hr>' . $continue . '<hr>' .
    '<a href="../">■掲示板に戻る■</a>' .
    '</body>' .
    '</html>';
  function comeback($title, $string)
  {
    die('<title>' . $title . ' | 墓場</title>' .
      '<style type="text/css">' .
      'body{background:silver;color:black;}' .
      'hr{border:1px gray solid;}' .
      'a:hover{color:red;}' .
      '</style>' .
      '</head>' .
      '<body>' .
      '<b>ERROR!!</b>' .
      '<p>' . $string . '</p>' .
      '<hr>' .
      '<a href="../../">■掲示板に戻る■</a>' .
      '</body>' .
      '</html>');
  }
