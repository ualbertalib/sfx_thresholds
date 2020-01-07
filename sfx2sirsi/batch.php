<?php

/*
 * Description of batchTest
 * 
 * @author Jeremy Hennig <jhennig@ualberta.ca>
 */
set_time_limit(0);
ini_set('display_errors',1);
error_reporting(E_ALL);




try{
include_once('config.inc.php');


$myDate = new DateTime('now');

//$fileWriter = new FileWriter();
 
//$fileWriter->write('Date File Created: ' . $myDate->format('Y-M-d H:i:s') . "\n");

$counter = 0;

if (isset($_POST['objectId'])){
$sfx_query = "SELECT distinct OBJECT_ID 
FROM thresholds
WHERE OBJECT_ID IN ( {$_POST['objectId']} )  ORDER BY OBJECT_ID;";

}else{

$sfx_query = "SELECT distinct OBJECT_ID 
FROM thresholds
ORDER BY OBJECT_ID";
}

 

$sth = $conn->prepare($sfx_query);
$sth->execute();
$result = $sth->fetchAll(PDO::FETCH_ASSOC);

echo "<b>Number of Objects: </b>" . $sth->rowCount() ."<br> <hr>";

 $sfx_query = "SELECT DISTINCT OP_ID, Threshold Threshold, LCL_Threshold , IS_FREE 
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
		
        //$result2 = mysql_query($sfx_query,$SFX_link) or die('error2:' . mysql_error()) ;
        
       // echo $sfx_query;
        
        $runonce=0;
        $IS_FREE=0;
        echo "<strong>" . $row['OBJECT_ID'] . "</strong> -- ";
         foreach ( $result2 as $row2 ){
                            if ($row2['IS_FREE']==1){
                                $IS_FREE=1;
                            }
                            if ($row2['LCL_Threshold']!=""){
                                    $mythreshold = $row2['LCL_Threshold'];
                            }else{
                                    $mythreshold = $row2['Threshold'];
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
											} catch (ArgumentCountError $e){
												echo 'Caught exception: '.$e->getMessage()."\n";
											}
										
										
									
										//if($worked==false){		
									
											//$fileWriter->write("Object ID: " . $row['OBJECT_ID'] . " " . ob_get_contents());
										//}
										
									
										
          							}else{
										
										$warning = "Warning - Method: " . $thresh[$key] . " is not defined. ". $myMethod . "Does not exist\n Error occured on object ID: " . $row['OBJECT_ID'];
										//$fileWriter->write("Object ID: " . $row['OBJECT_ID'] . " " . $warning);
										
										trigger_error($warning, E_USER_WARNING);
										
										//ob_flush();
										//ob_end_clean();
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
			
			
			
            //fwrite($myFile,$msg);
			if ($IS_FREE==1){
        echo $msg . "[IS_FREE] " . "<br>";
			}else{
			  echo $msg . "<br>";
			}
         }

}

}catch(Exception $ex){
		
		$msg =  "Error occured in " . $ex->getFile() . "\n Error Message:" . $ex->getMessage();
		echo $msg;
	//	error_log($msg,0 );		
		//mail ( ADMIN_EMAIL , EMAIL_ERROR_SUBJECT , $msg , "From: libwebms@ualberta.ca" );
		
}

        
        
?>
