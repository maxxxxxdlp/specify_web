<?php 

if (strpos($_SERVER['HTTP_ACCEPT'],"application/rdf+xml")!==false || strpos($_SERVER['HTTP_USER_AGENT'],'Firefox')!==false) {
    // If firefox, or specifically requested, provide correct content type for response.
    header('Content-type: application/rdf+xml');
} else { 
    // Tell chrome/IE/Opera, other browsers that response is html
    header('Content-type: text/html');
}

/*   <?xml-stylesheet type="text/xsl" href="botaniststyle.xsl"?>
*/

// Resolves to "http://kiki.huh.harvard.edu/databases/rdfgen.php?uuid=$uuid";
// Maintaned at purl.oclc.org
$baseuri = 'http://purl.oclc.org/net/edu.harvard.huh/guid/uuid/';

// See edu.ku.brc.specify.datamodel.Agent
// Definitions for agentype:
//  public static final byte                ORG    = 0;
//  public static final byte                PERSON = 1;
//  public static final byte                OTHER  = 2;
//  public static final byte                GROUP  = 3;
// Definitions for datestype: 
//  public static final byte                BIRTH              = 0;
//  public static final byte                FLOURISHED         = 1;
//  public static final byte                COLLECTED          = 2;
//  public static final byte                RECEIVED_SPECIMENS = 3;

$connection = "";

include_once('connection_library.php');
include_once('specify_library.php');

$connection = specify_connect();
$debug = FALSE;

@$request_uuid = preg_replace('[^a-zA-Z0-9\-]','',$_GET['uuid']);

if (php_sapi_name()==="cli" || $request_uuid!='') { 
   // only run if either request is made from command line interface
   // or a uuid was provided.
   // Prevents web call to generate entire rdf/xml dump, alows this
   // dump only from command line call.


   echo '<?xml version="1.0"?>
<!DOCTYPE rdf:RDF [
  <!ENTITY xsd "http://www.w3.org/2001/XMLSchema#">
]>
';

   $target = "agent";

   if ($request_uuid!='') { 
     // what sort of object is this a guid for? 
     // botanist?, occurance?, publication? 
     $sql = "select tablename from guids where uuid = ? ";
     $statement = $connection->prepare($sql);
     if ($statement) {
       $statement->bind_param('s',$request_uuid);
       $statement->execute();
       $statement->bind_result($tablename);
       $statement->store_result();
       while ($statement->fetch()) {
         $target = $tablename;
       }
     }
   }

     switch ($target) { 

      case "fragment": 
	   if ($request_uuid!='') { 
	      echo '<?xml-stylesheet type="text/xsl" href="specimenstyle.xsl"?>'."\n";
	      echo "<!-- request: $request_uuid -->\n";
	      if ($debug) { 
	         echo "<!-- accept: " . $_SERVER['HTTP_ACCEPT'] . " -->\n";
   	         echo "<!-- agent: " . $_SERVER['HTTP_USER_AGENT'] . " -->\n";
              }
              $sql = 'select fragment.text1, concat(\'barcode-\',fragment.identifier) from guids left join  fragment on guids.primarykey =  fragment.fragmentid  where uuid = ? '; 
	      echo '<rdf:RDF
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
  xmlns:dwc="http://rs.tdwg.org/dwc/terms/"
  >
';
	      if ($debug) { 
	          echo "<! query: $sql >\n";
   	      }
   $statement = $connection->prepare($sql);
   if ($statement) {
      if ($request_uuid!='') { 
        $statement->bind_param('s',$request_uuid);
      }
      $statement->execute();
      $statement->bind_result($collectionCode, $catalogNumber );
      $statement->store_result();
      while ($statement->fetch()) {
         $row = "";
         $occuri = "$baseuri$uuid";
         $row = "<dwc:Occurrence rdf:about=\"$occuri\" >\n";
             if ($collectionCode!='') { $collectionCode = "   <dwc:collectionCode>$collectionCode</dwc:collectionCode>\n"; } 
             if ($catalogNumber!='') { $catalogNumber = "   <dwc:catalogNumber>$catalogNumber</dwc:catalogNumber>\n"; } 
         $row .= "$collectionCode$catalogNumber</dwc:Occurrence>\n";
         echo $row;
	   } // end while  
      } // end if statement 
   echo '</rdf:RDF>';
      } // end if request_uri
         break;

      case "publication":
         break;

      case "agent":
      default;

   if ($request_uuid!='') { 
      echo '<?xml-stylesheet type="text/xsl" href="botaniststyle.xsl"?>'."\n";
      echo "<!-- request: $request_uuid -->\n";
      if ($debug) { 
         echo "<!-- accept: " . $_SERVER['HTTP_ACCEPT'] . " -->\n";
         echo "<!-- agent: " . $_SERVER['HTTP_USER_AGENT'] . " -->\n";
      } 
      $sql = "select uuid, primarykey, agenttype, firstname, lastname, email, remarks, url, dateofbirth, dateofbirthconfidence, dateofbirthprecision, dateofdeath, dateofdeathconfidence, dateofdeathprecision, datestype, state from guids left join agent on agent.agentid = guids.primarykey where tablename = 'agent' and (agenttype > 0 or agenttype is null) and uuid = ? order by agenttype asc ";
   } else { 
       $sql = "select uuid, primarykey, agenttype, firstname, lastname, email, remarks, url, dateofbirth, dateofbirthconfidence, dateofbirthprecision, dateofdeath, dateofdeathconfidence, dateofdeathprecision, datestype, '' as state from agent left join guids on agent.agentid = guids.primarykey where tablename = 'agent' and agenttype > 0 order by agenttype asc ";
   }
   echo '<rdf:RDF
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
  xmlns:foaf="http://xmlns.com/foaf/0.1/"
  xmlns:skos="http://www.w3.org/2004/02/skos/core#"
  xmlns:bio="http://purl.org/vocab/bio/0.1/"
  >
';
   
   if ($debug) { 
       echo "<! query: $sql >\n";
   }
   $statement = $connection->prepare($sql);
   if ($statement) {
      if ($request_uuid!='') { 
        $statement->bind_param('s',$request_uuid);
      }
      $statement->execute();
      $statement->bind_result($uuid, $primarykey, $agenttype, $firstname, $lastname, $email, $remarks, $url, $dateofbirth, $dateofbirthconfidence, $dateofbirthprecision, $dateofdeath, $dateofdeathconfidence, $dateofdeathprecision, $datestype, $state);
      $statement->store_result();
      while ($statement->fetch()) {
         $row = "";
         if ($agenttype == 1) { 
             if ($email!='') { $email = "   <foaf:mbox_sha1sum>" . hash("sha1",$email) . "</foaf:mbox_sha1sum>\n"; } 
             if ($firstname!='') {
                 $firstname = str_replace("&","&amp;",$firstname);
                 $firstname = str_replace("<","&lt;",$firstname);
                 $firstname = "   <foaf:firstname>" . $firstname . "</foaf:firstname>\n";
              } 
             if ($lastname!='') { 
                 $lastname = str_replace("&","&amp;",$lastname);
                 $lastname = str_replace("<","&lt;",$lastname);
                 $lastname = "   <foaf:surname>" . $lastname . "</foaf:surname>\n"; 
             } 
             //$personuri = "http://guids.huh.harvard.edu/resource/$uuid";
             $personuri = "$baseuri$uuid";
             $row = "<foaf:Person rdf:about=\"$personuri\" >
   $firstname$lastname$email   <foaf:isPrimaryTopicOf rdf:resource=\"http://kiki.huh.harvard.edu/databases/botanist_search.php?id=$primarykey\" />\n";
             $remarks = trim($remarks);
             if ($remarks!='') { 
                $remarks = str_replace("&","&amp;",$remarks);
                $remarks = str_replace("<","&lt;",$remarks);
                $row .= "   <skos:note xml:lang=\"en-US\">$remarks</skos:note>\n";
             } 
             $row .= "   <foaf:topic_interest xml:lang=\"en-US\">Botany</foaf:topic_interest>\n";
             if ($url!='') { 
               //$url = rawurlencode($url);
               $row .= "   <foaf:isPrimaryTopicOf rdf:resource=\"$url\" />\n";
             }
             $sqln = "select distinct name from agentvariant where agentid = ? order by vartype desc ";
             $statementn = $connection->prepare($sqln);
             if ($statementn) {
                $statementn->bind_param("i",$primarykey);
                $statementn->execute();
                $statementn->bind_result($name);
                $statementn->store_result();
                $count = 0;
                while ($statementn->fetch()) {
                   $name = str_replace("&","&amp;",$name);
                   $name = str_replace("<","&lt;",$name);
                   if ($count==0) { $row .= "   <rdfs:label>$name</rdfs:label>\n"; } 
                   $row .= "   <foaf:name>$name</foaf:name>\n";
                   $count++;
                }
             }
             $row .= "</foaf:Person>\n";
             if ($datestype==0) { 
                if ($dateofbirthprecision=="") { $dateofbirthprecision = 3;   }
                if ($dateofdeathprecision=="") { $dateofdeathprecision = 3;   }
                // Limit date of birth information for people who are living to the year of birth.
                if ($datestype=="0") {
                    if ($dateofdeath=="") { $dateofbirthprecision = 3;   }
                }
      
                // temporary workaround for dates not being editable below year.
                if (substr($dateofbirth,4,10)=="-01-01") {
                    $dateofbirthprecision = 3;
                }
                if (substr($dateofdeath,4,10)=="-01-01") {
                    $dateofdeathprecision = 3;
                }
      
                $dateofbirth = transformDateText($dateofbirth,$dateofbirthprecision);
                $dateofdeath = transformDateText($dateofdeath,$dateofdeathprecision);
      
                if ($dateofbirth!='') { 
                    $dateofbirth = str_replace("-","/",$dateofbirth);
                    $note = '';
                    if ($dateofbirthconfidence!='') { 
                        $note = "   <skos:note xml:lang=\"en-US\">$dateofbirthconfidence</skos:note>\n";
                    }
                    $row .= "<bio:Birth rdf:about=\"$personuri#birth\">\n   <bio:date rdf:datatype=\"&xsd;date\" >$dateofbirth</bio:date>\n$note   <bio:principal rdf:resource=\"$personuri\" />\n</bio:Birth>\n";
                }
                if ($dateofdeath!='') { 
                    $dateofdeath = str_replace("-","/",$dateofdeath);
                    $note = '';
                    if ($dateofdeathconfidence!='') { 
                        $note = "   <skos:note xml:lang=\"en-US\">$dateofdeathconfidence</skos:note>\n";
                    }
                    $row .= "<bio:Death rdf:about=\"$personuri#death\">\n   <bio:date rdf:datatype=\"&xsd;date\">$dateofdeath</bio:date>\n$note   <bio:principal rdf:resource=\"$personuri\" />\n</bio:Death>\n";
                }
             }
         } else { 
             if ($state!='' ) { 
                $row = "<! state: $state  >\n";
             }
             // skip agent type 0 - organization
             // skip agent type 2 - other
             if ($agenttype ==3) { 
                $row = "<foaf:Group rdf:about=\"$baseuri$uuid\" >
          <foaf:isPrimaryTopicOf rdf:resource=\"http://kiki.huh.harvard.edu/databases/botanist_search.php?id=$primarykey\" />\n";
                $row .= "    <foaf:topic_interest>Botany</foaf:topic_interest>\n"; 
                $remarks = trim($remarks);
                if ($remarks != '') {
                    $remarks = str_replace("&","&amp;",$remarks);
                    $remarks = str_replace("<","&lt;",$remarks);
                    $row .= "    <skos:note>$remarks</skos:note>\n"; 
                }
                if ($url!='') {
                  //$url = rawurlencode($url);
                  $row .= "   <foaf:isPrimaryTopicOf rdf:resource=\"$url\" />\n";
                }
                $sqln = "select distinct name from agentvariant where agentid = ? order by vartype desc ";
                $statementn = $connection->prepare($sqln);
                if ($statementn) {
                   $statementn->bind_param("i",$primarykey);
                   $statementn->execute();
                   $statementn->bind_result($name);
                   $statementn->store_result();
                   $count = 0;
                   while ($statementn->fetch()) {
                      $name = str_replace("&","&amp;",$name);
                      $name = str_replace("<","&lt;",$name);
                      if ($count==0) { $row .= "   <rdfs:label>$name</rdfs:label>\n"; }
                      $row .= "   <foaf:name>$name</foaf:name>\n";
                      $count++;
                   }
                }
                $sqlg = "select distinct uuid from groupperson left join guids on groupperson.memberid = guids.primarykey where groupid = ? and guids.tablename = 'agent' ";
                $statementg = $connection->prepare($sqlg);
                if ($statementg) {
                   $statementg->bind_param("i",$primarykey);
                   $statementg->execute();
                   $statementg->bind_result($memberuuid);
                   $statementg->store_result();
                   while ($statementg->fetch()) {
                      $row .= "   <foaf:member rdf:resource=\"$baseuri$memberuuid\" />\n";
                   }
                }
                $row .= "</foaf:Group>\n";
             }
         } 
         echo $row;
      }
   } // end if else 
   echo '</rdf:RDF>';

   }

} // end is cli or has uuid parameter


?>
