<?php

// UI (HTML) Template Files
require_once 'ui/html_header_footer_template.php';
require_once 'ui/navbar_template.php';
require_once 'ui/dashboard_template.php';
require_once 'ui/carousel_template.php';
require_once 'ui/jumbotron_template.php';

require_once 'utils/session.php';

    /* This is the internal ('intranet') home page, so we need to check
     * we have an authenthenticated user here.
     */
    $requireAuth = true;
    $session = new AuthSession($requireAuth);

    echo htmlHeader();
    echo htmlOpenBody();
    echo htmlNavbar('Home', $session->loggedInUsername());
?>

    <!-- Start of container -->
    <div class="container" style="margin-top: 40px">

    <?php
        /*
         * Even though we don't *require* authentication, if a user *is*
         * logged in, we should show this.
         */

        /*
         * For now, fake this date. In due course it could be fetched from
         * the database.
         */
        $employeeName =$session->loggedInUsername();
        $numInspectionsToday = 80;
        $numNewInterventions = 30;
        $currentBiggestIssueMTD = 'Not Wearing PPE';

        echo htmlDashboard(
            $employeeName,
            $numInspectionsToday,
            $numNewInterventions,
            $currentBiggestIssueMTD);

        $htmlCarouselItems = htmlCarouselItemsInternal();
            echo htmlCarousel($htmlCarouselItems);
        echo htmlJumbotron();
    ?>

    </div>
    <!-- End of container -->

<?php
    echo htmlFooterNav();
?>

    </body>
</html>
