<?php
/*
 * Created on Jun 8, 2010
 *
 * Copyright 2010 The President and Fellows of Harvard College
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * @Author: Paul J. Morris  bdim@oeb.harvard.edu
 * 
 */
$debug=false;

include_once('connection_library.php');
include_once('specify_library.php');

if ($debug) { 
	mysqli_report(MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_STRICT);
} else { 
	mysqli_report(MYSQLI_REPORT_OFF);
}

$connection = specify_connect();
$errormessage = "";

$mode = "menu";
 
if ($_GET['mode']!="")  {
	if ($_GET['mode']=="show_table_locks") {
		$mode = "show_table_locks"; 
	}
	if ($_GET['mode']=="force_unlock") {
		$mode = "force_unlock"; 
	}
	if ($_GET['mode']=="unlinked_collectionobjects") {
		$mode = "unlinked_collectionobjects"; 
	}
	if ($_GET['mode']=="unlinked_preparations") {
		$mode = "unlinked_preparations"; 
	}
	if ($_GET['mode']=="unlinked_items") {
		$mode = "unlinked_items"; 
	}
	if ($_GET['mode']=="collectionobjects_without_barcodes") {
		$mode = "collectionobjects_without_barcodes"; 
	}
	if ($_GET['mode']=="collectingevents_without_locality") {
		$mode = "collectingevents_without_locality"; 
	}
	if ($_GET['mode']=="list_entry_for_collectingevents_without_locality") {
		$mode = "list_entry_for_collectingevents_without_locality"; 
	}
	if ($_GET['mode']=="agent_ages") {
		$mode = "agent_ages"; 
	}
	if ($_GET['mode']=="individual_agent_ages") {
		$mode = "individual_agent_ages"; 
	}
	if ($_GET['mode']=="team_agent_ages") {
		$mode = "team_agent_ages"; 
	}
	if ($_GET['mode']=="collection_when_not_alive") {
		$mode = "collection_when_not_alive"; 
	}
	if ($_GET['mode']=="weekly_rate_creation") {
		$mode = "weekly_rate_creation"; 
	}
	if ($_GET['mode']=="weekly_rate_modification") {
		$mode = "weekly_rate_modification"; 
	}
	if ($_GET['mode']=="person_week_records") {
		$mode = "person_week_records"; 
	}
	if ($_GET['mode']=="loan_null_role") {
		$mode = "loan_null_role"; 
	}
	if ($_GET['mode']=="NEVP") {
		$mode = "nevp"; 
	}
	if ($_GET['mode']=="items_with_no_preparation") {
		$mode = "items_with_no_preparation"; 
	}
} 
	
echo pageheader('qc'); 

// Only display if internal 
if (preg_match("/^140\.247\.98\./",$_SERVER['REMOTE_ADDR']) || 
    preg_match("/^10\.1\.147\./",$_SERVER['REMOTE_ADDR']) || 
    preg_match("/^140\.247\.98\./",$_SERVER['HTTP_X_FORWARDED_FOR']) ||
    preg_match("/^10\.1\.147\./",$_SERVER['HTTP_X_FORWARDED_FOR']) ||
    $_SERVER['REMOTE_ADDR']=='127.0.0.1') { 
						
	if ($connection) {
		if ($debug) {  echo "[$mode]"; } 
		
		switch ($mode) {
			case "force_unlock":
		        echo force_unlock();
		        break;
		    case "show_table_locks":
		        echo show_table_locks();
		        break;
			case "unlinked_collectionobjects":	
				echo unlinked_collectionobjects();
				break;
			case "unlinked_items":	
				echo unlinked_items();
				break;
			case "unlinked_preparations":	
				echo unlinked_preparations();
				break;
			case "collectionobjects_without_barcodes":	
				echo collectionobjects_without_barcodes();
				break;
			case "list_entry_for_collectingevents_without_locality":	
				echo list_entry_for_collectingevents_without_locality();
				break;
			case "collectingevents_without_locality":	
			    $agentid = preg_replace("/[^0-9]/","",$_GET['agentid']);
				echo collectingevents_without_locality($agentid);
				break;
			case "agent_ages":
				echo agent_ages();
				break;
			case "individual_agent_ages":
				echo agent_ages(1);
				break;
			case "team_agent_ages":
				echo agent_ages(3);
				break;
			case "collection_when_not_alive":	
				echo collection_out_of_date_range();
				break;
			case "weekly_rate_creation":	
				echo weekly_rate('created');
				break;
			case "weekly_rate_modification":	
				echo weekly_rate('modified');
				break;
			case "loan_null_role":	
				echo loan_null_role();
				break;
			case "items_with_no_preparation":	
				echo items_with_no_preparation();
				break;
			case "nevp":	
                                echo "List of <a href='image_search.php?mode=qc&batch=urn:uuid'>NEVP Batches</a>.<BR>";
                                echo "List of <a href='botanist_search.php?remarks=Created+from+NEVP+Ingest'>Botanists created by NEVP Ingests</a>.<BR>";
			        $batch = preg_replace("/[^A-Za-z\-0-9]/","",$_GET['batch']);
				echo nevp_records_without_images($batch);
				break;
			case "person_week_records":
                                $person = 'Lewis-Gentry';
                                $year = "2010";
                                $week = "33";
			        $person = preg_replace("/[^A-Za-z\-0-9]/","",$_GET['person']);
			        $year = preg_replace("/[^0-9]/","",$_GET['year']);
			        $week = preg_replace("/[^0-9]/","",$_GET['week']);
				echo person_week_records($person,$year,$week);
				break;
			case "menu": 	
			default:
				echo menu(); 
		}
		
		$connection->close();
		
	} else { 
		$errormessage .= "Unable to connect to database. ";
	}
	
	if ($errormessage!="") {
		echo "<strong>Error: $errormessage</strong>";
	}
	
	
    echo "<h3><a href='qc.php'>Quality Control Tests</a></h3>";						
	
} else {
	echo "<h2>QC pages are available only within HUH</h2>"; 
}

echo pagefooter();

// ******* main code block ends here, supporting functions follow. *****

function menu() { 
   $returnvalue = "";

   $returnvalue .= "<div>";
   $returnvalue .= "<h2>NEVP Project</h2>";
   $returnvalue .= "<ul>";
   $returnvalue .= "<li><a href='qc.php?mode=NEVP'>NEVP QC Reports</a></li>";
   $returnvalue .= "</ul>";
   $returnvalue .= "<h2>Find anomalous values for Collection Objects</h2>";
   $returnvalue .= "<ul>";
   $returnvalue .= "<li><a href='qc.php?mode=unlinked_items'>Items without collection objects (likely to cause loans to fail to print)</a></li>";
   $returnvalue .= "<li><a href='qc.php?mode=unlinked_collectionobjects'>Collection objects without Items</a></li>";
   $returnvalue .= "<li><a href='qc.php?mode=items_with_no_preparation'>Items without Preparations</a></li>";
   $returnvalue .= "<li><a href='qc.php?mode=unlinked_preparations'>Preparations without Items</a></li>";
   $returnvalue .= "<li><a href='qc.php?mode=collectionobjects_without_barcodes'>Collection objects without barcodes</a></li>";
   $returnvalue .= "<li><a href='qc.php?mode=list_entry_for_collectingevents_without_locality'>Collecting Events without Localities</a></li>";
   $returnvalue .= "</ul>";
   $returnvalue .= "</ul>";
   $returnvalue .= "</ul>";
   $returnvalue .= "<h2>Find anomalous values for Agents/Botanists</h2>";
   $returnvalue .= "<ul>";
   $returnvalue .= "<li><a href='qc.php?mode=agent_ages'>Agents ages</a></li>";
   $returnvalue .= "<li><a href='qc.php?mode=individual_agent_ages'>Individual Agent ages</a></li>";
   $returnvalue .= "<li><a href='qc.php?mode=team_agent_ages'>Team Agent ages</a></li>";
   $returnvalue .= "<li><a href='qc.php?mode=collection_when_not_alive'>Collections before birth/after death</a></li>";
   $returnvalue .= "</ul>";
   $returnvalue .= "<h2>Progress</h2>";
   $returnvalue .= "<ul>";
   $returnvalue .= "<li><a href='qc.php?mode=weekly_rate_creation'>Collection object records created per week</a></li>";
   $returnvalue .= "<li><a href='qc.php?mode=weekly_rate_modification'>Collection object records last modified per week</a></li>";
   $returnvalue .= "</ul>";
   $returnvalue .= "<h2>Transactions</h2>";
   $returnvalue .= "<ul>";
   $returnvalue .= "<li><a href='qc.php?mode=loan_null_role'>Loans where the recipient role is null</a></li>";
   $returnvalue .= "</ul>";
   $returnvalue .= "<h2>Locks</h2>";
   $returnvalue .= "<ul>";
   $returnvalue .= "<li><a href='qc.php?mode=show_table_locks'>Show which tables are locked</a></li>";
   $returnvalue .= "</ul>";
   $returnvalue .= "</div>";

   return $returnvalue;
}


function loan_null_role() { 
   global $connection;
$debug = TRUE;
   $returnvalue = "";
   $sql = "select year(loandate), loannumber from loan where text3 is null or text3 = '' order by year(loandate) desc;";
   if ($debug) { echo "[$sql]<BR>"; } 
   $stmt = $connection->stmt_init();
   $stmt->prepare($sql);
   $stmt->execute();
   $stmt->bind_result($loanyear, $loannumber);
   $returnvalue .= "<table>";
   $returnvalue .= "<tr><th>Year of Loan</th></th><th>Loan Number</th></tr>";
   while ($stmt->fetch()) {
       $returnvalue .= "<tr><td>$loanyear</td><td>$loannumber</td></tr>";
   }
   $stmt->close();
   $returnvalue .= "</table>";
   return $returnvalue;
} 

function collection_out_of_date_range() { 
	global $connection;
   $returnvalue = "";
   $query = "select count(collectionobjectid), agent.lastname, agent.agentid, agentvariant.name " .
   		" from agent left join collector on agent.agentid = collector.agentid " .
                " left join agentvariant on agent.agentid = agentvariant.agentid " .
   		" left join collectingevent on collector.collectingeventid = collectingevent.collectingeventid " .
   		" left join collectionobject on collectingevent.collectingeventid = collectionobject.collectingeventid " .
   		" where dateofbirth is not null " .
   		" and (startdate < dateofbirth or enddate > dateofdeath) " .
                " and agentvariant.vartype = 4 " . 
                " group by agent.lastname, agent.agentid, agentvariant.name " .
   		" order by count(collectionobjectid) desc  " . 
                " limit 10 ";
	if ($debug) { echo "[$query]<BR>"; } 
    $returnvalue .= "<h2>Most frequent collectors for collecting event dates outside of the birth/death, flourished, collected, or received dates for a collector.</h2>";
	$statement = $connection->prepare($query);
	if ($statement) {
		$statement->execute();
		$statement->bind_result($count,$name,$agentid,$collname);
		$statement->store_result();
	    $returnvalue .= "<table>";
	    $returnvalue .= "<tr><th>Number of Anomolies</th></th><th>Collector</th></tr>";
		while ($statement->fetch()) {
	          $returnvalue .= "<tr><td>$count</td><td>$name <a href='botanist_search.php?id=$agentid'>$collname<a></td></tr>";
		}
	    $returnvalue .= "</table>";
	}
    
   $query = "select collectionobjectid, agent.lastname, agent.agentid, dateofbirth, startdate, dateofdeath, agent.datestype as datetype, agentvariant.name " .
   		" from agent left join collector on agent.agentid = collector.agentid " .
                " left join agentvariant on agent.agentid = agentvariant.agentid " .
   		" left join collectingevent on collector.collectingeventid = collectingevent.collectingeventid " .
   		" left join collectionobject on collectingevent.collectingeventid = collectionobject.collectingeventid " .
   		" where dateofbirth is not null " .
   		" and (startdate < dateofbirth or enddate > dateofdeath) " .
                " and agentvariant.vartype = 4 " . 
   		" order by agent.initials, agent.lastname, startdate "; 
	if ($debug) { echo "[$query]<BR>"; } 
    $returnvalue .= "<h2>Cases where collecting event dates are outside of the birth/death, flourished, collected, or received dates for a collector.</h2>";
	$statement = $connection->prepare($query);
	if ($statement) {
		$statement->execute();
		$statement->bind_result($collectionobjectid,$name,$agentid, $dob, $collectiondate, $dod, $datetype,$collname);
		$statement->store_result();
        $returnvalue .= "<h2>There are ". $statement->num_rows() . " anomalous collecting events</h2>";
	    $returnvalue .= "<table>";
	    $returnvalue .= "<tr><th>Begin Date</th><th>Collecting Event</th><th>End Date</th><th>Type</th><th>Collector</th></tr>";
		while ($statement->fetch()) {
                   switch ($datetype) {  
                      case 0: 
                         $datetypetext = "Birth/Death";
                         break;
                      case 1: 
                         $datetypetext = "Flourished";
                         break;
                      case 2: 
                         $datetypetext = "First/Last Collection";
                         break;
                      case 3: 
                         $datetypetext = "First/Last Recieved";
                         break;
                  }
	          $returnvalue .= "<tr><td>$dob</td><td><a href='specimen_search.php?mode=details&id=$collectionobjectid'>$collectiondate</a></td><td>$dod</td><td>$datetypetext</td><td>$name <a href='botanist_search.php?id=$agentid'>$collname<a></td></tr>";
		}
	    $returnvalue .= "</table>";
	}

   return $returnvalue;
}

function agent_ages($type="all") {
	global $connection;
	$returnvalue = "";
	
	$agenttype = "All";
	if ($type==3) {
		$agenttype = "Group"; 
	}
	if ($type==1) {
		$agenttype = "Individual"; 
	}
	
	$returnvalue .= "<h2>Distribution of $agenttype agents by difference between first known and last known date.</h2>";
	$query = "select count(*), year(dateofdeath)-year(dateofbirth) from agent group by year(dateofdeath)-year(dateofbirth)";
        $anomolytype = "Class contains fewer than 30 agents.";
	if ($type==3) { 
		$query = "select count(*), year(dateofdeath)-year(dateofbirth) from agent where agenttype = 3 group by year(dateofdeath)-year(dateofbirth)"; 
                $anomolytype = "Teams lasting less than zero or more than 50 years";
	}
	if ($type==1) { 
		$query = "select count(*), year(dateofdeath)-year(dateofbirth) from agent where agenttype = 1 group by year(dateofdeath)-year(dateofbirth)";
                $anomolytype = "Individuals with lifetimes less than 20 or more than 100 years.";
	}
	if ($debug) { echo "[$query]<BR>"; } 
	$returnvalue .= "<table>";
	$returnvalue .= "<tr><th>Age</th><th>Number of agents</th><th>Anomalous Agents: $anomolytype</th></tr>";
	$statement = $connection->prepare($query);
	if ($statement) {
		$statement->execute();
		$statement->bind_result($count,$age);
		$statement->store_result();
		while ($statement->fetch()) {
			$agents = "";
			if (($type=="all" && $count<30) || ($type==1 && ($age < 20 || $age > 100))  || ($type==3 &&( $age <1 || $age > 50))) { 
				$query = "select lastname, agentid from agent where year(dateofdeath)-year(dateofbirth) = ? ";
	                        if ($type==3) {
				    $query = "select lastname, agentid from agent where year(dateofdeath)-year(dateofbirth) = ? and agenttype = 3 ";
	                        } 
	                        if ($type==1) {
		                   $query = "select lastname, agentid from agent where year(dateofdeath)-year(dateofbirth) = ? and agenttype = 1 and ( datestype = 0 or ( year(dateofdeath)-year(dateofbirth) <= 0 ) )  ";
   	                        }
				if ($debug) { echo "[$query]<BR>"; } 
				$statement_geo = $connection->prepare($query);
				if ($statement_geo) {
					$statement_geo->bind_param("i",$age);
					$statement_geo->execute();
					$statement_geo->bind_result($agentname,$agentid);
					$statement_geo->store_result();
					$separator = "";
					while ($statement_geo->fetch()) { 
                                                $varname = "";
                                                $sql = "select name from agentvariant where agentid = ? order by vartype desc  limit 1 ";
                                                $stmt = $connection->prepare($sql);
                                                if ($stmt) { 
                                                   $stmt->bind_param('i',$agentid);
                                                   $stmt->execute();
                                                   $stmt->bind_result($varname);
                                                   $stmt->store_result();
                                                   $stmt->fetch();
                                                }
                                                $stmt->close();
						$agents .= "$separator<a href='botanist_search.php?mode=details&id=$agentid'>$agentname $varname</a>";
						$separator = "; ";
					}
				}
			}
			$returnvalue .= "<tr><td>$age</td><td>$count</td><td>$agents</td></tr>";
		}
	}
	$returnvalue .= "</table>";

    return $returnvalue;
}
 
function unlinked_collectionobjects() { 
	global $connection;
   $returnvalue = "";
   $query = "select collectionobject.collectionobjectid, collectionobject.timestampcreated, lastname, collectionobject.description, fieldnumber " .
   		" from collectionobject left join fragment on collectionobject.collectionobjectid = fragment.collectionobjectid " .
   		" left join agent on collectionobject.createdbyagentid = agent.agentid " .
   		" where collectionobject.collectionobjectid is not null " .
   		" and fragment.fragmentid is null";
	if ($debug) { echo "[$query]<BR>"; } 
    $returnvalue .= "<h2>Cases where a CollectionObject is not linked to any Item.  All of these are errors and need to be corrected.</h2>";
	$statement = $connection->prepare($query);
	if ($statement) {
		$statement->execute();
		$statement->bind_result($collectionobjectid,$timestampcreated,$createdby, $description, $fieldnumber);
		$statement->store_result();
        $returnvalue .= "<h2>There are ". $statement->num_rows() . " orphan collection objects.</h2>";
	    $returnvalue .= "<table>";
	    $returnvalue .= "<tr><th>Record Created By</th><th>Date Created</th><th>Collector Number</th><th>Type</th><th>Description</th></tr>";
		while ($statement->fetch()) {
	        $returnvalue .= "<tr><td>$createdby</td><td><a href='specimen_search.php?mode=details&id=$collectionobjectid'>$createdby</a></td><td>$fieldnumber</td><td>$description</td></tr>";
		}
	    $returnvalue .= "</table>";
	}
   return $returnvalue;
} 
 
function unlinked_items() {
        global $connection;
   $returnvalue = "";
   $query = " select distinct fragment.identifier, preparation.identifier, loan.loannumber, " .
            "        fragment.timestampcreated, agent.lastname, taxon.fullname, fragment.text1 " . 
            " from fragment left join preparation on fragment.preparationid = preparation.preparationid "  .
            "     left join loanpreparation on preparation.preparationid = loanpreparation.preparationid ". 
            "     left join loan on loanpreparation.loanid = loan.loanid ". 
   	    "     left join agent on fragment.createdbyagentid = agent.agentid " .
   	    "     left join determination on fragment.fragmentid = determination.fragmentid " .
   	    "     left join taxon on determination.taxonid = taxon.taxonid " .
            "  where fragment.collectionobjectid is null " . 
   	    "     and (determination.taxonid is null or determination.iscurrent = 1) " .
            "  order by loan.loannumber desc, fragment.identifier asc ";
        if ($debug) { echo "[$query]<BR>"; }
    $returnvalue .= "<h2>Cases where an Item is not linked to a CollectionObject.  All of these are errors and need to be corrected.  This is a likely cause of loan paperwork failing to print, so loan numbers are included when preparations are involved in loans.</h2>";
        $statement = $connection->prepare($query);
        if ($statement) {
                $statement->execute();
                $statement->bind_result($fragbarcode,$prepbarcode,$loannumber,$datecreated,$createdby,$fullname,$herb);
                $statement->store_result();
        $returnvalue .= "<h2>There are ". $statement->num_rows() . " orphan collection objects.</h2>";
            $returnvalue .= "<table>";
            $returnvalue .= "<tr><th>Record Created By</th><th>Date Created</th><th>Barcode</th><th>Loan Number</th><th>Current Det.</th></tr>";
                while ($statement->fetch()) {
                $returnvalue .= "<tr><td>$createdby</td><td>$datecreated</td><td>$herb <a href='specimen_search.php?mode=details&barcode=$fragbarcode'>$fragbarcode $prepbarcode</a></td><td>$loannumber</td><td>$fullname</td></tr>";
                }
            $returnvalue .= "</table>";
        }
   return $returnvalue;
}



function unlinked_preparations() { 
	global $connection;
   $returnvalue = "";
   $query = "select preptype.name, preparation.identifier, preparation.preparationid, preparation.timestampcreated, lastname " .
   		" from preparation left join fragment on preparation.preparationid = fragment.preparationid " .
   		" left join agent on preparation.createdbyagentid = agent.agentid " .
   		" left join preptype on preparation.preptypeid = preptype.preptypeid " .
   		" where preparation.preparationid is not null and fragment.fragmentid is null " .
   		" and ((agent.lastname is null and preptype.name <> 'Lot') or agent.lastname is not null)";
	if ($debug) { echo "[$query]<BR>"; } 
    $returnvalue .= "<h2>Cases where a Preparation is not linked to any Item.  These may be errors or may be loaned lots with no further information.</h2>";
	$statement = $connection->prepare($query);
	if ($statement) {
		$statement->execute();
		$statement->bind_result($preptype,$barcode,$preparationid, $datecreated, $createdby);
		$statement->store_result();
        $returnvalue .= "<h2>There are ". $statement->num_rows() . " orphan preparations.</h2>";
	    $returnvalue .= "<table>";
	    $returnvalue .= "<tr><th>Record Created By</th><th>Date Created</th><th>Preparation Barcode</th><th>Type</th></tr>";
		while ($statement->fetch()) {
	        $returnvalue .= "<tr><td>$createdby</td><td>$datecreated</td><td>$barcode</td><td>$preptype</td></tr>";
		}
	    $returnvalue .= "</table>";
	}
   return $returnvalue;
} 
function items_with_no_preparation() { 
	global $connection;
   $returnvalue = "";
   $query = "select fragment.text1, fragment.identifier, fragment.timestampcreated, lastname " .
   		" from fragment " .
   		" left join agent on fragment.createdbyagentid = agent.agentid " .
   		" where fragment.preparationid is null order by fragment.timestampcreated desc ";
	if ($debug) { echo "[$query]<BR>"; } 
    $returnvalue .= "<h2>Cases where an Item is not linked to a Preparation.  These are errors and need to be fixed.</h2>";
	$statement = $connection->prepare($query);
	if ($statement) {
		$statement->execute();
		$statement->bind_result($herb,$barcode, $datecreated, $createdby);
		$statement->store_result();
        $returnvalue .= "<h2>There are ". $statement->num_rows() . " items without a preparation.</h2>";
	    $returnvalue .= "<table>";
	    $returnvalue .= "<tr><th>Record Created By</th><th>Date Created</th><th>Barcode</th><th></th></tr>";
		while ($statement->fetch()) {
	        $returnvalue .= "<tr><td>$createdby</td><td>$datecreated</td><td>$herb-$barcode</td><td></td></tr>";
		}
	    $returnvalue .= "</table>";
	}
   return $returnvalue;
} 

function list_entry_for_collectingevents_without_locality() { 
	global $connection;
    $returnvalue = "";

    $query = "select count(*),agent.lastname, agent.agentid from collectionobject left join collectingevent on collectionobject.collectingeventid = collectingevent.collectingeventid left join agent on collectionobject.createdbyagentid = agent.agentid where localityid is null and year(collectionobject.timestampcreated) > 2009 group by agent.lastname";
	if ($debug) { echo "[$query]<BR>"; } 
    $returnvalue .= "<h2>Cases where a collecting event isn't linked to a locality.</h2>";
	$statement = $connection->prepare($query);
	if ($statement) {
		$statement->execute();
		$statement->bind_result($count, $createdby, $agentid);
		$statement->store_result();
	    $returnvalue .= "<table>";
	    $returnvalue .= "<tr><th>Number of Records</th><th>Collection Object Record Created By</th></tr>";
		while ($statement->fetch()) {
	        $returnvalue .= "<tr><td>$count</td><td><a href='qc.php?mode=collectingevents_without_locality&agentid=$agentid'>$createdby</td></tr>";
		}
	    $returnvalue .= "</table>";
	}
   return $returnvalue;

}

function collectingevents_without_locality($agentid) { 
	global $connection;
   $returnvalue = "";
   $query = " select collectionobjectid, collectionobject.timestampcreated, agent.lastname, collectingevent.remarks, collectionobject.remarks " .
   		" from collectionobject left join collectingevent on collectionobject.collectingeventid = collectingevent.collectingeventid " .
   		" left join agent on collectionobject.createdbyagentid = agent.agentid " .
   		" where localityid is null and year(collectionobject.timestampcreated) > 2009 and agentid = ? ";
	if ($debug) { echo "[$query]<BR>"; } 
    $returnvalue .= "<h2>Cases where a Collecting Event isn't linked to a locality for $agent.</h2>";
	$statement = $connection->prepare($query);
	if ($statement) {
		$statement->bind_param("s",$agentid);
		$statement->execute();
		$statement->bind_result($collectionobjectid, $datecreated, $createdby, $eventremarks, $objectremarks);
		$statement->store_result();
        $returnvalue .= "<h2>There are ". $statement->num_rows() . " collecting events without a locality for this person.</h2>";
	    $returnvalue .= "<table>";
	    $returnvalue .= "<tr><th>Record Created By</th><th>Date Created</th><th>Remarks</th></tr>";
		while ($statement->fetch()) {
	        $returnvalue .= "<tr><td>$createdby</td><td><a href='specimen_search.php?mode=details&id=$collectionobjectid'>$datecreated</a></td><td>$eventremarks $objectremarks</td></tr>";
		}
	    $returnvalue .= "</table>";
	}
   return $returnvalue;
}

 
function collectionobjects_without_barcodes() { 
	global $connection;
   $returnvalue = "";
   $query = " select c.collectionobjectid, a.lastname, c.timestampcreated, c.fieldnumber, fragment.text1, c.remarks " .
   		" from collectionobject c left join fragment on c.collectionobjectid = fragment.collectionobjectid " .
   		" left join preparation on fragment.preparationid = preparation.preparationid " .
   		" left join agent a on c.createdbyagentid = a.agentid " .
   		" where fragment.identifier is null and preparation.identifier is null " .
   		" order by a.lastname, c.timestampcreated ";
	if ($debug) { echo "[$query]<BR>"; } 
    $returnvalue .= "<h2>Cases where a Collection object lacks a barcode on one or more items.</h2>";
	$statement = $connection->prepare($query);
	if ($statement) {
		$statement->execute();
		$statement->bind_result($collectionobjectid, $createdby, $datecreated, $fieldnumber, $herbarium, $objectremarks);
		$statement->store_result();
        $returnvalue .= "<h2>There are ". $statement->num_rows() . " collection objects without a barcode.</h2>";
	    $returnvalue .= "<table>";
	    $returnvalue .= "<tr><th>Record Created By</th><th>Date Created</th><th>Field Number</th><th>Herbarium</th><th>Remarks</th></tr>";
		while ($statement->fetch()) {
	        $returnvalue .= "<tr><td>$createdby</td><td><a href='specimen_search.php?mode=details&id=$collectionobjectid'>$datecreated</a></td><td>$fieldnumber</td><td>$herbarium</td><td>$objectremarks</td></tr>";
		}
	    $returnvalue .= "</table>";
	}
   return $returnvalue;
}

function show_table_locks() {
	global $connection;
	$returnvalue = "";
	
	$sql =  "select taskname, lockedtime, islocked=true, machinename from sptasksemaphore";
	if ($debug) { echo "[$sql]"; }
	$statement = $connection->prepare($sql);
	if ($statement) {
	   $statement->execute();
	   $statement->bind_result($taskname,$lockedtime,$islocked,$machinename);
	   $statement->store_result();
	   while ($statement->fetch()) {
	   	  if ($islocked==0) { $islocked="not locked"; } else { $islocked="<strong style=' font-color: red;'>Locked</strong>"; }
	   	  $returnvalue .= "$taskname $islocked $machinename $lockedtime<BR>"; 
	   }	
	}
	
	return $returnvalue; 
}

function force_unlock() {
        global $debug;
	if (preg_match("/^140\.247\.98\./",$_SERVER['REMOTE_ADDR']) || $_SERVER['REMOTE_ADDR']=='127.0.0.1') { 
	$unlockpassphrase = $_GET['unlockpassphrase'];
	$returnvalue = "<strong>Attempting Unlock</strong><BR>";
	if (correctUnlockPassphrase($unlockpassphrase)) { 
	      $admconnection = specify_spasa1_adm_connect();
	      $sql = "update sptasksemaphore set islocked = false, machinename = ''";
	      if ($debug) { echo "[$sql]"; }
	      $statement = $admconnection->prepare($sql);
	      if ($statement) {
	            $statement->execute();
	            $changedrowcount = $admconnection->affected_rows;
	            $returnvalue .= "Changed $changedrowcount rows in the lock table.<BR>";
	      }
	      $admconnection->close();
	} else {
		$returnvalue = "<strong>Unlock Failed</strong><BR>"; 
	}
	}
	$returnvalue .= show_table_locks();
	return $returnvalue; 
}

function person_week_records ($person,$year,$week) { 
    global $connection;
    $returnvalue = "";
    $sql = "select 'modified' as action, c.collectionobjectid, lastname, f.identifier 
          from collectionobject c left join agent on c.modifiedbyagentid = agent.agentid 
               left join fragment f on c.collectionobjectid = f.collectionobjectid
          where lastname = ? and year(c.timestampmodified) = ? and week(c.timestampmodified) = ?
          union
          select 'created' as action, c.collectionobjectid, lastname, f.identifier
          from collectionobject c left join agent on c.createdbyagentid = agent.agentid 
               left join fragment f on c.collectionobjectid = f.collectionobjectid 
          where lastname = ? and year(c.timestampmodified) = ? and week(c.timestampmodified) = ?
          order by action, collectionobjectid
    ";
    if ($debug) { echo "[$sql]<BR>"; } 
    $statement = $connection->prepare($sql);
    if ($statement) { 
         $statement->bind_param("siisii",$person,$year,$week,$person,$year,$week);
         $returnvalue .= "In week $week of $year, the following collection object records were created or modified by $person<BR>";
         $returnvalue .=  "Lastname Action Barcode [collectionobjectid]<br>";
         $statement->execute();
         $statement->bind_result($state, $collectionobjectid, $lastname, $barcode);
         $statement->store_result();
         while ($statement->fetch()) {
            $returnvalue .=  "$lastname $state <a href='specimen_search.php?barcode=$barcode'>$barcode</a> [$collectionobjectid]<br>";
         }
    }    
    return $returnvalue;
} 

function weekly_rate($type='created') { 
   global $connection;
   $returnvalue = "";
   if ($type=='modified') { 
   	  $type='last modified';
   	  $effort = "does not capture";
   $query = "select count(f.fragmentid), lastname, year(c.timestampmodified), week(c.timestampmodified) " .
   		" from collectionobject c left join agent on c.modifiedbyagentid = agent.agentid " .
   		" left join fragment f on c.collectionobjectid = f.collectionobjectid " .
   		" group by lastname, year(c.timestampmodified), week(c.timestampmodified)" .
   		" order by lastname, year(c.timestampmodified), week(c.timestampmodified) ";
   } else { 
   	  $type='created';
   	  $effort = "significantly underestimates";
   $query = "select count(f.fragmentid), lastname, year(c.timestampcreated), week(c.timestampcreated) " .
   		" from collectionobject c left join agent on c.createdbyagentid = agent.agentid " .
   		" left join fragment f on c.collectionobjectid = f.collectionobjectid " .
   		" group by lastname, year(c.timestampcreated), week(c.timestampcreated)" .
   		" order by lastname, year(c.timestampcreated), week(c.timestampcreated) ";
   }
	if ($debug) { echo "[$query]<BR>"; } 
    $returnvalue .= "<h2>New Barcoded Item Records $type per person per week.</h2>";
    $returnvalue .= "<h2>Note: This report $effort data quality and data enhancement efforts.</h2>";
	$statement = $connection->prepare($query);
	if ($statement) {
		$statement->execute();
		$statement->bind_result($count, $createdby, $yearcreated, $weekcreated);
		$statement->store_result();
	    $fullist .= "<table>";
	    $fullist .= "<tr><th>Record $type By</th><th>Year</th><th>Week</th><th>Number $type in Week</th></tr>";
	    $summary .= "<table>";
	    $summary .= "<tr><th>Record $type By</th><th>Total Barcoded Item Records $type</th></tr>";
	    $persontotal = 0;
	    $oldperson = "";
		while ($statement->fetch()) {
	        $fullist.= "<tr><td>$createdby</td><td>$yearcreated</td><td>$weekcreated</td><td><strong><a href='qc.php?mode=person_week_records&person=$createdby&week=$weekcreated&year=$yearcreated'>$count</a></strong></td></tr>";
	        if ($oldperson != "" && $oldperson != $createdby) { 
	             $summary .= "<tr><td>$oldperson</td><td><strong>$persontotal</strong></td></tr>";
	             $persontotal = 0;
	        } else { 
	             $persontotal += $count;
                }
	        $oldperson = $createdby;
		}
	    $fullist .= "</table>";
	    $summary .= "</table>";
	    
	}   
	$returnvalue .= "$summary\n$fullist";
   
   return $returnvalue;
}


function nevp_records_without_images($batch = "") { 
    global $connection,$debug;
    $returnvalue = "";
    if ($batch!="") { 
       $wherebit = " and internalremarks like ? ";
    }
    $sql = "
       select c.internalremarks, f.identifier, c.collectionobjectid from collectionobject c
       left join IMAGE_SET_collectionobject i on c.collectionobjectid = i.collectionobjectid 
       left join fragment f on c.collectionobjectid = f.collectionobjectid 
       where internalremarks like 'NEVP Ingest%' and i.imagesetid is null $wherebit
       order by c.internalremarks
    ";
    if ($debug) { echo "[$sql]<BR>"; } 
    $statement = $connection->prepare($sql);
    if ($statement) { 
         $returnvalue .= "The following collection object records were created by the NEVP ingest, but lack an image.<BR>";
         $returnvalue .= "<ul>";
         $returnvalue .= "<li><a href='qc.php?mode=NEVP&batch=2013-'>Records without images in 2013 batches</a></li>";
         $returnvalue .= "<li><a href='qc.php?mode=NEVP&batch=2014-'>Records without images in 2014 batches</a></li>";
         $returnvalue .= "<li><a href='qc.php?mode=NEVP&batch=2015-'>Records without images in 2015 batches</a></li>";
         $returnvalue .= "<li><a href='qc.php?mode=NEVP&batch=2016-'>Records without images in 2016 batches</a></li>";
         $returnvalue .= "<li><a href='qc.php?mode=NEVP&batch=2017-'>Records without images in 2017 batches</a></li>";
         $returnvalue .= "</ul>";
         if ($batch!="") { 
            $batch = '%' . $batch . '%';
            if ($debug) { echo "[$batch]<BR>"; } 
            $statement->bind_param('s',$batch);
         }
         $statement->execute();
         $statement->bind_result($remark, $barcode, $collectionobjectid);
         $statement->store_result();
         while ($statement->fetch()) {
            preg_match('/(NEVP Ingest from Batch )(urn:uuid:[a-z0-9\-]*)( .*)$/',$remark,$matches);
            if (count($matches)>0) { 
               $remark = $matches[1]." <a href='image_search.php?mode=qc&batch=".$matches[2]."'>".$matches[2]."</a> ".$matches[3] ;
            } 
            $returnvalue .=  "$remark <a href='specimen_search.php?mode=details&id=$collectionobjectid'>$barcode</a><br>";
         }
    }    
    return $returnvalue;
} 


mysqli_report(MYSQLI_REPORT_OFF);
 
?>
