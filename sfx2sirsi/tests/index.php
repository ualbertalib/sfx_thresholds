<?php
require('config.inc.php');

echo "<link href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css' rel=\"stylesheet\" />";
?>

<style>
	.Good{
		background-color: green;
	} 
	.Bad{
		background-color: red;
	}
</style>
<body>
<?php

// objectID => evaluated threshold
$testData = array('110978977730741' => '(1889)-',
				  '110978976472911' => '(1890) - (2002) (2010)- [IS_FREE]',
				  '110978979595975' => '(2007)- [IS_FREE]',
				  '954922649138' => '(1989) - (1994) (1997)- Most recent 6 month(s) not available',
				  '991042728005534' => '(1971)(1975) - (1976)',
				  '963018263111' => '(1996)- Most recent 21 month(s) not available [IS_FREE]',
				  '954925475460' => '(1977)-',
				  '111021054522004' => '(1954)- [IS_FREE]',
				  '110975506068985' => '(1868) - (1909) (1911) - (1923) (2001)- [IS_FREE]',
				  '954925434435' => '(1886)-',
				  '110985822454272' => '(1967) - (1973)',
				  '110978977734311'=> '(1948) - (2000) (2003)-',
				  '991042752811136'=> '(1997)-',
				  '954925373786'=>'(1953) - (2012)',
				  '1000000000526658' => '(1912) - (1912) [IS_FREE]',
				  '111088196500348' => '(2002)-',
				  '991042740551666' => '(1998) - (1999)',
				  '110992357391240' => '(1994) - (1997)',
				  '954921355368' => '(1962)- [IS_FREE]',
				  '110978978550329' => '(2001) - (2002)',
				  '954925553394' => '(1986) - (2011)',
				  '960239086778'=> '(1975) - (1995) (1998) - (2003)',
				  '110978977280124' => '(1966)- [IS_FREE]',
				  '954922828207' => '(1849)- [IS_FREE]',
				  '954925612992' => '(1995)- [IS_FREE]',
				  '110978977973856' => '(1964) - (1972) (1992)(2000) - (2014)',
				  '954927539088' => '(1951)-',
				  '954927646699' => '(1972) - (1974) (1976) - (1977) (1979) - (1982) (1984)',
				  '110992357336632' => '(1953)(1956)(1971)',
				  '110978979596013' => '(1991) - (2004)',
				  '954927684666' => '(1983) - (1999) (2002) - (2002)'
				);


foreach($testData as $objectId => $expectedResult){
	
	$postData = array('objectId'=>$objectId, 'debugMode'=>false); 

	curl::fetch($POST_TO_URL,array(
	    CURLOPT_USERAGENT=>'Mozilla 6.0',
	    CURLOPT_ENCODING=>'gzip,deflate',
	    CURLOPT_POST=>1, 
	    CURLOPT_POSTFIELDS=>$postData, 
	    CURLOPT_TIMEOUT=>5,   
	    CURLOPT_HEADERFUNCTION=>array('curl','head'),
	    CURLOPT_WRITEFUNCTION=>array('curl','body')
	));



	$body = curl::$b;


	$bodyParts = explode('--',$body); 
	


	$actualResult = trim($bodyParts[1]); 
	$actualResult = preg_replace( "/\r|\n/", "", $actualResult );
	$actualResult = trim(str_replace("<br>","",$actualResult));
	
 
/* 
	echo "\n<table border=1>\n"; 
	echo "<tr>\n<td>actual</td><td>expected</td></tr>"; 
	echo "<tr>\n<td>"  . $actualResult . "</td>"; 
	echo "\n<td>"  . $expectedResult . "</td>\n</tr>"; 
	echo "</table><hr>"; 
*/
	$row[] = array('objectid'=> $objectId, 'expected'=>$expectedResult , 'actual'=>$actualResult, 'status'=> ($expectedResult==$actualResult ? 'Good' : 'Bad') );
	

}
 
//print_r($row);
echo "<table border=1 class='table'><tbody><tr><td>ObjectID</td><td>Expected</td><td>Actual</td><td>Status</td></tr>";
foreach($row as $key=>$value){

	echo "<tr class='{$row[$key]['status']}'><td>{$row[$key]['objectid']}</td><td>{$row[$key]['expected']}</td><td>{$row[$key]['actual']}</td><td>{$row[$key]['status']}</td></tr>";

}

echo "</tbody></table>";