================================================================================
                        SOCIETYPRESS INSTALLER
                             Version 1.0.0
================================================================================

WHAT THIS DOES
--------------
This installer sets up a complete WordPress website with SocietyPress, the
membership management system for genealogical and historical societies.

It handles:
- Downloading and installing WordPress
- Creating the database (on cPanel hosting)
- Installing the SocietyPress plugin
- Installing the SocietyPress theme
- Basic configuration

REQUIREMENTS
------------
- Web hosting with PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.3+
- A web browser
- About 10 minutes of your time

HOW TO USE
----------
1. Upload sp-installer.php to your web hosting
   - Use your hosting's File Manager, or
   - Use an FTP program like FileZilla
   - Upload to the folder where you want WordPress installed
     (usually public_html or www)

2. Visit the file in your web browser
   Example: https://yourwebsite.com/sp-installer.php

3. Follow the on-screen instructions
   - The installer will guide you through each step
   - Have your hosting login ready if using cPanel

4. DELETE THE INSTALLER when finished!
   - Very important for security
   - The installer will remind you

TROUBLESHOOTING
---------------

"Permission denied" errors:
- Your hosting folder permissions may be too restrictive
- Contact your hosting provider or set folder permissions to 755

"Cannot connect to database":
- Double-check your database name, username, and password
- Make sure you've created the database in cPanel/hosting panel first

"Download failed":
- Your server may not allow external downloads
- Try again, or contact your hosting provider

cPanel automatic database creation not working:
- Use the "Manual Entry" tab instead
- Create the database manually in cPanel's MySQL Databases section

SUPPORT
-------
Documentation: https://getsocietypress.org/docs/
Support: https://getsocietypress.org/support/
GitHub: https://github.com/charles-stricklin/SocietyPress

================================================================================
                     Copyright 2024-2026 Stricklin Development
================================================================================
