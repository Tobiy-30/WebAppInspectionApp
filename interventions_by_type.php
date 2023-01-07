<?php

// UI (HTML) Template Files
require_once 'ui/html_header_footer_template.php';
require_once 'ui/navbar_template.php';
require_once "ui/developer_readme_template.php";
require_once 'ui/interventions_by_type_template.php';

require_once 'database/interventions_by_type_query.php';

require_once 'utils/session.php';
require_once 'utils/common.php';

$extraCSS = '<link href="css/interventions_by_type.css" rel="stylesheet">';

$enableBootstrap = true;
echo htmlHeader($extraCSS, $enableBootstrap);

$session = new AuthSession();

echo htmlOpenBody();
echo htmlNavbar('Reports', $session->loggedInUsername());

$interventions = [];
$searchFilter = '';

if(isset($_POST['btnFilter']))
    $searchFilter = trim($_POST['filter']);

$pdo = createAppPDO();
$reportsQuery = new InterventionsByTypeQuery($pdo);
$interventions = $reportsQuery->findSummaryOfReports($searchFilter);

echo '<main>';
echo htmlInterventionsByTypeHeader($thisScript);

$htmlRows = '';

if (count($interventions) > 0) {
    foreach($interventions as $row) {   
        $htmlRows .= htmlInterventionsByTypeRow($row);
    }
}
else
{
    $htmlRows .= <<<END
        <tr><td>No records found</td></tr>
END;
}

echo htmlInterventionsByTypeTable($thisScript, $htmlRows);
echo '</main>';

/* Page footer */
echo htmlFooterNav();
?>

</body>
</html>