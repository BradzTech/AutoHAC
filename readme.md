# Auto Home Access Center

Auto Home Access Center is a program designed to connect with a user's Home Access Center account and send them updates every time a grade is updated. The goal is to always be in the know of one's grades, without the need of constantly refreshing the website.

Auto Home Access Center is a PHP script based around the Laravel Framework 5.3. I wrote it several years ago, so it is admittedly not my best quality programming work. However, I am choosing to open source it to help guide any other developers create something similar. The most important code is contained in app/Http/Controllers/AutohacController.php, which shows how to initiate a cURL request and to parse the HTML.

An HTTP website is used to facilitate the signup process. Outputting messages can be done through the Telegram API (recommended), the Kik API, or Verizon SMS.

## Very brief installation guide

1. Clone the repository to a working web server with PHP (>=5.6) and a MySQL or derivative server.
2. Install Composer if necessary and run composer install.
3. Copy .env.example to .env. Edit .env with the database credentials, any valid HAC domain to retrieve assets from, and appropriate API keys for communication methods.
4. Run the database migration to create the tables.
5. Add at least one row to the AutoHAC_schools table in the database manually.
6. Point the web server to the Laravel location.
