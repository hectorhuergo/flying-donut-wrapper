#!/bin/php
<?php
/**
 * Flying-donut API wrapper. By default, prints text tree to standard output.
 * This simple helper was motivated for the imposibility of view the Time tracking comments and values in 
 * the flying-donut's UI.
 * @author: h4
 * @date: 20170225
 *
 * Usage:
 * $ php flying-donut.php --apikey=aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa --project_id=bbbbbbbbbbbbbbbbbbbbbbbb 
 * [--method=iterations | milestones | backlog | iterations/{iteration_id}/items | milestones/{milestone_id}/items] 
 * [--mode=xml | tree]
 *
 */



/**
 * @autor: anonymous http://php.net/manual/en/features.commandline.php#81176
 */
function arguments ($args){
	array_shift($args);
	$args = join($args, ' ');
	preg_match_all('/ (--\w+ (?:[= ] [^-]+ [^\s-])?) | (-\w+) | (\w+) /x', $args, $match);
	$args = array_shift($match);
	$ret = array('input'    => array(),'commands' => array(),'flags'    => array());
	foreach ($args as $arg) {
		// Is it a command? (prefixed with --)
		if (substr($arg, 0, 2) === '--') {
			$value = preg_split('/[= ]/', $arg, 2);
			$com   = substr(array_shift($value), 2);
			$value = join($value);
			$ret['commands'][$com] = !empty($value) ? $value : true;
			continue;
		}
		// Is it a flag? (prefixed with -)
		if (substr($arg, 0, 1) === '-') {
			$ret['flags'][] = substr($arg, 1);
			continue;
		}
		$ret['input'][] = $arg;
		continue;
	}
	return $ret;
}

/**
 * tree() prints text tree or xml tree from objects or arrays.
 * 
 */
 
function tree(&$o,$l=0,$m='tree'){
	$a = false;
	foreach ($o as $key=>$value) {
		if (is_object($value) || is_array($value)) {
			$t = $m != 'xml' ? str_repeat("│",$l)."─ ".$key."\n" : str_repeat("\t",$l).'<_'.str_replace('$','_',$key).'>'."\n";
			echo $t;
			$a = true;
			tree($value,$l+1,$m);
		}
		else {
			$t = $m != 'xml' ? str_repeat("│",$l)."─ ".$key.": ".trim($value)."\n" : str_repeat("\t",$l).'<_'.str_replace('$','_',$key).'>'.$value.'</_'.str_replace('$','_',$key).'>'."\n";
			echo $t;
			$a = false;
		}
		if ($a && $m == 'xml' && isset($key)) echo str_repeat("\t",$l).'</_'.str_replace('$','_',$key).'>'."\n";
	}
	//│─
}

$arg = arguments($argv);

$apikey = "";
$project_id = "";
$command = "";

$server = "https://www.flying-donut.com/api/projects/";

$apikey = @$arg['commands']['apikey'];

$project_id = @$arg['commands']['project_id'];

$method = @$arg['commands']['method'];

$mode = @$arg['commands']['mode'];

$url = $server.$project_id;

$method != "" ? $url.='/'.$method : null;

$c = curl_init($url);

$d = array('Authorization: secret '.$apikey);

if ($apikey == "" || $project_id == "" || isset($arg['commands']['help'])) {
	echo "\nFlying-donut API wrapper. By default, prints text tree to standard output.
This simple helper was motivated for the imposibility of view the Time tracking 
comments and values in the flying-donut's UI.

Usage:

 $ php flying-donut.php --apikey=aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa 
   --project_id=bbbbbbbbbbbbbbbbbbbbbbbb 
   [--method=iterations | milestones | backlog | iterations/{iteration_id}/items | 
   milestones/{milestone_id}/items]
   [--mode=xml | tree]
 
/* @author: h4 <hectorhuergo@gmail.com> * @date: 20170225 */
 \n";
	die();
}

curl_setopt($c, CURLOPT_HTTPHEADER, $d);
curl_setopt($c, CURLOPT_HEADER, 0);
curl_setopt($c, CURLOPT_POST, 0);
curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec($c);

curl_close($c);

$o = json_decode($response);

if ($mode == 'xml') echo '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n\t".'<project id="'.$project_id.'" method="'.$method.'">'."\n";

is_object($o) || is_array($o) ? tree($o,0,$mode) : die("Nothing to show. Please review your parameters.\n");

if ($mode == 'xml') echo '</project>';
?>
