# Mentor-Management

#  Setup Instructions

bash
# Step 1: Create the Database
# ------------------------------------
# - Open your SQL tool (e.g., phpMyAdmin or MySQL CLI).
# - Import the `db.sql` file to create the required database and tables.

# Example using phpMyAdmin:
# 1. Create a new database (e.g., project_db)
# 2. Go to the Import tab
# 3. Upload and execute the `db.sql` file


bash
# Step 2: Setup Project Files
# ------------------------------------
# - If you're using WAMP, place the project folder in:
C:/wamp/www/your-project-folder/

# - If you're using XAMPP, place the folder in:
C:/xampp/htdocs/your-project-folder/


bash
# Step 3: Pull Project Files from GitHub
# ------------------------------------

# Clone the repository (replace the link with your actual repo URL)
git clone https://github.com/your-username/your-repo-name.git

# OR if you've already cloned it, just pull the latest files
git pull origin main

# Note: You don’t need to run the db.sql file in the browser.
# It should only be used for importing into the database.


bash
# Step 4: Run the Project
# ------------------------------------
# - Start your WAMP or XAMPP server.
# - Open your browser and go to:
http://localhost/your-project-folder/


