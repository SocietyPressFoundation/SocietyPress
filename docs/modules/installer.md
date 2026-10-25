# Installer

The fastest way from "I heard about SocietyPress" to "our site is running on it": one PHP file, `sp-installer.php`, that you upload to your web host and open in a browser.

For a walk-through with screenshots, see the [Installation page on getsocietypress.org](https://getsocietypress.org/docs/installation/). This guide is the short version.

## What it does

In a few minutes on most cPanel hosts, the installer:

1. Checks your host meets the requirements, and tells you plainly what to fix if it doesn't.
2. Tests your database details.
3. Downloads the latest WordPress and writes its configuration file, with fresh security keys.
4. Downloads SocietyPress — the plugin, the parent theme and all five child themes.
5. Installs WordPress, creates your administrator account, and turns on SocietyPress and its theme.
6. Fills in your society's name, address, phone, email and membership year.
7. Imports your membership list, if you gave it one.
8. Deletes itself, signs you in, and opens the [Setup Wizard](setup-wizard.md).

## What you need before starting

- **A web hosting account.** Ordinary shared cPanel hosting is fine.
- **A domain name** pointed at that hosting.
- **PHP 8.0 or newer.** Most hosts let you choose under cPanel's **MultiPHP Manager** or **Select PHP Version**.
- **An empty MySQL database.** Create one under cPanel's **MySQL Databases**, add a user to it with all privileges, and note the database name, username and password. The host is almost always `localhost`.

## How to run it

1. **Download the installer** from [getsocietypress.org/sp-installer.php](https://getsocietypress.org/sp-installer.php) and keep the name `sp-installer.php`.
2. **Upload it** to the folder your domain serves — usually `public_html` — with cPanel's **File Manager** or an FTP program.
3. **Open it in a browser**: `https://yourdomain.org/sp-installer.php`. If your host fails a requirement, the page says which and how to fix it.
4. **Fill in the form:**
   - **Database** — name, username, password, host, and table prefix (leave the prefix as it is unless you know you need another).
   - **Society Name**, and your **Admin Email**, first and last name, **Admin Username** and **Admin Password**.
   - Optional: the society's **Mailing Address**, **Phone Number** and **Contact Email**.
   - **Membership Period** — annual (a fixed year), rolling (12 months from joining) or lifetime only — and the month your **Fiscal Year Starts**.
   - Optional: a **Membership File** — your member list saved as CSV. An EasyNetSites member export works as it is.
5. **Click Install** and watch the progress list.
6. **You're signed in** and taken to the Setup Wizard.

## How it stays safe

- **It deletes itself** when it finishes, so nothing is left behind for anyone else to run.
- **Downloads come over HTTPS** from wordpress.org and getsocietypress.org (or SocietyPress's GitHub releases if that fails), and each download is checked to be a complete ZIP before it's unpacked.
- **Your database password goes only into `wp-config.php`**, where WordPress needs it. It isn't stored in the session or sent anywhere else.
- **One small report**: when someone opens the installer, it tells getsocietypress.org the site's address and whether the requirements check passed — nothing else — so the project knows when hosts are turning people away.

The installer is in the repository at `Code/installer/sp-installer.php` if you'd like to read it first.

## Installing without the installer

If your host won't run an uploaded PHP file, or you'd rather do it yourself:

1. Install WordPress with your host's usual one-click WordPress installer.
2. Download the bundle from `https://getsocietypress.org/downloads/societypress-latest.zip` and unzip it.
3. Upload the `societypress` plugin folder to `wp-content/plugins/`, and the theme folders to `wp-content/themes/`.
4. In WordPress, **Plugins → SocietyPress → Activate**, then activate the SocietyPress theme or one of the five child themes.
5. The Setup Wizard opens the next time you visit the admin.

## Updates

SocietyPress checks for new releases itself. The plugin and its themes update automatically, the same way WordPress updates itself; you can also update from **Dashboard → Updates** as soon as one is announced. After an update, the first admin page you open brings the database up to date. Nothing is lost.

The plugin, the parent theme and the five child themes always carry the same version number and update together.

If your host blocks automatic updates, download the latest bundle and upload the new `societypress` plugin folder over the old one.

## If something looks wrong

**The installer page is blank or shows code.** PHP is off or older than 8.0. Set the domain to PHP 8.0 or newer in cPanel and reload.

**"Cannot connect to the database."** Check the name, username and password. Most cPanel hosts put your account name in front of database and user names (`youraccount_society`), and the host is almost always `localhost`. Make sure the user was added to the database with all privileges.

**"Cannot write files."** The folder you uploaded to must be writable by your site. In File Manager, set its permissions to 755, or ask your host.

**Pages other than the home page show "Not Found".** In the WordPress admin, go to **Settings → Permalinks** and click **Save Changes** without changing anything.

**A feature you expected isn't in the sidebar.** Most features start turned on, but a few — such as Paid Research Services — start off. Turn them on under **Settings → Features**.

## Related guides

- [Setup Wizard](setup-wizard.md) — what happens after the installer hands off
- [Members](members.md) — usually the next thing you'll do
- [Child Themes](child-themes.md) — choosing your site's look
