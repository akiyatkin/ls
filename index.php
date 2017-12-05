<?php
use infrajs\rest\Rest;
use infrajs\load\Load;
use infrajs\ans\Ans;
use infrajs\template\Template;
use infrajs\path\Path;
use infrajs\rubrics\Rubrics;

return Rest::get( function () {
	http_response_code(501);
}, [ function () {
	http_response_code(501);
}, [function ($dir, $type) {
	$ans = array();
	$src = '~'.$dir.'/';
	if (!Path::isNest('~', $src)) return Ans::err($ans, 'Передан некорректный или небезопасный путь');
	$chunk = Ans::GET('chunk', 'int', 0);
	$order = Ans::GET('order', ['ascending','descending'], 'descending');
	$list = array();
	array_map(function ($file) use (&$list, $src, $type) {
		if ($file{0} == '.') return;
		$file = Path::toutf($file);
		$fd = Load::nameInfo($file);
		if ($type =='pages') {
			if (!in_array($fd['ext'],['docx','tpl'])) return;
		} else {
			if (!in_array($fd['ext'],['jpg','png','jpeg'])) return;
		}
		$name = $fd['name'];
		
		$src = $src.$fd['file'];
		$page = Rubrics::info($src);
		$page['body'] = Rubrics::article($src);
		$list[] = $page;
		
	}, scandir(Path::theme($src)));
	Load::sort($list, $order);

	if ($chunk) {
		$list = array_chunk($list, $chunk);
	}

	$ans['list'] = $list;
	return Ans::ret($ans);
}, function ($dir, $type, $file) {
	$list = Load::loadJSON('-ls/'.$dir.'/'.$type);
	foreach ($list['list'] as $f) {
		if ($f['name'] == $file) {
			header('Location: /'.Path::theme($f['src']));
			return;
		}
	}
	http_response_code(404);
}]]);
