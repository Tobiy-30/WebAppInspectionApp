<?php

// UI (HTML) Template Files
require_once 'ui/auth_login_template.php';
require_once 'ui/html_header_footer_template.php';

require_once 'utils/session.php';

    $extraCSS = '<link href="css/login.css" rel="stylesheet">';

    $enableBootstrap = false;
    echo htmlHeader($extraCSS, $enableBootstrap);

    /* Do not use htmlOpenBody() here, as this file does not include a footer.
     *  Instead, just use a regular <body> tag.
     */
    echo '<body>';

    $enteredUsername = isset($_POST['username']) ? $_POST['username'] : '';
    $enteredPassword = isset($_POST['password']) ? $_POST['password'] : '';

    $htmlAlert = '';

    if ($enteredUsername && $enteredPassword) {
        /*
         * Since this is the login dialog, we must NOT require a user is already
         * logged-in, in order to access this page. Otherwise they'll never be able
         * to login!
         */
        $requireAuth = false;
        $session = new AuthSession($requireAuth);

        /*
         * Try to login with the credentials (combination of username/password)
         * provided. If this succeeds, the auth session will redirect the page
         * away from this login dialog. It is therefore only if this fails that
         * execution will continue beyond this login() call, at which point we
         * can display an 'incorrect login' message on the login form.
         */
        $session->login($enteredUsername, $enteredPassword);

        /*
         * If we get here, the attempt at authenticating has failed, so show an
         * error message to the user.
         */
        $htmlAlert = '<div class="error-alert">Incorrect login</div>';
    }

    /*
     * Show the login dialog, whether or not we have credentials to display or
     * not. htmlAlert is set to an error message to display, or an empty string,
     * if not - as decided above.
     */
    echo htmlLoginDialog($enteredUsername, $enteredPassword, $htmlAlert);
?>

</body>
</html>
