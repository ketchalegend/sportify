# General info
This is a football (soccer :) match prediction game with a simple concept - you and your buddies battle it out to see who's best at predicting match final scores. Each player gives score predictions to upcoming matches, and then points are awarded check the [Game Flow](https://github.com/dev-labs-bg/sportify#game-flow). There is a Slack integration for notifications on events like standings, results and fixture updates. The application is fetching real-world football fixtures and scores via a free football API (http://api.football-data.org), but custom tournaments can be created from the [administration](https://github.com/dev-labs-bg/sportify#admin-user).  

# Requirements to run the project
* PHP, MySQL, Apache (or other web server ;)
* Symfony [system requirements](http://symfony.com/doc/current/reference/requirements.html)
* [composer](https://getcomposer.org/)
* [npm](https://docs.npmjs.com/getting-started/installing-node)
* [bower](https://bower.io/) & [gulp](http://gulpjs.com/) installed globally 

# Installation and Environment Setup
The project can be run without issues on both *nix and Windows (Mac OS X, Ubuntu and Windows with LAMP tested :). Check [the A to Z wiki article](https://github.com/dev-labs-bg/sportify/wiki/Setting-up-the-project-on-DigitalOcean-droplet-from-A-to-Z) for a step by step guide on how to prepare a 5$ 512MB DigitalOcean Ubuntu LAMP 16.04 droplet for the task.

The major steps needed to set-up Symfony and all the tools (for both development and production):

`composer install` - *for a basic install you just need username and password for mysql, all are written to _app/config/parameters.yml_ check here for those the advanced parameters.*  
`npm install` *(if running on a machine with 0.5/1GB RAM add swap)*  
`bower install` *(you may need --allow-root as a parameter if running as sudo)*  
`gulp`

We suggest that you setup your web server to use `web/` as root directory.
The app has two main environments:
* Development (**dev**):
    + Accessed by navigating to `app_dev.php` - can only be accessed locally (from 127.0.0.1, a.k.a. localhost)
    + Debug Mode is enabled, so you have key development-friendly features like access to stack traces on error pages and auto/dynamic rebuilding of cache files on each request. The latter meaning that you don't have to clear the app's cache every time you change something.
* Production (**prod**):
    + Accessed by navigating to `app.php`
    + By default, when you access `/` you are redirected to `app.php` (configured in `.htaccess`).
    + Debug Mode is disabled. The main thing is that every time a change is made to the app (routing, templates, parameters, etc.), it will not take effect until the cache is manually cleared by using this command: `php bin/console cache:clear --env=prod` (have this in mind if creating a deployment script).

# Application parameters and initial setup
* Database schema update (migrations):
    + `php bin/console doctrine:schema:update --force`
* Create your admin user (follow the steps):
    + `php bin/console fos:user:create`
* Give yourself Admin access from the command line: 
    + `php bin/console fos:user:promote your-username ROLE_ADMIN`
* You can now access the web interface :)
* If the emails are not configured (SMTP credentials not set), users will not abe able to make a registration by themselves because sending confirmation emails will not be possible. In this case you can fallback to creating user accounts with `php bin/console fos:user:create`

**N.B.**
After doing any changes on **app/config/parameters.yml** run `php bin/console cache:clear --env=prod` for them to take effect.

### Changing advanced app settings (app/config/parameters.yml):
 
`mailer_host`, `mailer_port`, `mailer_user`, `mailer_password` - SMTP settings in order to have sending of registration confirmation and password reset emails enabled.

Best practice is to use a transactional mail service, like Mailgun, Amazon SES, etc. We recommend Mailgun as registering an account takes a few minutes and the free tier should be enough. Another option is to use [Gmail SMTP settings](https://www.digitalocean.com/community/tutorials/how-to-use-google-s-smtp-server).  

 `football_api.token` - you can get one free from [football-data.org](http://www.football-data.org/register). If you use the service for a longer time, consider [donating](http://api.football-data.org/about) :)  
` slack.url, slack.channel` - Generate them in [Slack](https://slack.com/apps/A0F7XDUAZ-incoming-webhooks) in order to get notifications.  
`secret` - Symfony variable - generate it by going [here](http://nux.net/secret) or running in shell `openssl rand -hex 20`

**N.B.**
After doing any changes on **app/config/parameters.yml** run `php bin/console cache:clear --env=prod` for them to take effect.

### Initial application data setup (admin only)

Once the App is up-and-running you have to create at least one tournament, so that users are able to join it when they register and login.

Create tournament by navigating to: **Admin Panel -> Tournaments**. After the tournament is created, you have to options:

* Automatic updating of teams, match fixtures/results data (via API)
    + Create tournament-to-API mapping (**Admin Panel -> API Mappings**) - when you acces this page you should see a list of tournaments and their IDs, dynamically fetched from the football API. You can also view the football API's tournament list from [here](http://api.football-data.org/v1/competitions)
    + API fetch - update teams (**Admin Panel -> Data Updates -> Update Teams**) - adds/updates teams for all tournaments with API mappings.
    + API fetch - update fixtures (**Admin Panel -> Data Updates -> Update Match Fixtures**) - adds/updates upcoming fixtures for specified period for all tournaments with API Mappings.

* Manual updating of teams, match fixtures/results data
    + Manual teams add or change (**Admin Panel -> Teams**)
    + Manual fixture/result add or change (**Admin Panel -> Matches**)

# Game flow

### Regular user
* Join tournament (**Tournaments**)
* Make predictions:
    + tournament champion team (**Predictions -> Champion**) - this can be changed until a deadline (usually this is the tournament's start date).
    + match results (**Predictions -> Matches**) - each match prediction form is locked when the match starts.
* Check match results for finished and scored matches (**History**):
    + you can see final results for finished matches and how many points you gained, according to your prediction(s).
    + you can also choose to see other users' predictions for finished matches.
* Check user standings for each tournament (**Standings**)

### Admin user
* Create/edit tournaments (**Admin panel -> Tournaments**)
* Create/edit tournament-to-API mappings (**Admin panel -> API Mappings**) - this is required for any tournament for which you want to fetch teams, matches data via API.
* Update teams data:
    + Automatic via API fetch (**Admin Panel -> Data Updates -> Update Teams**)
    + Manual (**Admin panel -> Teams**) - adding new teams or change/update names, logos.
* Update match fixtures:
    + Automatic via API fetch (**Admin Panel -> Data Updates -> Update Match Fixtures**)
    + Manual (**Admin Panel -> Matches**)
* Update match results:
    + Automatic via API fetch (**Admin Panel -> Data Updates -> Update Match Results**) - if new data is fetched, prediction points and standings are updated
    + Manual (**Admin Panel -> Matches**)
* Standings/scores updates (**Admin Panel -> Scores/Standings Update**) - required only when manually updating match results.
* Updating of match fixtures and results via API fetch can be scheduled by using Cron the following commands:

    `php bin/console --env=prod sportify:data:update matches-fixtures <days>` - get match fixtures for the next number of *days*
    
    `php bin/console --env=prod sportify:data:update matches-results <days>` - get match results for the previous number of *days*
* Send notifications to users which have not made a prediction for upcoming matches (in the next 2 hours) in tournaments they have joined:
    
    `php bin/console --env=prod sportify:notify users-not-predicted`

* Example crontab entries (assuming your project folder is: /var/www/sportify):
    + Every hour at 5 and 35 minutes of the clock, check for users which have not made their predictions for upcoming matches and notify them (and log this to log_notify.txt):
    
    `5,35 * * * *    php /var/www/sportify/bin/console --env=prod sportify:notify users-not-predicted >> /var/www/sportify/var/log_notify.txt`
    + Every Monday at 8:00 AM, fetch matches fixtures for the next 14 days (and log this to log_data_updates.txt)
    
    `0 8 * * 1       php /var/www/sportify/bin/console --env=prod sportify:data:update matches-fixtures 14 >> /var/www/sportify/var/log_data_updates.txt`
    + Every day at 1:00 AM, fetch matches results for the previous 1 day. If new data is fetched, calculate user prediction points and update tournaments' standings (and log this to log_data_updates.txt)
    
    `0 1 * * *       php /var/www/sportify/bin/console --env=prod sportify:data:update matches-results 1 >> /var/www/sportify/var/log_data_updates.txt`

### Scoring system - what points are awarded for what
* **3 points** for exact match score prediction, examples:
    + You predicted 1-0, final score 1-0
    + You predicted 1-2, final score 1-2
    + You predicted 3-3, final score 3-3
* **1 point** for match outcome predicted, examples:
    + You predicted 1-0, final score 3-1  (home team win)
    + You predicted 1-2, final score 0-2  (away team win)
    + You predicted 2-2, final score 1-1  (draw)
* **0 points** for wrong match score/outcome prediction :)
* **5 points** for correct prediction of tournament champion/winner (so, assigned just once for each tournament)

# Contribution

We accept all kind of contributions that you guys make and we'll love you for them! <3

If you find any problems, have any suggestions or want to discuss something you can either open an issue here or make a pull request with code changes instead.

If you want to contribute, but you're not sure where to start you can always take a look at the open issues we have and pick any of them.

Try to follow our conventions for naming issues, branches and existing code structure and conventions.
