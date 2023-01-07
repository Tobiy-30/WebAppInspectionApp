<?php

// UI (HTML) Template Files
require_once 'ui/html_header_footer_template.php';
require_once "ui/navbar_template.php";
require_once "ui/add_employee_template.php";

// Database-Layer Files
require_once "database/database.php";
require_once "database/tables.php";
require_once "database/employee_query.php";

require_once 'utils/session.php';

$session = new AuthSession();

echo htmlHeader();
echo htmlOpenBody();
echo htmlNavbar('Admin', $session->loggedInUsername());

$alertHtml='';

/* Do we have any POST data? This indicates a form submission has occurred */
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    /* Yes - so fetch the fields from the HTML form */
    $employeeName = $_POST['employee-name'];
    $password = $_POST["password"];
    $email = $_POST["email"];

    if ($employeeName) {
        /*
         * We have an employee name in the form, so let's connect to the database
         * and add a new one.
         */

        $pdo = createAppPDO();

        /* Construct Employee object and populate with fields from the HTML form */
        $employeeRow = new Employee();
        $employeeRow->Name = $employeeName;
        $employeeRow->Email = $email;
        $employeeRow->PasswordHash = password_hash($password, PASSWORD_DEFAULT);

        /* Decide how we'll respond to the user. Start with success as a default case. */
        $style = 'alert-success';
        $alertMsg = "Added new Employee $employeeName successfully";

        /* Create a database query object and then perform the query. Catch errors
         * (exceptions) that may occur here.
         */
        $employeeQuery = new EmployeeQuery($pdo);

        try {
            $existingEmployeeWithEmail = $employeeQuery->findEmployeeByUsername($email);

            if ($existingEmployeeWithEmail) {
                $alertMsg = "User with email address '$email' already exists";
                $style = 'alert-danger';
            } else {
                $employeeQuery->insertEmployee($employeeRow);
            }
        } catch (PDOException $e) {
            $alertMsg = "Failed to add new Employee: $employeeName";
            $debugMsg = $e;
            $style = 'alert-danger';
        } catch (Exception $e) {
            $alertMsg = "Failed to add new Employee: $employeeName";
            $debugMsg = $e;
            $style = 'alert-danger';
        }
    } else {
        $alertMsg = "No name given for new user";
        $debugMsg = '';
        $style = 'alert-warning';
    }

    /* Whatever the outcome, print a message back to the user as a Bootstrap 'alert' */
    $alertHtml = <<<END
        <div class="col-12"><div class="alert $style" role="alert">$alertMsg</div>
END;
}

/* Complete the HTML */
echo htmlAddEmployee($alertHtml);
echo htmlFooterNav();

?>
</body>
</html>
