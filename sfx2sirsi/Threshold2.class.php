<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Threshold2
 *
 * @author jhennig
 */
class Threshold2 {

    private $debug = false;
    //put your code here
    private $year = array(), $vol = array(), $issue = array(), $operator = array();
    private $from_date = array();
    private $to_date = array();
	//the $only_date is used when the threshold has an double equals sign: example: parsedate('==', 2011, undef, undef)
	private $only_date = array();
    private $merge = array();
    //daterange is a year/volumen and issue Id combined YYYYVVVVIIII: Example: 199500010001-199500020001  - (Y=year, V=Volume, I=issue (or embargo) )
    private $dateRange = array();
    /**
     * This helps is set based on the type of threshold that is processed. 
     * It helps keep track of how to display the threshold. It is currently not used very often.
     * Possible values are: 
     *  timediff<=  This means that only the most recent number of months/years are kept
     *  timediff=>  This means that the most recent number of months/years are NOT available.
     */
    private $thresholdType = array();

    function clear(){
        $this->OP_ID = "";
        $this->from_date = array();
        $this->merge = array();
        $this->dateRange = array();
        $this->myMsg = array();
        $this->thresholdType = array();
    }
    function setOP_ID($op_id) {
        //add an underscore to the end to force the array's that may be sorted to keep the associative array. 
        //Otherwise they will re-index the key's and the OP_ID will be lost
        $this->OP_ID = $op_id . '_';
    }
    
    function setMaxDatePart(){
       
       
        $this->parsedDate('<=',9999,9999,9999,null);
    }

    function parsedDate($op, $year, $vol, $issue, $part=null) {
        //load($op,$year,$vol,$issue,$part);
	//	echo __FUNCTION__ . " year:" . $year . " vol:" . $vol . " issue:" . $issue."<br>";
        $OP_ID = $this->OP_ID;
		$volIsDefined=true;
		$issueIsDefined=true;
		
		
		//JULY 17, 2013 - ALWAYS treat volume as UNDEF (meaning 0) to simplify the process
		//$vol = 0;
		//$issue = 0;
       
	//IF volume is undef then treat as 0
	if ($vol == 'undef') {
            $vol = 0;			
	    $volIsDefined=false;
        }
        if ($issue == 'undef') {
            $issue = 0;			
	    $issueIsDefined=false;
        }
		

        $padVol = str_pad($vol, 4, "0", STR_PAD_LEFT);
        $padIssue = str_pad($issue, 4, "0", STR_PAD_LEFT);

        if ($op == ">=") {
            
            // will execute in this scenario: $obj->parsedDate('>=','1980',21,1) && $obj->parsedDate('<=','1980',21,4) || $obj->parsedDate('>=','1992',33,1)
            /**
             * The reason why a 1_, 2_, 3_, etc. is prepended to the key is if the key already exists in the array. 
			 Remember this function is being looped so it's possible to have 2 * of the same keys
             */
				$new_OPID_key = $this->calculateArrayKey($this->from_date, $OP_ID);
				$this->from_date[$new_OPID_key]['threshold'] = $year . $padVol . $padIssue;
				$this->from_date[$new_OPID_key]['thresholdType'] = "parsedDate>=";
                $this->thresholdType[$new_OPID_key]="parsedDate>=";
			
        }
        if ($op == "<=") {
				$new_OPID_key = $this->calculateArrayKey($this->to_date, $OP_ID);
				$this->to_date[$new_OPID_key]['threshold'] = $year . $padVol . $padIssue;
				$this->to_date[$new_OPID_key]['thresholdType'] ="parsedDate<=";
				$this->thresholdType[$new_OPID_key]="parsedDate<=";
			
			 
        } 

	   if ($op == "==") {
				$new_OPID_key = $this->calculateArrayKey($this->only_date, $OP_ID);
				 $this->only_date[$new_OPID_key]['threshold'] = $year . $padVol . $padIssue;
				 $this->only_date[$new_OPID_key]['thresholdType'] = "parsedDate==";
				  $this->thresholdType[$new_OPID_key]="parsedDate==";    
				
        }

        
		//$this->showDebug($this->thresholdType, __FUNCTION__ . "() End. Variable thresholdType " . "<br>");
		$this->showDebug($this->from_date, __FUNCTION__ . "() End. Variable from_date " . "<br>");
		$this->showDebug($this->to_date, __FUNCTION__ . "() End. Variable to_date " . "<br>");
		$this->showDebug($this->only_date, __FUNCTION__ . "() End. Variable to_date " . "<br>");

            
      /*  echo "<pre>";
        echo "from Date: ";print_r($this->from_date);
        echo "--------------------";
        echo "TO Date: ";
         print_r($this->to_date);
        echo "</pre>";
        */
        return true;
    }
	
	private function calculateArrayKey($tmpArray, $desired_key){
		$prependedCount = 1;
		$newDesired_key = $desired_key;
		while(isset($tmpArray[$newDesired_key]) ){
			
			$newDesired_key = $prependedCount . '_' .$desired_key;
			$prependedCount +=1;
		}
		return $newDesired_key;
		
	}

    //This will merge the first operand (example: $obj->parsedDate('>=','1997','387','6633'))
    //with the second operand but a this point it will be a string
    //example: 199703876633
    function timespan_merge() {
        

        $this->showDebug($this->merge, __FUNCTION__ . "() <br> this->merge start of function" );
        $this->showDebug($this->from_date, __FUNCTION__ . "() <br> this->from_date is below" );
		$this->showDebug($this->to_date, __FUNCTION__ . "() <br> this->to_date is below" );
        $this->showDebug($this->only_date, __FUNCTION__ . "() <br> this->only_date" );
      
            $count = 1;
		//if (isset($this->only_date[$this->OP_ID])){
			//	$this->merge[$this->OP_ID] = $this->only_date[$this->OP_ID];

		//}else{
				foreach ($this->from_date as $key => $value) {
				  
						if ( isset($this->to_date[$key]['threshold']) && $this->to_date[$key]['threshold'] !="" ){
							$this->merge[$key] = $value['threshold'] . "-" . $this->to_date[$key]['threshold'];
						}else{
							/*  TODO: When does this execute? March 8, 2016
							echo "There is no 'to_date'"; */
							if ($this->from_date[$key]['thresholdType']=="parsedDate>="){
								@ $this->merge[$key] = $value['threshold'] . "-" . '999999999999';
								 $this->to_date[$key]['threshold']= '999999999999';
							}
						}
				}
				//Only 1 date
				foreach ($this->only_date as $key => $value) {
					
					$newKey = $this->calculateArrayKey($this->merge,$key);
					
					 $this->merge[$newKey] = $value['threshold'] . "-" . $value['threshold'];
					/*
					if (isset($this->merge[$key])){
						$this->merge[$count . "_" . $key] = $value . "-" . $value;
					}else{
						$this->merge[$key] = $value . "-" . $value;
					}
					*/
				}
			asort($this->merge);

            $this->showDebug($this->merge , "End of " . __FUNCTION__ . "().  variable this->merge is below<br>");
              
		                
    }

   
   
    //Returns a sortable string of YYYYVVVVIIII
    //where YYYY is 9999 to represent a very large year as this functionis called when there is an ongoing subscription
    //VVVV is volume. 0000 is a sufficient value as the volume wouldn't matter
    //IIII is the issue which is 9999 minus the number of embargo months (months is calculated using the $timeperiod.)
    function timediff($op, $timeperiod) {
       
	   
        $OP_ID = $this->OP_ID;

        $period = $this->getPeriodNumber($timeperiod);
        $unit = $this->getTimeUnit($timeperiod);

        
        //Embargo : Most Recent x years not available -> $obj->timeDiff('<=',2006,2003)
        if ($op == ">=" || $op == ">") {
            if ($unit == 'year'){
                $timeDiffYear = (date("Y") - $period);
            }else{
                $timeDiffYear = 9999;
            }
			
		


            $msg = "";
             $this->myMsg[$OP_ID] = " Most recent " . $period . " " . $unit . "(s) not available";

                if ($unit == 'year') {
                    //multiply the period by 12 if the period is in years.
                    $embargo = ($period * 12);
                } else {
                    $embargo = $period;
                }
            $issue = (9999 - $embargo);
            $vol = '0000';
            //$this->to_date[$OP_ID] = '9999' . $vol . $issue;            
            $this->to_date[$OP_ID]['threshold'] = $timeDiffYear . $vol . $issue;            
            
			//TODO: replace thresholdType
            $this->thresholdType[$OP_ID] = "timediff>=";
            
        }

        //"<=" - this means We only have the most recent $period months/years. 
        //Example: Only have the most recent 2 months -- $obj->timediff('<=','2m') 
        if ($op == "<=" || $op == "<") {
            $currentDate = strtotime('now');
            $date = strtotime("-{$period} {$unit}", $currentDate);            
            $embargoYear = date('Y', $date);            
            $this->thresholdType[$OP_ID] = "timediff<=";
            $this->from_date[$OP_ID]['threshold'] = $embargoYear . '0000' .'0000';
            //$this->to_date[$OP_ID] = '9999' . '9999' .'9999';
            $this->to_date[$OP_ID]['threshold'] = '9999' . '9999' .'9999';
            $this->myMsg[$OP_ID] = $msg = "Most recent " . $period . " " . $unit . "s only";
        }
       
	   $this->showDebug( $this->myMsg , __FUNCTION__ . "() - End. <br> variable: this->myMsg. " );
	   
        return true;
       
    }

    /**
     *
     * @param type $period example '6y' meaning an embargo time period of 6 years.
     * @return type 
     */
    function getPeriodNumber($period) {

        //gets the number in the time period example '6y' returns 6
        preg_match_all('!\d+!', $period, $matches);                
        //if there values in the array then most likely the $period is something like 1y6m in which case calculate the number of months 1y6m=18 months
        if (count($matches[0])==2 ){           
            $var= (($matches[0][0]*12) + $matches[0][1]);
        }else{
            $var = implode(' ', $matches[0]);
        }
       
        return $var;
    }

    /**
     *
     * @param type $period - returns "year"  or "month"
     * @return string 
     */
    function getTimeUnit($period) {
        preg_match_all('![^\d]+!', $period, $matches);
    
        $p = implode(' ', $matches[0]);
		$var = "";
        if ($p == 'y') {
            $var = 'year';
        } elseif ($p == 'm') {
            $var = 'month';
        }
        //if this happens then return months as 1y6m will be 18 months see the $this->getPeriodNumber() function
        if ($var == "" && $matches[0][0]=='y' && $matches[0][1]=='m'){
            $var='month';
        }
        return $var;
    }

    function removeOverlap() {
		$runonce=0;
        $extraLooping =1;
        $i=0;
		$overLapDebug = "";
    
			//first deduplicate any date/volume ranges
			$this->dateRange = array_unique($this->merge);

            $this->showDebug($this->dateRange, '<hr>' . __FUNCTION__ . "() - Begin, Variable dateRange:<br>");
          
			
			//if the only_date is set then that means the == was used in the parsedDate function therefore you don't need to do this loop
			//if (! isset($this->only_date[$this->OP_ID])  ){
                            
			//$this->merge is in the format (YYYYVVVVIIII) example: 199500010001-199500020001  - (Y=year, V=Volume, I=issue (or embargo) )
			//so explode it into 2 parts seperated by a dash so it's easier to work with				
					foreach ($this->dateRange as $key => $value) {
							$datePart = explode('-', $this->dateRange[$key]);
							$dateFrom[$key] = $datePart[0];
							//$dateTo[$key] = $datePart[1];
							if (! isset($datePart[1])){
								$dateTo[$key]="";
							}else{
								$dateTo[$key] = $datePart[1];
							}
					}
					
					while($i<$extraLooping){
						reset($this->dateRange);
						$previousKey = "";
						//echo "Extra " . $extraLooping."<br>";
						$mycount=0;
                                               // echo "================<br>";
                                                // echo "<pre>";
                                               // print_r($this->dateRange);
                                               // echo "</pre>";
						foreach ($this->dateRange as $key => $value) {
							$mycount += 1;
							//echo $mycount.") Prev-".$previousKey. '--'. $dateFrom[$previousKey] . ' - ' . $dateTo[$previousKey]  . "| CurKey-" . $key .'--' .$dateFrom[$key] . ' - '. $dateTo[$key] . '<br>';
							//$datePart[1] is the part after the dash example: 199500010001-199500020001 datePart[1] would be: 199500020001
							if (is_null($dateTo[$key]) || $dateTo[$key] == "" || substr($dateTo[$key],0,4) == "9999") {
								 // if there is no ending date in the first span then just say "available starting with 199500010001" for example
								 // $avail.= $datePart[0];
								 // if this "IF" statement executes there is no need to continue to loop as we already know what the oldest issue and subscription is ongoing
								 // therefore call the arrayDeleteAfterKey function to delete everything after the current key
								 // $this->arrayDeleteAfterKey($key);   
								 
								 //  break;                
							}

							/*  echo  "<br>previous DATE :" . @ $dateFrom[$previousKey]. "-" . @ $dateTo[$previousKey];
							//  echo "<br>current DATE:" . $dateFrom[$key] . "-" . $dateTo[$key]; */
							
							if (@ ($dateFrom[$key] > $dateFrom[$previousKey] && $dateFrom[$key] < $dateTo[$previousKey]) &&
									($dateTo[$key] <= $dateTo[$previousKey])   ) {               
									/** 
										Updated on Feb 29, 2016 to fix problem with Object ID: 954922649138
									Executed when:
										[1000000000670098_] => 199700000000-999900009993
										[1000000001031975_] => 200800000000-201300000000
										and becomes:
										[1000000000670098_] => 199700000000-999900009993
									*/
									
									$this->dateRange[$previousKey] = $dateFrom[$previousKey] . '-' . $dateTo[$previousKey];
									$dateFrom[$previousKey]=$dateFrom[$previousKey];
									$dateTo[$previousKey]=$dateTo[$previousKey];
									
									unset($this->dateRange[$key]);
									unset($dateFrom[$key]);
									unset($dateTo[$key]);
									
									
									
									$overLapDebug .= "Scenario 'A' executed. Line:" . __LINE__ . ", Loop #" . $mycount . "<br>";
								
							} elseif(@ ($dateFrom[$key] == $dateFrom[$previousKey] ) &&
									($dateTo[$key] > $dateTo[$previousKey])  ){
									/* This will exec on the following scenario:
									 *  196900010001-199600280012
									 *  196900010001-999900009981
									 */
									// echo "unset previous key";
								//	echo "Q".$previousKey. ' '. $dateFrom[$previousKey] . '-' . $dateTo[$previousKey]  . "|<strong>" . $key .'</strong>' .$dateFrom[$key] . '-'. $dateTo[$key] . '<br>';									  
									 unset($this->dateRange[$previousKey]);
									 unset($dateFrom[$previousKey]);
									 unset($dateTo[$previousKey]);
									$overLapDebug .= "Scenario 'B' executed. Line:" . __LINE__ . "<br>";
							} elseif(@ $dateFrom[$key] >= @$dateFrom[$previousKey] && 
										@$dateFrom[$key]<=@$dateTo[$previousKey] && 
											@$dateTo[$key] > @$dateTo[$previousKey]){
											/* This will exec on the following scenario:
											 *  196900010001-199600280012
											 *  197500010001-999900009981
											 */
									//echo "B".$previousKey. ' '. $dateFrom[$previousKey] . '-' . $dateTo[$previousKey]  . "|<strong>" . $key .'</strong>' .$dateFrom[$key] . '-'. $dateTo[$key] . '<br>';
										$this->dateRange[$key] = $dateFrom[$previousKey] . '-' .$dateTo[$key];
										$dateFrom[$key] = $dateFrom[$previousKey];
										//remove the overlapped daterange
										unset($this->dateRange[$previousKey]);
										unset($dateFrom[$previousKey]);
										unset($dateTo[$previousKey]);
									$overLapDebug .= "Scenario 'C' executed. Line:" . __LINE__ . "<br>";
										
							}elseif( @ $dateFrom[$key] >= @$dateFrom[$previousKey] &&  
									 @ ( (substr($dateTo[$previousKey],0,4)+1)==substr($dateFrom[$key],0,4) ||
                                                                                substr($dateTo[$previousKey],0,4)==substr($dateFrom[$key],0,4) ) && 
                                                                        @$dateTo[$key] > @$dateTo[$previousKey]){
										/* If there is only 1 year apart between the $dateTo[$previousKey] and $dateFrom[$key] merge the 2 records
										 *  NOTE: The addition of 110000000 in the above if statement would represent 1 year (plus a greater volume).
										 *	This will exec on the following scenario
										 *	*  196500010001-196900000000
											*  197000000000-999900009981
										*/
										//echo "C".$previousKey. ' '. $dateFrom[$previousKey] . '-' . $dateTo[$previousKey]  . "|<strong>" . $key .'</strong>' .$dateFrom[$key] . '-'. $dateTo[$key] . '<br>';
									 	//$this->dateRange[$previousKey] = $dateFrom[$previousKey] . '-' .$dateTo[$key];
										$this->dateRange[$key] = $dateFrom[$previousKey] . '-' .$dateTo[$key];
										$dateFrom[$key] = $dateFrom[$previousKey];
										
										//remove the overlapped daterange
										unset($this->dateRange[$previousKey]);
										unset($dateFrom[$previousKey]);
										unset($dateTo[$previousKey]);	
										$overLapDebug .= "Scenario 'D' executed. Line:" . __LINE__ . "<br>";
							}elseif( @ $dateFrom[$key] >= @$dateFrom[$previousKey] &&  $dateTo[$key]=="" &&  
                                                                    ( @ substr($dateFrom[$key],0,4) == @substr($dateTo[$previousKey],0,4) ||
                                                                            (@ substr($dateFrom[$key],0,4)-1) == @substr($dateTo[$previousKey],0,4) 
                                                                        ) ){
                                                                   /**
                                                                    *   This executes when there is no DateTo field and when dateFrom[key]==$dateTo[$previousKey]  example:
                                                                    * Array(     [3360000000007553_] => 199400010001-199600030001
                                                                                 [3370000000005205_] => 199700030002
                                                                            )
                                                                    *  
                                                                    */
                                                            
                                                                   $this->dateRange[$key] = $dateFrom[$previousKey] . '-' .$dateFrom[$key];
                                                                   unset($this->dateRange[$previousKey]);
									unset($dateFrom[$previousKey]);
									unset($dateTo[$previousKey]);	
									$overLapDebug .= "Scenario 'E' executed. Line:" . __LINE__ . "<br>";								   
                                                                   
                           } /**
                            * Added January 22, 2015 because of problem with: 991042749771672
                            */
                            elseif( substr($dateFrom[$key],0,4) ==  substr($dateTo[$key],0,4)  &&   isset($dateTo[$previousKey]) &&
                                        substr($dateTo[$previousKey],0,4) == substr($dateTo[$key],0,4) ){
                                    /**
                                     * Executes when:  [1000000000055608_] => 200200000000-200800000000
                                     *                   [1000000001847486_] => 200800000000-200800000000
                                     */
                                                                    
                                     $this->dateRange[$key] = $dateFrom[$previousKey] . '-' . $dateFrom[$key];
                                unset($this->dateRange[$previousKey]);
                                unset($dateTo[$previousKey]);
                                unset($dateFrom[$previousKey]);
								$overLapDebug .= "Scenario 'F' executed. Line:" . __LINE__ . "<br>";

                            }

							//the previous key is used to compare the previous record in the array to see if the current record falls in the date/volume range of the previous record
							$previousKey = $key;
							//echo "<br>Previous Key" . $previousKey;
						}
						 if ($runonce==0){
								$i=$i-count($this->dateRange);
							   // echo $extraLooping . "Extra Looping set<br>";
								$runonce=1;
						 }
						 $i=$i+1;
					}
          // }else{
		   
		//   }
      
        $this->showDebug($this->dateRange, __function__ . "() - End. Variable: dateRange." . "<br>" .$overLapDebug );
        
    }
    
    //Delete all the values that come after the key
    function arrayDeleteAfterKey($mykey){
        //advance the internal pointed until it reaches the key
        reset($this->dateRange);         
       // echo "<br>KEY:" . $mykey;
        $count = 0;
        $size=count($this->dateRange);
        while (key($this->dateRange) !== $mykey){
            next($this->dateRange);
            if ($size==$count){
                break;
            }
            $count++;
        }
        next($this->dateRange);
        //delete everything after the $key as the internal pointer should be pointing at it.
        //cannot use a FOREACH statement as foreach will reset the internal pointer
       while (list($key, $value) = each($this->dateRange)) {
           $datePart = explode('-', $this->dateRange[$key]);
            if ( trim($datePart[1])!="" ){
                //echo "<br>deleting-".$this->dateRange[$key]."<br>";
                unset($this->dateRange[$key]);
            }
            
        }
    }
    
    
    function translateDateRange(){
       
        $this->showDebug($this->dateRange, "<hr>" . __FUNCTION__ . '() dateRange ' );
        $this->showDebug($this->thresholdType, "<hr>" . __FUNCTION__ . '() thresholdType' );
		$this->showDebug(@$this->myMsg, __function__ . "(). Start. Variable myMsg" );
		$this->showDebug(@$this->from_date, __function__ . "(). Start. Variable from_date" );
		$this->showDebug(@$this->to_date, __function__ . "(). Start. Variable to_date" );
		$this->showDebug(@$this->only_date, __function__ . "(). Start. Variable only_date" );
       
       
        $msg ="";
		//if $this->only_date[$this->OP_ID] is set then no need to do the loop 
		//if (!isset($this->only_date[$this->OP_ID])){
			
			// if statement Added on 2019-08-16
			// if this statement is executed then we should be dealing with a situation where the output will be "Most recent x year(s) not available"  where x is an number.
			if(count($this->dateRange) == 0 && count($this->from_date)==0 &&  (isset($this->myMsg) && is_array($this->myMsg)) ){
				
				// get the minimum statement in the myMsg array, for example:
				// Most recent 2 year(s) not available, Most recent 4 year(s) not available
				// We would want 'Most recent 2 year(s) not available' to be displayed since it indicates a greater availablility of articles.
				$msg = min($this->myMsg);
			
				
			}
			
        
		 
            foreach($this->dateRange as $key=>$value){
			   // $msg .= "Available starting with: ";
                            
				  $datePart = explode('-', $this->dateRange[$key]);
				  $dateFrom[$key] = $datePart[0];

                                  if (isset($datePart[1])){
				                        $dateTo[$key] = $datePart[1];
                                  }else{
                                        $dateTo[$key]="";										
                                  }
				  
				  $fromVol = $this->getVolume($dateFrom[$key]);
				  $fromIssue = $this->getIssue($dateFrom[$key]);
                                  
                                  
                                  //if the dateTo and date From are the same (1964 - 1964) then just have 1 date in the message
                                  if (  $dateFrom[$key]!="" && $dateFrom[$key]==$dateTo[$key] ){
									  
                                      $msg.= "(" . $this->getYear($dateFrom[$key])  . ")";
                                      continue;
                                  }
                                  
				  if ($dateFrom[$key]!=""){
					  //$msg.= $this->getYear($dateFrom[$key]);
					  
        					  if($fromVol!=""){ 
        						// $msg.= "v." . $fromVol ;
        					  }
        					  if($fromIssue!=""){ 
        						  //issue number is not needed anymore
        						//$msg.= " issue: " . $fromIssue ;
        					  }
        					   if($fromVol!="" || $fromIssue!=""){ 
        						   //end the parenthesis
        							//$msg.= ")";
        					   } 
                                           
                               //if the current usage is a timediff<= that is taken care of in the timediff function and 
                               //displayed later through myMsg[$key]  
                                        
                             if ( count($this->merge)!=1 && @$this->thresholdType[$key] == "timediff<=" ){  
                                $msg.= "(" . $this->getYear($dateFrom[$key]) . ")";
                             }elseif( @$this->thresholdType[$key] != "timediff<="){
                                 $msg.= "(" . $this->getYear($dateFrom[$key]) . ")";
                             }
                                         
				  }
				  //echo $dateTo[$key] . "<br>";
				 // echo $dateFrom[$key] . "<br>";
				
				  
				  if ($dateTo[$key]!=""){
					  //$msg.= " and ending with ";
					 // echo "dateTo: ". $dateTo[$key]."<br>";
					  $toYear = $this->getYear($dateTo[$key]);
					  $toVol=$this->getVolume($dateTo[$key]);
					  $toIssue=$this->getIssue($dateTo[$key]);
					  // There is no ending if the $toYear == 9999
						if ($toYear == 9999){

							  $this->showDebug($this->thresholdType, "<hr>" . 'thresholdType' );
							  
							$translatedEmbargoMonths = $this->translateEmbargo($toIssue);
                                                        
                                            if (isset($this->myMsg[$key]) &&  count($this->merge)==1){
												if($msg != ""){
													//Dec 16, 2016: replace (2003) with (2003-) example: 110978984081479
													//$msg = str_replace(")","-)", $msg);
													$msg = $msg . "-";
												}
												$msg.= $this->myMsg[$key];
												 
											

                                            }else{
                                                if ($translatedEmbargoMonths!=0){       							 
												
													//adds the dash example id: 954927539088  (1951)- Most recent 18 month(s) not available 
													if( substr($dateTo[$key],0,4)=='9999' ){
															$msg.= '-';
													}
                                                     $msg.=  $this->myMsg[$key];
                                                 
                                                }else{
                                                        $msg.= "-";													
                                                }
                                            }
                                                         
						}else{
						

							 //$msg.= " and ending with: " . $toYear;
							$msg.= " - "  ;
							  if($toVol!=""){ 
							//	$msg.= "v. " . $toVol ;
							  }
							  if($toIssue!=""){ 
								//$msg.= " issue: " . $toIssue ;
							  }
							   if($toVol!="" || $toIssue!=""){ 
								   //end the parenthesis
									$msg.= "";
							   } 
                                                          
							   //Embargo
							   //TODO: Not sure if the isset is proper
							   if (isset($this->thresholdType[$key]) && $this->thresholdType[$key]=='timediff>='){
								   $msg.= $this->myMsg[$key];
							   }else{
									
									if($dateTo[$key] == $dateFrom[$key]){
																		
										//echo $msg . "<br>";
										//$this->pre($dateTo[$key]);
										//$this->pre($dateFrom[$key]);
										$msg = $this->str_replace_first('-','',$msg);
										
										
									}else{
										$msg.= "(".$toYear.")";
									}
							   }
							  
							  
							 
							  
							   
						}                  
					  /*
						  if ($toVol=="" && $toVol=="0000"){
							  //if the issue is greater then 9900 then most likely an embargo was used (example: timediff(>=,'2y'))
							  if ($toIssue>'9900'){
								$msg.= "Most recent " . translateEmbargo($toIssue) . " not available";
							  }   
						  }  */ 
				  }
				  $msg .=" \n";
			}
       //}else{
                    
                   /*	//Code that was used for when there is a == as the operator. example display: "1990 Only"
                       
                    if (isset($this->only_date[$this->OP_ID]) ){
                       
			$onlyYear = $this->getYear($this->only_date[$this->OP_ID]);
			$msg .= $onlyYear . " only";
                    }
                    */
			
	  // } 
        return $msg;
    }
    
    /**
     *  Returns the year from a datePart a datePart is in the format of YYYYVVVVIIII where YYYY is the year VVVV is the volume and IIII is the issue or embargo
     * 
     * @param type $datePart
     * @return type 
     */
    function getYear($datePart){
       // echo "datepart-" . $datePart . "<br>";
        return substr($datePart,0,4);        
    }
    
    /**
     *  Returns the Volume (V) section of the DatePart (YYYYVVVVIIII) it also removes any leading zeros
     * @param type $datePart
     * @return type 
     */
    function getVolume($datePart){
        return ltrim(substr($datePart,4,4),0);        
    }

     /**
     *  Returns the Issue (I) section of the DatePart (YYYYVVVVIIII) it also removes any leading zeros
     * @param type $datePart
     * @return type 
     */
    function getIssue($datePart){
        return ltrim(substr($datePart,8,4),0);        
    }
    
    /**
     * Will return the embargo time when given a issueis passed. 
     * @param type $issue
     * @return string 
     */
    function translateEmbargo($issue){
        $embargo = 9999-$issue;
        if ($embargo <12){
            $embargoTime = $embargo . " months";
        }
        else{
            $years = $embargo/12;
            $embargoTime = $years . " year(s)";
        }
        return $embargoTime;
    }
    
    function tiffdiff(){
        return true;
    }
   

    function showDebug($data, $before="",$after=""){
        
        if($this->debug == true){
           
            echo $before; 
            $this->pre($data);
            echo $after;
        }
    }
	
	function setDebug($bool=false){
		
		if($bool=="false"){
			$this->debug = false;
		}else{
			$this->debug = $bool;
		}
		
	}
	
	private function str_replace_first($from, $to, $subject){
		$from = '/'.preg_quote($from, '/').'/';

		return preg_replace($from, $to, $subject, 1);
	}
	
   function pre($s){
    echo "<pre>";
    print_r($s);
    echo "</pre>";
   }

}

?>
