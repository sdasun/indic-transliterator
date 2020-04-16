<?php

if ($scriptfrom==""){
	return;
}

$scriptto=$_POST["scriptto"];
$scriptfrom=$_POST["scriptfrom"];
$text=stripslashes($_POST["text"]);
$textOriginal=$text;
$docFrom = new DOMDocument();
$docTo = new DOMDocument();		
$docFrom->load( "script/$scriptfrom.xml" );
$docTo->load( "script/$scriptto.xml" );

//Translitarate to common notation
$script= $docFrom->getElementsByTagName( "script" );
$language = $script->item(0)->getElementsByTagName( "language" );
$letters = $language->item(0)->getElementsByTagName( "letter" );

foreach( $letters as $letter )
{
	$code = $letter->getAttribute('code');
	$custom = $letter->getAttribute('custom');
	$text=str_replace($code,$custom,$text);
}
$chrs = array ("\n", "\t", " ",".","!", "/","\\");
$repl = array ("\n", "\t{start}", " {start}",".{start}","!{start}", "/{start}","\\{start}");
$text = str_replace($chrs, $repl, $text);

//Applying rules for output
$script= $docFrom->getElementsByTagName( "script" );
$pipeline = $script->item(0)->getElementsByTagName( "pipeline-out" );
$rules = $pipeline->item(0)->getElementsByTagName( "rule" );
foreach( $rules as $rule )
{
	$in  = $rule->getAttribute('in');
	$out = $rule->getAttribute('out');
	$out = str_replace("[c","[c-a",$out);
	$out = str_replace("[v","[v-a",$out);
	$out = str_replace("[s","[s-a",$out);
	$text= str_replace($in,$out,$text);
}
$text= str_replace("[c-a","[c",$text);
$text= str_replace("[v-a","[v",$text);
$text= str_replace("[s-a","[s",$text);

//Applying rules for input
$script= $docTo->getElementsByTagName( "script" );
$pipeline = $script->item(0)->getElementsByTagName( "pipeline-in" );
$rules = $pipeline->item(0)->getElementsByTagName( "rule" );
foreach( $rules as $rule )
{
	$in  = $rule->getAttribute('in');
	$out = $rule->getAttribute('out');
	$out = str_replace("[c","[c-a",$out);
	$out = str_replace("[v","[v-a",$out);
	$out = str_replace("[s","[s-a",$out);
	$text= str_replace($in,$out,$text);
}
$text= str_replace("[c-a","[c",$text);
$text= str_replace("[v-a","[v",$text);
$text= str_replace("[s-a","[s",$text);
//echo $text;

//Translitarate from common notation to Indic
$script= $docTo->getElementsByTagName( "script" );
$language = $script->item(0)->getElementsByTagName( "language" );
$letters = $language->item(0)->getElementsByTagName( "letter" );

if ($scriptto=="iso15919")
{
	foreach( $letters as $letter )
	{
		$code = $letter->getAttribute('code');
		$custom = $letter->getAttribute('custom');
		if (substr($custom, 0, 2)=="[c")
		{
			$text=str_replace($custom,$code."a",$text);
		} else if (substr($custom, 0, 2)=="[s")
		{
			$text=str_replace($custom,"[vir]".$code,$text);
		} else 
		$text=str_replace($custom,$code,$text);
	}
	$text = str_replace("a[vir]", "", $text);
	$text = str_replace("[vir]", "", $text); //Remove any VIRAMA if there is
	$text = str_replace("‍", "", $text); //Remove Zero Width Joiner
	$text = str_replace("‌", "", $text);//Remove Zero Width None Joiner
}else
{
	foreach( $letters as $letter )
	{
		$code = $letter->getAttribute('code');
		$custom = $letter->getAttribute('custom');
		$text=str_replace($custom,$code,$text);
	}	
}

$text = str_replace("{start}", "", $text);

// $text=str_replace("\r\n", "<br/>", $text);
// $text=str_replace("\n", "<br/>", $text);
