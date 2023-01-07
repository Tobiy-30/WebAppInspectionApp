<?php

/* Publicly-visible web page (no authentication required) */

// UI (HTML) Template Files
require_once 'ui/html_header_footer_template.php';
require_once 'ui/navbar_template.php';
require_once 'ui/dashboard_template.php';
require_once 'ui/carousel_template.php';
require_once 'ui/jumbotron_template.php';

require_once 'utils/session.php';

    /* Do not require authentication - this is a public page */
    $session = new AuthSession(false);

    echo htmlHeader();
    echo htmlOpenBody();
    echo htmlNavbar('Home', $session->loggedInUsername());
?>

    <!-- Start of container -->
    <div class="container" style="margin-top: 40px">

    <?php
        $htmlCarouselItems = htmlCarouselItemsPublic();
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
