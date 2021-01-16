Gotify Plugin for Kanboard
===========================
*(Based on Hipchat, Telegram and [Signal](https://github.com/bhopmann/kanboard-plugin-signal) Plugin)*

Receive Kanboard notifications on [Gotify](https://gotify.net/).

Developed using [Kanboard](https://kanboard.org) Version 1.2.18

Author
------

- Benedikt Hopmann
- License MIT
- Gotify Logo from https://avatars3.githubusercontent.com/u/36410427?s=200&v=4

Requirements
------------

- Kanboard >= 1.0.37
- Existing [Gotify](https://github.com/gotify/server) server

Installation
------------

You have the choice between two methods:

1. Download the zip file and decompress everything under the directory `plugins/Gotify`
2. Clone this repository into the folder `plugins/Gotify`

Note: Plugin folder is case-sensitive.

Configuration
-------------

### Gotify Plugin Settings

Firstly, you need to set up the Gotify Plugin, then configure Kanboard.

Go to **Settings > Integrations > Gotify** and fill the forms:

- **Gotify URL**: URL of Gotify server
- **Gotify Token**: Token of Gotify Application
- **Gotify Priority**: Set Notification Priority

### Receive individual user notifications

- Go to your user profile settings then choose **Integrations > Gotify**
- *Optionally* make different specifications of Gotify URL, Gotify Token and Gotify Priority
- Then enable Gotify notifications in your profile: **Notifications > Select Gotify**

### Receive project notifications to a group

- Go to the project settings then choose **Integrations > Gotify**
- *Optionally* make different specifications of Gotify URL, Gotify Token and Gotify Priority
- Then enable Gotify notifications in your profile: **Notifications > Select Gotify**

Troubleshooting
---------------

- Enable the PHP debug mode
- All errors are recorded in the logs
- Enable verbose mode in file `plugins/Gotify/Notification/Gotify.php` (`$gotify_verbose = true;`)
