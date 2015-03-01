# bighub-brother IS WATCHING YOU

:warning: this is work in progress!

Be notified when someone edits some specific files in your GitHub organization.

## Why?

You don't want to watch a repo because it's too noisy.

Maybe you're the front-end guy at your job, but back-end devs edit some CSS and JS from time to time, without letting you know. You want notifications for those moments.

## Install

This works as a GitHub **organization** webhook.

* set this up on a server so that GitHub can access the `webhook.php` file.
* `composer install`

### Setting the Webhook

Follow GitHub doc to [set up the webhook](https://developer.github.com/webhooks/creating/#setting-up-a-webhook) and [secure the webhook](https://developer.github.com/webhooks/securing/).

In `production` mode, bighub-brother needs a `WEBHOOK_SECRET` env variable to work. It must contain the *secret* you entered when setting up the webhook.

In `dev`, it looks for an example push payload in `examples/push.json`.

### Create the config file

The most important file is `config/config.json`. An example config file, with all the available options listed and commented, is `config/config-example.js`.

Create the `config/config.json` and set it up as you wish (note: comments are not allowed in this file).

### Done!

Now when someones pushes some stuff in your organization, e-mails will be sent accordingly to notify people.
