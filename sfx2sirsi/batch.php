<?php

/*
 * Description of batchTest
 * 
 * @author Jeremy Hennig <jhennig@ualberta.ca>
 */
set_time_limit(0);
ini_set('display_errors',1);
error_reporting(E_ALL);

function cleanSpaces($str){	
	
	$str = str_replace(") - Most recent", ")-Most recent", $str);
	return str_replace(") - (", ")-(", $str);
		
}


try{
$error = array();	
include_once('config.inc.php');


$myDate = new DateTime('now');

//$fileWriter = new FileWriter();
 
//$fileWriter->write('Date File Created: ' . $myDate->format('Y-M-d H:i:s') . "\n");

$counter = 0;

if (defined('STDIN') && isset($argv[1])) {
  $objectId = $argv[1];
} elseif (isset($_POST['objectId'])) { 
  $objectId = $_POST['objectId'];
}

if (isset($objectId)){
$sfx_query = "SELECT distinct OBJECT_ID 
FROM thresholds
WHERE OBJECT_ID IN ( {$objectId} ) 
ORDER BY OBJECT_ID;";

}else{

$sfx_query = "SELECT distinct OBJECT_ID 
FROM thresholds
ORDER BY OBJECT_ID";
}

 

$sth = $conn->prepare($sfx_query);
$sth->execute();
$result = $sth->fetchAll(PDO::FETCH_ASSOC);

echo "Number of Objects:" . $sth->rowCount() ."\r\n";

 $sfx_query = "SELECT DISTINCT OP_ID, trim(Threshold) Threshold, trim(LCL_Threshold) LCL_Threshold , IS_FREE 
            FROM  thresholds
            WHERE object_id = :object_id            
            ORDER BY object_id";

$sth1 = $conn->prepare($sfx_query);


		
foreach ($result as $row ){
    
    
        $obj = new Threshold2();
		if(isset($_POST['debugMode'])){			
			$obj->setDebug($_POST['debugMode']);
		}

       
        
		$sth1->bindParam(':object_id', $row['OBJECT_ID']);
		$sth1->execute();
		$result2 = $sth1->fetchAll();
		
		
        $runonce=0;
        $IS_FREE=0;
        echo "" . $row['OBJECT_ID'] . " | ";
         foreach ( $result2 as $row2 ){
                            if ($row2['IS_FREE']==1){
                                $IS_FREE=1;
                            }
                            if ($row2['LCL_Threshold']!=""){
                                    $mythreshold = trim($row2['LCL_Threshold'],'&|');
                            }else{
                                    $mythreshold = trim($row2['Threshold'],'&|');
                            }   
                            
                    $obj->setOP_ID($row2['OP_ID']);                
                   
                    $mythreshold=cleanFunctionParameters($mythreshold);
                    $thresh = explode("||",$mythreshold);
                    
					
                    foreach($thresh as $key => $value){
						
					
          						//if the method exists then process otherwise throw an error.
          						if( $thresh[$key] ){
          							$myMethod = getMethodName($thresh[$key]);
									
									//ob_start();
          							if(method_exists('Threshold2',$myMethod) )  {
										
										
										$call = $thresh[$key];	
										
										try {											
												$worked = @eval($call . "; return true;"); 		
											} catch (ParseError $e) {
												echo 'Caught exception: '.$e->getMessage()."\n";
												$error[] = "Object: {$row['OBJECT_ID']}: Error -> " . $e->getMessage();
											} catch (ArgumentCountError $e){
												echo 'Caught exception: '.$e->getMessage()."\n";
												$error[] = "Object: {$row['OBJECT_ID']}: Error -> " . $e->getMessage();
											} catch (Exception $e){
												$error[] = "Object: {$row['OBJECT_ID']}: Error -> " . $e->getMessage();
											}
										
									
										
          							}else{
										
										$warning = "Warning - Method: " . $thresh[$key] . " is not defined. '". $myMethod . "' Does not exist\n Error occured on object ID: " . $row['OBJECT_ID'];
										$error[] = $warning;
																		
										
          								continue 2;
          							}
									
									//ob_flush();
									//ob_end_clean();
          						}
						
						
                    }
                            
             
             
             $runonce=1;
             $counter +=1;
         }
          $obj->timespan_merge();
              $obj->removeOverlap();
          if ($runonce==1){
           
            $msg = $obj->translateDateRange();
			//$msg .= "TRANSLATE";
			 
			
			
            //fwrite($myFile,$msg);
			if ($IS_FREE==1){
				echo cleanSpaces($msg) . "[IS_FREE] \r\n";
			}else{
				echo cleanSpaces($msg) . "[NOT_FREE] \r\n";
			}
         }

}




if(count($error)>0){
	$body = implode("\r\n", $error);
	mail($ERROR_EMAILS, "Error in Threshold Process", $body, "from: libwebms@ualberta.ca");
}

}catch(Exception $ex){
		
		$msg =  "Error occured in " . $ex->getFile() . "\n Error Message:" . $ex->getMessage();
		echo $msg;
		mail ( ADMIN_EMAIL , 'Threshold Processing Error Occured' , $msg , "from: libwebms@ualberta.ca");
		
}

        
        
