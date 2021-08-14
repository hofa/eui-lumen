<?php
// php
// $path = '/Users/supereatbb/mycode/php/ddz/check/bc-analysis-center/apiserver/api/Agent.php';

function include_lang_class($content)
{
    if (strpos('use think\\Lang;', $content) === false) {
        $content = str_replace('<?php', "<?php\nuse think\\Lang;", $content);
    }
    return $content;
}

function common_replace($content)
{
    $content = str_replace('！', '!', $content);
    $content = str_replace('，', ',', $content);
    $content = str_replace('（', '(', $content);
    $content = str_replace('）', ')', $content);
    // $content = str_replace('!', '', $content);
    // $content = str_replace('。', '.', $content);
    return $content;
}

function same_time_not($arr, $str)
{
    $r = true;
    foreach ($arr as $a) {
        if (strpos($str, $a) !== false) {
            $r = false;
            break;
        }
    }
    return $r;
}

function php_str_replace($str, $content, &$langs)
{
    if (empty($str)) {
        return $content;
    }
    $ls = ['"', "'"];
    $ls = ["'"];
    $funcs = ['Lang::get(', ')'];
    // echo $content;exit;

    // foreach ($ls as $l) {

    // if (mb_strpos($l . $str . $l, $content) !== false) {
    // echo "replace: ".$l . $str . $l.PHP_EOL;
    $r = same_time_not(['{', '}', '$', '[', ']'], $str);
    // echo "find: " . $str . ' r:' . ($r ? 'Y' : 'N') . PHP_EOL;
    if ($r && preg_match("/[\x7f-\xff]/", $str)) {
        // echo "yfind: " . $str . PHP_EOL;
        // echo $str, $funcs[0] .'|'. $str . $funcs[1].PHP_EOL;
        // $content = mb_ereg_replace($str, $funcs[0] . $str . $funcs[1], $content);
        $content = str_replace($str, $funcs[0] . trim($str) . $funcs[1], $content);
        $key = str_replace(['"', "'"], '', $str);
        if (!isset($langs[$key])) {
            $langs[$key] = "";
        }

    }
    // }
    // }
    return $content;
}

function php_execs($dir, $path, $lang_file)
{
    $content = file_get_contents($path);

    // preg_match_all("/^[x{4e00}-x{9fa5}a-za-z0-9_]+$/u", $content, $out, PREG_PATTERN_ORDER);

    // preg_match_all("/([\x{4e00}-\x{9fa5}]+)/u", $content, $out);

    // preg_match_all("/[\x{4e00}-\x{9fa5}A-Za-z0-9_,!]{1,}$/u", $content, $out);
    // preg_match_all("/([\x{4e00}-\x{9fa5},!0-9a-zA-Z]+)'/u", $content, $out);
    $newc = include_lang_class($content);
    $newc = common_replace($newc);
    preg_match_all("/[\"'](.*?)[\"']/", $newc, $out);
    // print_r($out);
    // exit;
    // exit;
    // $result = array_flip($out[0]);
    $unique_str = array_flip($out[0]); // 数组翻转

    $langs = !empty($lang_file) ? require $lang_file : [];
    foreach ($unique_str as $k => $v) {
        // echo $k.PHP_EOL;
        $newc = php_str_replace($k, $newc, $langs);
    }

    $new_path = str_replace('.phpxxxx', '.lang.phpxxxx', $path);
    file_put_contents($new_path, $newc);
    $langs = array_reverse($langs);
    $s = var_export($langs, true);

    $new_path = !empty($lang_file) ? $lang_file : $dir . basename($new_path) . '.php';
    file_put_contents($new_path, "<?php\nreturn " . $s . ";");
}

function list_dirs($dir, $callback)
{
    foreach (glob($dir . '/*.php') as $path) {
        $callback($dir, $path);
    }
}

function html_common_before_replace($content)
{
    $content = str_replace('！', '!', $content);
    $content = str_replace('，', ',', $content);
    $content = str_replace('（', '(', $content);
    $content = str_replace('）', ')', $content);

    return $content;
}

function html_common_after_replace($content)
{
    $content = str_replace("{:lang('{:lang", "{:lang", $content);
    $content = str_replace(")}')}", ")}", $content);
    return $content;
}

function html_str_replace($str, $content, &$langs)
{
    if (empty($str)) {
        return $content;
    }
    $ls = ['"', "'"];
    $ls = ["'"];
    $funcs = ['{:lang("', '")}'];
    // if (preg_match("/[\x7f-\xff]/", $str)) {
    echo 'find: ' . $str . PHP_EOL;
    if (strlen($str) < 200 && preg_match("/[\x7f-\xff]/", $str)) {
        // $str = trim($str);
        $start = $str[0];
        $end = $str[strlen($str) - 1];
        $start = $start == '"' ? "'" : $start;
        $end = $end == '"' ? "'" : $end;
        $s = substr($str, 1, strlen($str) - 2);
        $content = str_replace($str, $start . $funcs[0] . trim($s) . $funcs[1] . $end, $content);
        $key = trim(str_replace(['"', "'", '<', '>'], '', $str));
        if (!isset($langs[$key])) {
            $langs[$key] = "";
        }
    }

    // }
    return $content;
}

function html_execs($dir, $path, $lang_file)
{
    $content = file_get_contents($path);
    // preg_match_all("/([\x{4e00}-\x{9fa5}]+)/u", $content, $out);
    $newc = $content;
    $newc = html_common_before_replace($newc);
    preg_match_all("/[\"'>](.*?)[\"'<]/", $newc, $out);

    $unique_str = array_flip($out[0]); // 数组翻转

    $langs = !empty($lang_file) ? require $lang_file : [];
    foreach ($unique_str as $k => $v) {
        $newc = html_str_replace($k, $newc, $langs);
    }
    $newc = html_common_after_replace($newc);
    $new_path = str_replace('.htmlzzzz', '.lang.html', $path);
    file_put_contents($new_path, $newc);
    $langs = array_reverse($langs);
    $s = var_export($langs, true);
    $new_path = !empty($lang_file) ? $lang_file : $dir . basename($new_path) . '.php';
    file_put_contents($new_path, "<?php\nreturn " . $s . ";");
}

// $path = '/Users/supereatbb/mycode/php/ddz/check/bc-analysis-center/apiserver/config/config.awardgoldtypes.php';
$path = '';
$dir = '';
// $lang_file = '/Users/supereatbb/mycode/php/ddz/check/bc-analysis-center/application/lang/en-us-robot-auto.php';
// $lang_file = '/Users/supereatbb/mycode/php/ddz/check/bc-analysis-focus/application/lang/en-us-robot-auto.php';
$lang_file = '/Users/supereatbb/mycode/php/ddz/check/bc-agent/application/lang/en-us-robot-auto.php';
// echo $argv[1].PHP_EOL;
$path = isset($argv[1]) ? $argv[1] : $path;
if (!empty($path) && is_file($path)) {
    $pathinfo = pathinfo($path);
    $affix = $pathinfo['extension'];
    echo 'Run:' . $affix . PHP_EOL;
    if ($affix == 'php') {
        php_execs($dir, $path, $lang_file);
    } else if ($affix == 'html') {
        html_execs($dir, $path, $lang_file);
    }
}
echo 'finish' . PHP_EOL;
