# bighub-brother IS WATCHING YOU

:warning: this is work in progress!

Be notified when someone edits some specific files in your GitHub organization.

You can choose to be notified per repo, per committer and per file pattern.

## Why?

You don't want to watch a repo because it's too noisy.

Maybe you're the front-end guy at your job, but back-end devs edit some CSS and JS from time to time, without letting you know. You want notifications for those moments.

## Install

This works as a GitHub **organization** webhook.

There are two ways to install this: *1.* directly clone this project, or *2.* make your own with bighub-brother as a dependency.

First solution is straight-forward but doesn't let you track your `config.json` file with git, unlike the second one.

### 1) Install by direct cloning…

* clone this and set this up on a server so that GitHub can access the `webhook.php` file.
* `composer install`
* [set the webhook](#2-setting-the-webhook)
* [create the config file](#3-create-the-config-file)

### 1) …or as a dependency

* create a new project and add bighub-brother as a dependency with composer. For now the package isn't registered in packagist so you have to add the repo in your `composer.json`:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/Leimi/bighub-brother"
    }
],
"require": {
    "Leimi/bighub-brother": "dev-master"
}
```

* `composer install`
* create a `webhook.php` page at root of your project and instanciate a BigHubBrother class with your settings. Here is an example with the default options set:

```php
<?php
// webhook.php
require 'vendor/autoload.php';

$BigHubBrother = new BigHubBrother\BigHubBrother([
    //path of your json config file
    'config' => __DIR__ . '/../config/config.json',
    //path of a github push payload example to use in not in production (based on APPLICATION_ENV env variable)
    'devExample' => __DIR__ . '/../examples/push.json',
    //name of the en variable where the webhook secret is stored
    'secret_env' => 'WEBHOOK_SECRET'
]);
?>
```


### 2) Setting the Webhook

Follow GitHub doc to [set up the webhook](https://developer.github.com/webhooks/creating/#setting-up-a-webhook) and [secure the webhook](https://developer.github.com/webhooks/securing/).

In `production` mode (when env variable `APPLICATION_ENV === production`), bighub-brother needs a `WEBHOOK_SECRET` env variable to work. It must contain the *secret* you entered when setting up the webhook.

In `dev`, it looks for an example push payload in `examples/push.json`.

### 3) Create the config file

The most important file is `config/config.json`. An example config file, with all the available options listed and commented, is `config/config-example.js`.

Create the `config/config.json` and set it up as you wish (note: comments are not allowed in this file).


### 4) Done!

Now when someones pushes some stuff in your organization, e-mails will be sent accordingly to notify people.
