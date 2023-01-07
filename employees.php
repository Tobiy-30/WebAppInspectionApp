<?php

/*  Employees-List page with Add-Employee facility */

// UI (HTML) Template Files
require_once 'ui/html_header_footer_template.php';
require_once "ui/navbar_template.php";
require_once "ui/employees_table_template.php";
require_once "ui/employee_list_template.php";
require_once "ui/developer_readme_template.php";

// Database-Layer Files
require_once "database/database.php";
require_once "database/tables.php";
require_once "database/employee_query.php";

require_once "utils/session.php";

    $session = new AuthSession();

    echo htmlHeader();
    echo htmlOpenBody();
    echo htmlNavbar('Admin', $session->loggedInUsername());

    echo htmlShowReadme(
        "<h1>'Manage Employees'</h1>"        
    );

    /* DO we have any POST data? */
    if (ISSET($_POST)) {
        /* Yes..... so create a PDO which gives us a DB connection */
        $pdo = createAppPDO();

        /* Now make use of classes in the database/ layer which have the
         * ability to fetch (or send) data from (or to) the DB.
         *
         * All classes that access the DB take the PDO, which is their
         * way of accessing the database.
         */
        $employeeQuery = new EmployeeQuery($pdo);
        $searchCriteria = ISSET($_POST['search']) ? $_POST['search'] : '';

        /* This asks the employee list object to do a search for employees */
        $employees = $employeeQuery->searchForEmployee($searchCriteria);

        /* How many employees are there in the list? If we have any, show
         * them in a simple table.
         *
         * NOTE: This really needs cleaning up! It is functional but hardly
         * very pretty yet..
         */
        if (count($employees) > 0) {
            $htmlTable = htmlEmployeeTable($employees);
            echo htmlEmployeesList($htmlTable);
        } else {
            echo '<p>No employees found</p>';
        }
    }
?>
        </div>
    </div>
    <?php
        /* Page footer */
        echo htmlFooterNav();
    ?>
</body>
</html>
