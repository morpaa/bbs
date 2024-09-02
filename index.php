<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <link rel="icon" href="./favicon.ico">
  <title>墓場</title>
  <style type="text/css">
    body {
      background-color: silver;
      color: black;
    }

    h2 {
      color: red;
      padding: 0 0 16px 0;
      margin: 0;
    }

    hr {
      border: 1px gray solid;
    }

    dl {
      padding-bottom: 5px;
    }

    dd {
      margin-bottom: 19px;
    }

    p {
      font-weight: bold;
      margin: 10px 0 2px;
    }

    a {
      color: blue;
      text-decoration: underline;
    }

    a:link {
      color: blue;
    }

    a:visited {
      color: indigo;
    }

    a:hover {
      color: red;
    }

    label {
      padding-left: 5px;
    }

    label:after {
      content: "：";
    }

    .fs2 {
      font-size: 13px;
    }

    div.b {
      width: 95%;
      background: gainsboro;
      border: 2px gray solid;
      border-radius: 8px;
      margin: 0 auto 16px;
    }

    .c {
      border: 1px gray solid;
      border-radius: 4px;
      padding: 4px;
      margin: 4px;
    }

    .right {
      text-align: right;
    }

    .submenu {
      display: block;
      margin: 2px 0 16px;
    }
  </style>
</head>

<body>
  <h1 style="font-size:26px;text-align:center;padding:10px 0;margin:0">墓場</h1>
  <div class="b">
    <div class="c">
      <div class="fs2">
        <p>■禁止行為</p>
        ・法律に違反する行為<br>
        ・荒らしまたはそれに類する行為<br>
        ・宣伝用のスレッドを建てる行為<br>
        <span style="color:red">※違反した場合IP晒したり規制したりします。 ご了承ください。。</span>
        <p>■この掲示板でできること</p>
        ・安価が使えます<br>
        ・10桁、12桁、生キーのトリップが使えます(15桁は使えないです)<br>
        ・自動リンク対応してます<br>
        ・&lt;c:red&gt;のような形式で入力するとfontタグもどきが使えます<br>
        ・&lt;special:saikoro1&gt;みたいな形式でサイコロが表示できます(数字の部分には半角で1から5を入力)<br>
        ・&lt;special:ua&gt;でユーザーエージェントが表示されます
      </div>
      <p>Links&ensp;<a href="./history.html">更新履歴</a></p>
    </div>
  </div>
  <a id="menu"></a>
  <div class="b">
    <div class="c">
      <div class="fs2">
        <?php
        $_COOKIE['name'] = isset($_COOKIE['name']) ? ' value="' . htmlspecialchars($_COOKIE['name'], ENT_QUOTES, 'utf-8') . '"' : '';
        $_COOKIE['mail'] = isset($_COOKIE['mail']) ? ' value="' . htmlspecialchars($_COOKIE['mail'], ENT_QUOTES, 'utf-8') . '"' : '';
        $folder = 'dat';
        if (!is_readable($folder) && !chmod($folder, 0705)) {
          echo 'ERROR. フォルダ読み込めませんでした。 処理を中断しますよ。',
          '</div>',
          '</div></div>',
          threadform();
          die('</body></html>');
        }
        $files = scandir($folder);
        unset($files[0]);
        unset($files[1]);
        $total = count($files);
        $i = 0;
        $continue = 0;
        foreach ($files as $value) {
          $dat = $folder . '/' . $value;
          if (!is_readable($dat)) {
            ++$continue;
            continue;
          }
          $data[$i] = file($dat, FILE_IGNORE_NEW_LINES);
          if (!isset($data[$i][0]) || !isset($data[$i][1])) {
            ++$continue;
            continue;
          }
          $title[] = $data[$i][0] . '(' . (count($data[$i]) - 1) . ')';
          unset($data[$i][0]);
          ++$i;
          $updatetime[] = filemtime($dat);
          $values[] = strstr($value, '.', true);
        }
        $total -= $continue;
        if ($total === 0) {
          echo 'スレッドはないようです。',
          '</div>',
          '</div></div>';
          threadform();
          die('</body></html>');
        }
        arsort($updatetime, SORT_NUMERIC);
        $no = 0;
        $new = 5;
        foreach ($updatetime as $key => $value) {
          $to = ++$no <= $new ? ' <a href="#' . $no . '">' : ' <a href="./read.php/' . $values[$key] . '/l50">';
          echo '<a href="./read.php/', $values[$key], '/l50">', $no, ':</a>', $to, $title[$key], '</a>　';
        }
        echo '<br>',
        '<div class="right">',
        $total, '個のスレッド。。<b><a href="./read.php/">スレッド一覧はこちら</a></b>&emsp;';
        if ($continue === 0) {
          echo 'dat正常取得完了';
        } else {
          echo 'コンティニュー', $continue, '回しました。変なdatがあるようです。';
        }
        echo '</div>',
        '</div>',
        '</div></div>';
        $i = 0;
        $no = 0;
        $max = 5;
        $last = $total > $max ? $max : $total;
        foreach ($updatetime as $key => $value) {
          ++$no;
          $up = $no - 1 !== 0 ? $no - 1 : $last;
          $down = $no + 1 <= $last ? $no + 1 : 1;
          echo '<div class="b"><div class="c">',
          '<a id="', $no, '"></a><div class="right"><a href="#menu">■</a><a href="#test">●</a><a href="#', $up, '">▲</a><a href="#', $down, '">▼</a></div>',
          '<h2>', $title[$key], '</h2>',
          '<dl>';
          $standard = count($data[$key]);
          $j = $standard > $new ? $new : $standard;
          if ($standard > $new) $tmp[0] = $data[$key][1];
          $less = $standard > $new ? array_merge($tmp, array_splice($data[$key], -$j)) : array_splice($data[$key], -$j);
          $void = true;
          foreach ($less as $value) {
            if ($void === false) {
              $portion = '<a href="./read.php/' . $values[$key] . '/' . ($standard - $j) . '" rel="nofollow">';
              $value = $portion . ($standard - $j) . '</a><>' . $value;
            } else {
              $portion = '<a href="./read.php/' . $values[$key] . '/1" rel="nofollow">';
              $value = $portion . '1</a><>' . $value;
              $void = false;
              if ($standard === $j) --$j;
            }
            --$j;
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
            $n = substr_count($split[4], '<br>');
            $message = $n < 7 ? $split[4] : join('<br>', explode('<br>', $split[4], -$n + 6)) . '<br><font color="green">（省略しました…全部読むには' . $portion . 'ここ</a>押してください）</font>';
            echo '<dt>', $split[0], ' 名前：', $name, ' ', $split[3], '</dt><dd>', $message, '</dd>';
          }
          echo '</dl>',
          '<form method="post" action="./write.php/', $values[$key], '/" class="write">',
          '<input type="submit" value="書き込む">',
          '<label for="name">名前</label><input type="text" size="19"', $_COOKIE['name'], ' id="name" name="NAME">',
          '<label for="mail">E-mail</label><input type="text" size="19"', $_COOKIE['mail'], ' id="mail" name="MAIL"><br>',
          '<textarea rows="5" cols="64" wrap="off" name="MESSAGE"></textarea><input type="hidden" value="', $values[$key], '" name="KEY">',
          '</form>',
          '<b class="submenu"><a href="./read.php/', $values[$key], '/">全部読む</a> <a href="./read.php/', $values[$key], '/l30">最新30</a> <a href="./read.php/', $values[$key], '/l50">最新50</a> <a href="./read.php/', $values[$key], '/-100">1-100</a> <a href="#menu">板のトップ</a> <a href="./">リロード</a></b>',
          '</div></div>';
          if (--$max === 0) break;
        }
        threadform();
        echo '<a id="test"></a>',
        '</body>',
        '</html>';
        function threadform()
        {
          echo '<div class="b"><div class="c">',
          '<p>スレッド作成</p>',
          '<form method="post" action="./write.php/why/" style="margin-left:10px">',
          'タイトル：<input type="text" size="40" name="SUBJECT"><input type="submit" value="Create a new thread!"><br>',
          '名前：<input type="text" size="19"', $_COOKIE['name'], ' name="NAME">E-mail：<input type="text" size="19"', $_COOKIE['mail'], ' name="MAIL"><br>',
          '内容：<textarea rows="5" cols="64" wrap="off" style="vertical-align:top" name="MESSAGE"></textarea>',
          '</form>',
          '</div></div>';
        }
