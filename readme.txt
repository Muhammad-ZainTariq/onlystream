To run this project, ensure the following are set up on your system:

XAMPP Installed:

Download and install XAMPP (if not already installed) from https://www.apachefriends.org/.

XAMPP should be installed in the default directory: C:\xampp.


Apache and MySQL Running:

Open the XAMPP Control Panel.
Start the Apache and MySQL modules to ensure the web server and database are running.


MySQL Workbench Installed:

Download and install MySQL Workbench from https://dev.mysql.com/downloads/workbench/.
This will be used to set up the database for the project.



Setup Instructions
Follow these steps to set up and run the project:
1. extract and Copy the Project Files

Ensure the project folder is placed at the following exact path:C:\xampp\htdocs\onlystream


The folder structure should look like this:C:\xampp\htdocs\onlystream\
    audio_storage\
    video_storage\
    admin_approve_staff.php
    admin_reports.php
    adminwork.php
    contact_us.php
    creatorhub.php
    databasestructure.txt
    db_connection.php
    display_audio.php
    functions.php
    index.php
    login.php
    logout.php
    membership.php
    navigation.php
    staff_login.php
    styles.css
    



2. Set Up the Database Using MySQL Workbench

Open MySQL Workbench:
Launch MySQL Workbench and connect to your local MySQL server (default XAMPP credentials: username root, password empty).


Create the database and tables:
In MySQL Workbench, go to the "File" menu and select "Open SQL Script".
Navigate to C:\xampp\htdocs\onlystream\databasestructure.sql and open it.
Copy and Paste all the Create and Insert staments.
The script contains all the necessary CREATE TABLE and INSERT statements.
Click the "Execute" button (lightning bolt icon) to run the queries.


What databasestructure.sql Does:
Creates the onlystream schema.
Creates all 17 tables (e.g., users, staff, videos, comments, etc.) with proper dependencies.
Inserts initial data, including:
Two security questions for account recovery.
An admin user for testing.
Sample user, video, and audio data.

note: the same sql file has been added in txt as well.



3. Verify the Setup

In MySQL Workbench, expand the onlystream schema in the sidebar to confirm all tables (e.g., users, staff, videos, comments, etc.) are created.
Check that the staff table contains the admin user for login (details below).

4. Access the Website

Open your browser and navigate to: http://localhost/onlystream/index.php
You should see the staff login page.

Admin Login Credentials
To test the admin features, use the following credentials:

Email: admin@gmail.com
Password: Admin123@

Security Questions
Two security questions are pre-inserted for account recovery:

Question ID 1: "What is the name of your first pet?"
Question ID 2: "What city were you born in?"

Additional Notes

File Paths: The project assumes that video and audio files are stored in video_storage/ and audio_storage/ directories within the onlystream folder. Ensure these directories exist and are writable by the web server (Apache).
Dependencies: The project uses PDO for database interactions. Ensure the PDO MySQL extension is enabled in your PHP setup (it is by default in XAMPP).


Troubleshooting

Database Connection Errors: If you encounter database connection issues, check the db_connection.php file and ensure the database credentials match your XAMPP MySQL setup (default: username root, password empty).
Path Issues: If the website doesn’t load, double-check that the project is in C:\xampp\htdocs\onlystream. Incorrect paths may cause file includes (e.g., require_once 'functions.php') to fail.
Apache/MySQL Not Running: Ensure both Apache and MySQL are started in the XAMPP Control Panel.
MySQL Workbench Connection: If MySQL Workbench cannot connect to the database, ensure the MySQL server is running in XAMPP and that the credentials match (default: host localhost, user root, password empty).



Thank you for reviewing my project!
