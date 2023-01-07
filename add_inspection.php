<?php

// UI (HTML) Template Files
require_once 'ui/html_header_footer_template.php';
require_once "ui/navbar_template.php";
require_once "ui/add_inspection_template.php";

require_once "database/database.php";
require_once "database/tables.php";
require_once "database/inspection_query.php";

require_once "utils/session.php";
require_once "utils/pdf.php";

function buildAccordianHTML($inspectionQuery)
{
    /* Ask the database for a list of distinct categories that exist */
    $categoryList = $inspectionQuery->listCategories();

    /*
     * We'll gradually build all the HTML for the entire accordian's categories
     * and items into this HTML string
     */
    $htmlItems = '';

    foreach($categoryList as $category) {
        $categoryLetter = $category['Category'];

        /* Ask the database to provide a list of all intervention types
         * within this category.
         */
        $categoryInterventionTypes =
            $inspectionQuery->listInterventionTypesForCategory($categoryLetter);

        /* HTML chunk for all intervention types within this category */
        $htmlCategoryInterventions = '';

        /*
         * Build the chunk for HTML for this category's accordian section. This
         * consists of the combined  HTML for all the intervention types within
         * this category. It is gradually built-up in htmlCategoryInterventions.
         */
        foreach ($categoryInterventionTypes as $categoryIntervention) {
            $interventionTypeID = $categoryIntervention['ID'];
            $categoryName = $categoryIntervention['Name'];

            /* Build the HTML for this one intervention type */
            $htmlIntervention = htmlAccordianInterventionItem(
                $interventionTypeID,
                $categoryName,
                '',     /* Comment */
                0,      /* Num of intervention of this type */
                true,   /* Completed? */
                '',     /* action taken */
                false   /* happy (true) or sad (false) */
            );

            /* Add to the chunk of HTML we are building that will be the
             * contents ('payload') of this category in the accordian.
             */
            $htmlCategoryInterventions .= $htmlIntervention;
        }
   
        /* Now build the HTML for this next category, and add it to $htmlItems. */
        $htmlItems .= htmlAccordianInterventionsCategory(
            $categoryLetter, $categoryName, false /* show */, $htmlCategoryInterventions);
    }

    $accordian = htmlInterventionsAccordion($htmlItems);
    return $accordian;
}

/**
 * Look at post data to figure out which types of intervention the user wants to
 * be added to the Inspection, and build-up an array of Intervention database
 * objects for them.
 */
function buildInterventionArray()
{
    $interventions = array();

    /**
     * This should probably find the InterventionType IDs from the database but is
     * hard-coded for now.
     */
    for($i = 1; $i <= 24; $i++)
    {
        /**
         * The value of the 'name' attribute in the HTML form, for the numbers of interventions,
         * for this intervention type. We need this first, as it tells us which intervention types
         * we actually need to create Interventions for, in the database (ie, all those where the
         * number of interventions is >0)
         */
        $numInterventionsNameAttr = "num-interventions-$i";
        $numInterventions = ISSET($_POST[$numInterventionsNameAttr]) ? $_POST[$numInterventionsNameAttr] : 0;

        /* Create Intervention object if we actually have any interventions of this type. */
        if ($numInterventions > 0) {
            $intervention = new Intervention();

            /*
             * Work out the values of the 'name' attributes of the HTML form elements we need
             * to check (we need to look each of these up in $_POST[] to get the values of
             * these fields from the HTML form)
             */
            $commentNameAttr = "comment-$i";
            $completedNameAttr = "completed-$i";
            $isPositiveNameAttr = "good-bad-$i";
            $actionTakenNameAttr = "action-comment-$i";
            /*
             * No need to populate these as it will be handled when we insert the Inspection
             * and its Interventions into the database later.
             */
            $intervention->InspectionID = 0;
            $intervention->ID = 0;;

            $intervention->NumberOfInterventions = intval($numInterventions);
            $intervention->InterventionType = intval($i);
            $intervention->Comment = ISSET($_POST[$commentNameAttr]) ? $_POST[$commentNameAttr] : '';

            $intervention->Completed = false;
            if (ISSET($_POST[$completedNameAttr])) {
                $value = $_POST[$completedNameAttr];
                if ($value == 'completed')
                    $intervention->Completed = true;
            }

            $intervention->ActionTaken = ISSET($_POST[$actionTakenNameAttr]) ? $_POST[$actionTakenNameAttr] : '';

            $intervention->IsPositive = false;
            if (ISSET($_POST[$isPositiveNameAttr])) {
                $value = $_POST[$isPositiveNameAttr];
                if ($value == 'good')
                    $intervention->IsPositive = true;
            }

            /** Add this Intervention object to the array we will pass back to the caller. */
            $interventions[] = $intervention;
        }
    }
    return $interventions;
}


function deleteInspectionAndRelatedData($inspectionQuery, $inspectionID)
{
    /** Delete interventions first so there are no references to the
     * Inspection that could cause data integrity errors.
     */
    $inspectionQuery->deleteInterventionsForInspection($inspectionID);

    /**
     * Now delete the Inspection itself.
     */
    $inspectionQuery->deleteInspection($inspectionID);
}

/* 
 * Locate the uploaded image files and bundle them up into an array that can be
 * passed to the PDF generator.
 */
function buildInterventionImagesArray()
{
    $interventionImages = [];

    for($i = 1; $i <= 24; $i++)
    {
        if (ISSET($_FILES["form-image-$i"])) {
            $info = $_FILES["form-image-$i"];
            if ($info['tmp_name'] && ($info['error'] == 0) && ($info['size'] > 0))
                $interventionImages[$i] = $info;
        }
    }
    return $interventionImages;
}

    $extraHeadContent = '';

    $session = new AuthSession();
    $userDisplayName = $session->loggedInUsername();

    $pdo = createAppPDO();
    $inspectionQuery = new InspectionQuery($pdo);

    /*
     * Setup an alert message that will either show a success message after we insert
     * the new inspection into the database, or an error message if things go wrong.
     */
    $alertMsg = 'Unknown error';    # Regular, user-visible error message
    $debugMsg = '';                 # Not normally shown, but useful for debugging
    $successful = false;            # Successful or not? Default to false

    /*
     * If we manage to generate a PDF file, the pathname to it will be set here.
     * The pathname is relative to the htdocs (/) folder, so we can easily link
     * to it.
     */
    $pathnameOfPDFGenerated = '';

    /** 
     * We may need to call on this at the very bottom of the page, to download the PDF,
     * if we generate one.
     */
    $pdfGenerator = NULL;

    /* Do we have any POST data? */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $site = ISSET($_POST['site']) ? $_POST['site'] : '';
        $date = ISSET($_POST['date']) ? $_POST['date'] : '';
        $workArea = ISSET($_POST['work-area']) ? $_POST['work-area'] : '';
        $jobDescription = ISSET($_POST['job-description']) ? $_POST['job-description'] : '';
        $supervisor = ISSET($_POST['supervisor']) ? $_POST['supervisor'] : '';
        $type = ISSET($_POST['type']) ? $_POST['type'] : '';

        /**
         * Does the meta-data for the inspection look ok?
         */
        if ($site && $date && $workArea && $jobDescription && $supervisor && $type) {
            /* Yes - so start building-up an Inspection database object */
            $inspection = new Inspection();
            
            /* Populate the meta-data fields of the Inspection object */
            $inspection->SiteName = $site;
            $inspection->Date = $date;
            $inspection->WorkArea = $workArea;
            $inspection->JobDescription = $jobDescription;
            $inspection->Supervisor = $supervisor;
            $inspection->Type = $type;
            $inspection->InspectorName = $userDisplayName;

            /* Now start to build-up an array of interventions for this inspection */
            $interventions = buildInterventionArray();

            /**
             * Similary, check whether any images have been uploaded by the user against each 
             * type of intervention.
             */
            $interventionImages = buildInterventionImagesArray();

            /* Try adding the inspection and the interventions to the database */
            try {
                $inspectionID = $inspectionQuery->insertInspectionAndInterventions($inspection, $interventions);

                $pdfGenerator = new PDFGenerator($pdo);
                $pdfGenerator->generatePDF($inspectionID, $interventionImages);

                /* We need the path (relative to the web server) of the new PDF, so we can provide a link to it. */
                $pathnameOfPDFGenerated = PDFGenerator::getWebserverPathForInspectionReport($inspectionID);

                $alertMsg = "Successfully added new Inspection to Database";
                $successful = true;
            } catch (PDOException $e) {
                $alertMsg = "Failed to add new inspection";
                $debugMsg = $e;

                /* Failed to add inspection, its interventions or the PDF - remove these to ensure
                 * data integrity
                 */
                deleteInspectionAndRelatedData($inspectionQuery, $inspectionID);
                $pdfGenerator->deletePDF($inspectionID);
            } catch (Exception $e) {
                $alertMsg = "Failed to add new inspection";
                $debugMsg = $e;

                /* Failed to add inspection, its interventions or the PDF - remove these to ensure
                 * data integrity
                 */
                deleteInspectionAndRelatedData($inspectionQuery, $inspectionID);
                $pdfGenerator->deletePDF($inspectionID);
            }
        } else {
            $alertMsg = "One or more required fields was omitted";
            $debugMsg = '';
        }
    }
 
    /* 
     * We arrive here if the form was submitted but the data couldn't be added
     * to the database, *or* the form is being shown for the first time (and
     * hasn't been submitted yet).
     * 
     * Ask the database for a list of objects representing the sites that
     * exist there.
     */
    $sites = $inspectionQuery->listSites();

    $htmlSites = '';

    foreach ($sites as $site){
        $siteName = $site['Name'];
         $htmlSites = $htmlSites . "<option>$siteName</option>\n";
    }

    /*
     * Date input element requires date value in the form YYYY-MM-DD.
     * https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/date
     * https://www.w3schools.com/php/php_date.asp
     */
    $todaysDate = date("Y-m-d");

    # Check whether we need to show an alert message
    $htmlAlertMsg = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $style = $successful ? 'alert-success' : 'alert-danger'; # Bootstrap style

        $htmlAlertMsg = <<<END
            <div class="col-12"><div class="alert justify-content-center $style" role="alert">$alertMsg</div>
    END;
    }

    echo htmlHeader();
    echo htmlOpenBody();
    echo htmlNavbar('Inspections', $userDisplayName);

    $metadata = htmlAddInspectionMetadataElements($userDisplayName, $todaysDate, $htmlSites);
    $accordian = buildAccordianHTML($inspectionQuery);

    echo htmlInspectionForm($metadata . $accordian . $htmlAlertMsg, $successful, $pathnameOfPDFGenerated);
    echo htmlFooterNav();
?>

    </body>
</html>
