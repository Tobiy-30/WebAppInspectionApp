# Developing Web Solutions Module (MOD002701) - Team 1
# Inspections-Management Project

This repository contains the code for the Inspections-Management (Musk) assignment
for Team 1, submitted April 2022, the coursework project completed as part of the
"Developing Web Solutions" Module (MOD002701) at Anglia Ruskin University, UK.

# Development Environment Setup Instructions

## Prerequisites:
This guide assumes the following software is installed and configured on your machine before you begin:
1. XAMPP (or equivalent)
   
    This is a pre-configured bundle containing the Apache webserver, MySQL (actually MariaDB, a fork of MySQL), PHP and other tools not required for this project (Tomcat, FTP and others).

2. VSCode
   
   Although other options are possible, this is a powerful and completely free tool provided by Microsoft, supporting the majority of the team's development needs.

   Installation of the following extensions is highly advised, too:
      - PHP Debug (connects to XDebug for debugging ability)
      - PHP Extension Pack (providing PHP awareness)
      - PHP IntelliSense (PHP autocompletion and refactoring support)
      - Git History (for extended Git support)

3. Github Desktop
   
   This makes obtaining and synchronising code changes much easier, since the project's source code is stored in a Github repository

---
## Setup steps

### Basic Installation

1. Clone this reposititory somewhere in your htdocs folder. If you're using XAMPP, this will be within your XAMPP installation directory.
   - On Mac this will likely be /Applications/XAMPP/htdocs/
   - On Windows this will likely be C:\xampp\htdocs\

2. Go to your PHPMyAdmin page (for XAMPP this is http://localhost/phpmyadmin/)
3. Create an empty Database. The name of this needs to match the name configured in the php-side code. Please set this to 'musk'.
4. In your web browser, navigate to the default page where you've installed the application's files. For example,

   http://localhost/musk-project/

Before you can do much with the application, you'll need to create the database tables in the database. Use the PHPMyAdmin **Import** menu-item and upload the file `database/schema.sql`.

At this point, it should in theory be possible to start using the application - from a 'blank' state.

Initially, only one user exists, named `Root`. The email address of this user is `root@musk.com`, and the password for this user is `peter`. Currently, though, functionality beyond that point is limited without also importing some test data, since no inspection sites exist yet (and there is currently no way to add any through the application itself).

### Test Data Files

Test data files are provided with fictious inspection sites, employees and inspection reports. These can be imported using the same method as for the `schema.sql` file above. However, these _**must** be imported in the order listed_ - and after importing `schema.sql`.

1. `testdata/testdata_employees_and_sites.sql`
   
   This inserts ficticious inspection sites and employees into the application's database ('musk').

2. `testdata/testdata_inspections.sql`
   
   This inserts ficticious inspections and associated interventions into the application's database.

   Note: if you intend to try and _view_ these inspection reports, please also ensure that you first copy all PDF files from `testdata/pdf/` into the folder `pdf/`, so the application can find them.

# Supported Functionality

At present, the following functionality exists in the application:
- Ability to login, logout and authenticate.
- Ability to add new employees.
- Ability to add new inspection reports.
- List inspection reports that exist in the database.
- View interventions by Site, Month and Year
- View interventions by Type, Month and Year
- Front page, including Navbar, Carousel, Dashboard and Carousel

Functionality currently missing:
- Ability to edit or delete Employees.
- Ability to add, edit or delete Sites.
- Ability to purge old report data.
- Ability to define or make use of Access Control information. ("Employee Roles").
- Ability to set the CompletedBy or EnteredBy fields of inspection reports.
- Ability to show report data in the formats presented in Musk examples 4 and 5.
- Sorting of report data.
- Ability to show 'most-common positive interventions' data.
- Wiring-up of the dashboard cards to the database. (Currently all data except the identity of the currently-logged-in employee is hard-coded).

# Developer Notes (for Team Consumption)

## Application Structure

Please see notes on the web about the MVC (Model View Controller) paradigm.
Roughly, the application structure fits into this as follows:

### View Layer:
Files: ui/* (HTML template files) - simple, dumb functions that return blocks
of HTML - often taking parameters that are then inserted into the body of these
HTML blocks.

### Controller Layer:
Front-end PHP scripts in the project-root (/) directory, plus the auth script
under auth/. These are the entry points run when a web browser makes HTTP
requests to our web server which then runs our PHP scripts.

These are conceptually the 'in-control' pages that decide what a given page
will actually do, and the 'business logic' required, but leaning heavily on
the services of the other layers in order to achieve this. For example,
you would not expect any SQL or direct database access to occur in here. Nor
would you expect much in the way of HTML code (though small fragments do
exist here and there).

### Model layer:
This is all the code under the database/ folder. This is where all the code
is that connects directory to the database, creates PDOs, performs SQL
statements - whether INSERTs, UPDATEs or complex queries. You will certainly
not find any HTML or CSS in here!
