<?php 

// TODO: Proper content negotation, delivering RDF/XML or Turtle or HTML
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
$debug = TRUE;

@$request_uuid = preg_replace('[^a-zA-Z0-9\-]','',$_GET['uuid']);
@$request_query = preg_replace('[a-z]','',$_GET['query']);

if (php_sapi_name()==="cli" || $request_uuid!='' || $request_query!='' ) { 
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
   } else { 
      @$request_name = $_GET['name'];
      @$request_barcode = preg_replace('[^0-9]','',$_GET['barcode']);
      if ($request_query == 'agent') { $target = "agent"; } 
      if ($request_query == 'collectionobject') { $target = "fragment"; } 
   }

     switch ($target) { 

      case "fragment": 
	   if ($request_uuid!='' || $request_barcode!='') { 
	      echo '<?xml-stylesheet type="text/xsl" href="specimenstyle.xsl"?>'."\n";
	      echo "<!-- request: $request_uuid -->\n";
	      if ($debug) { 
	         echo "<!-- accept: " . $_SERVER['HTTP_ACCEPT'] . " -->\n";
   	         echo "<!-- agent: " . $_SERVER['HTTP_USER_AGENT'] . " -->\n";
              }
              if ($request_barcode!='') { 
                   $sql = 'select fragment.text1, concat(\'barcode-\',fragment.identifier), fragment.collectionobjectid, continent, country, stateprovince, locality, scientificname, scientificnameauthorship, timestamplastupdated, uuid from guids left join fragment on guids.primarykey =  fragment.fragmentid left join dwc_search on fragment.collectionobjectid = dwc_search.collectionobjectid  where fragment.identifier = ? limit 1 '; 

              } else { 
                   $sql = 'select fragment.text1, concat(\'barcode-\',fragment.identifier), fragment.collectionobjectid, continent, country, stateprovince, locality, scientificname, scientificnameauthorship, timestamplastupdated, uuid from guids left join fragment on guids.primarykey =  fragment.fragmentid left join dwc_search on fragment.collectionobjectid = dwc_search.collectionobjectid  where uuid = ? limit 1 '; 
              }
	      echo '<rdf:RDF
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
  xmlns:dwc="http://rs.tdwg.org/dwc/terms/"
  xmlns:dcterms="http://purl.org/dc/terms/"
  >
';
	      if ($debug) { 
	          echo "<!-- query: $sql -->\n";
   	      }
   $statement = $connection->prepare($sql);
   if ($statement) {
      if ($request_uuid!='') { 
        $statement->bind_param('s',$request_uuid);
      } else { 
         if ($request_barcode!='') { 
            $statement->bind_param('s',$request_barcode);
         }
      }
      $statement->execute();
      $statement->bind_result($collectionCode, $catalogNumber, $collectionobjectid, $continent, $country, $stateProvince, $locality, $scientificname, $authorship, $modified, $uuid );
      $statement->store_result();
      while ($statement->fetch()) {
         $row = "";
         $occuri = "$baseuri$uuid";
         $row = "<dwc:Occurrence rdf:about=\"$occuri\" >\n";
             if ($collectionCode!='') { $collectionCode = "   <dwc:collectionCode>$collectionCode</dwc:collectionCode>\n"; } 
             if ($catalogNumber!='') { $catalogNumber = "   <dwc:catalogNumber>$catalogNumber</dwc:catalogNumber>\n"; } 
             if ($country!='') { $country = "   <dwc:country>$country</dwc:country>\n"; } 
             if ($stateProvince!='') { $stateProvince = "   <dwc:stateProvince>$stateProvince</dwc:stateProvince>\n"; } 
             if ($locality!='') { $locality = "   <dwc:locality>$locality</dwc:locality>\n"; } 
             if ($scientificname!='') { $scientificname = "   <dwc:scientificName>$scientificname</dwc:scientificName>\n"; } 
             if ($authorship!='') { $authorship = "   <dwc:scientificNameAuthorship>$authorship</dwc:scientificNameAuthorship>\n"; } 
             if ($modified!='') { $modified = "   <dcterms:modified>$modified</dcterms:modified>\n"; } 
         $row .= "$collectionCode$catalogNumber$country$stateProvince$locality$scientificname$authorship$modified</dwc:Occurrence>\n";
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
       if ($request_name!='') { 
           echo '<?xml-stylesheet type="text/xsl" href="botaniststyle.xsl"?>'."\n";
           $sql = "select distinct uuid, primarykey, agenttype, firstname, lastname, email, remarks, url, dateofbirth, dateofbirthconfidence, dateofbirthprecision, dateofdeath, dateofdeathconfidence, dateofdeathprecision, datestype, '' as state from agent left join guids on agent.agentid = guids.primarykey left join agentvariant on agent.agentid = agentvariant.agentid where tablename = 'agent' and agenttype > 0 and agentvariant.name = ? order by agenttype asc ";
       } else { 
           $sql = "select uuid, primarykey, agenttype, firstname, lastname, email, remarks, url, dateofbirth, dateofbirthconfidence, dateofbirthprecision, dateofdeath, dateofdeathconfidence, dateofdeathprecision, datestype, '' as state from agent left join guids on agent.agentid = guids.primarykey where tablename = 'agent' and agenttype > 0 order by agenttype asc ";
       }
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
       echo "<!-- query: $sql -->\n";
   }
   $statement = $connection->prepare($sql);
   if ($statement) {
      if ($request_uuid!='') { 
        $statement->bind_param('s',$request_uuid);
      } else { 
          if ($request_name!='') { 
             $statement->bind_param('s',$request_name);
          }
      }
      $statement->execute();
      $statement->bind_result($uuid, $primarykey, $agenttype, $firstname, $lastname, $email, $remarks, $url, $dateofbirth, $dateofbirthconfidence, $dateofbirthprecision, $dateofdeath, $dateofdeathconfidence, $dateofdeathprecision, $datestype, $state);
      $statement->store_result();
      while ($statement->fetch()) {
         $row = "";
         if ($agenttype == 1 || $agenttype == 2) { 
             $foaftype = "Person";
             if ($agenttype==2) { 
                 $foaftype = "Agent";
             }
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
             $row = "<foaf:$foaftype rdf:about=\"$personuri\" >
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
             $query = "select concat(url_prefix,uri) as url, pixel_height, pixel_width, t.name, file_size " .
                      " from IMAGE_SET_agent c left join IMAGE_OBJECT o on c.imagesetid = o.image_set_id " .
                      " left join REPOSITORY r on o.repository_id = r.id " .
                      " left join IMAGE_OBJECT_TYPE t on o.object_type_id = t.id " .
                      " where c.agentid = ? " .
                      " order by object_type_id desc ";
             $statement_img = $connection->prepare($query);
             if ($statement_img) {
             	$statement_img->bind_param("i",$primarykey);
                $statement_img->execute();
                $statement_img->bind_result($url,$height,$width,$imagename,$filesize);
                $statement_img->store_result();
                while ($statement_img->fetch()) {
               		$row .= "   <foaf:isPrimaryTopicOf rdf:resource=\"$url\" />\n";
                }
             } 

             $row .= "</foaf:$foaftype>\n";
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

