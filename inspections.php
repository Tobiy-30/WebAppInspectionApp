<?php

// UI (HTML) Template Files
require_once 'ui/html_header_footer_template.php';
require_once 'ui/navbar_template.php';
require_once 'ui/inspections_list_template.php';

require_once "database/inspections_list_query.php";

require_once "utils/session.php";
require_once "utils/pdf.php";

$session = new AuthSession();

$extraCSS = <<<END
    <link rel="stylesheet" href="./css/inspectionstable.css">
END;

echo htmlHeader($extraCSS);
echo htmlOpenBody();
echo htmlNavbar('Inspections', $session->loggedInUsername());

$pdo = createAppPDO();
$query = new InspectionsListQuery($pdo);

$orderby= ISSET($_GET['sort']) ? $_GET['sort'] : ''; 
if ($orderby=='') $orderby = 'ID';
$SiteName= ISSET($_GET['sitename']) ? $_GET['sitename'] : '';
$Date= ISSET($_GET['date']) ? $_GET['date'] : '';
$Month= ISSET($_GET['month']) ? $_GET['month'] : '';
$Year= ISSET($_GET['year']) ? $_GET['year'] : '';
$Inspector=  ISSET($_GET['inspector']) ? $_GET['inspector'] : '';
$inspections = $query->getInspections($orderby, $SiteName, $Date, $Month, $Year, $Inspector);

$htmlTableRows = '';

if(sizeof($inspections)>0) {
    foreach($inspections as $row)
    {
        if($SiteName !=''){
            if(stristr($row['SiteName'], $SiteName)==false)
            continue;
        }
        if($Date !=''){
            if(stristr($row['Date'], $Date)==false)
            continue;
        }
        if($Month !=''){
            if(stristr($row['Month'], $Month)==false)
            continue;
        }
        if($Year !=''){
            if(stristr($row['Year'], $Year)==false)
            continue;
        }
        if($Inspector !=''){
            if(stristr($row['Inspector'], $Inspector)==false)
            continue;
        }

        $inspectionID = $row['ID'];
        $pdfLink = PDFGenerator::getWebserverPathForInspectionReport($inspectionID);
        $htmlTableRows .= htmlShowInspectionsTableRow($row, $pdfLink);
    }
} else {
    $htmlTableRows .= "<tr><td>No records found</td></tr>";
}

echo htmlInspectionFilter();
echo htmlInspectionsTable($htmlTableRows);

echo ('Filtered by: '. $SiteName . ' '.$Date.' '. $Month.' '. $Year.' '. $Inspector); 

echo htmlFooterNav();
?>
 