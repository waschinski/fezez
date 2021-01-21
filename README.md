# fezez
A self-hostable web app to share digital keys (e.g. bundle leftovers) with a group of friends

## Installation
A docker image is available on DockerHub: https://hub.docker.com/r/waschinski/fezez

You will also need a database accessable from your Fezez container. I only tested MySQL/MariaDB but any database supported by the Yii Framework should actually work. Note: There's use of UNIX_TIMESTAMP() in some queries so you will make sure the database you want to use also supports that.

In order to send emails, provide SMTP data. We are just sending emails, so a SMTP relay server would be fine.

Additionally, you will need to set up a couple environment variables on your container in order to get Fezez running.

Name | Description
------------ | -------------
DB_DSN | Data source name of your database
DB_USERNAME | Database user name
DB_PASSWORD | Database password
HOME_URL | your full qualified domain name including the URL scheme
MAIL_ADMIN | Your admin email used in outgoing emails
MAIL_SENDER | Your sender email used in outgoing emails
MAIL_SENDERNAME |  Your sender name used in outgoing emails
SECRETKEY | Random hash
COOKIEVALIDATIONKEY | Random hash
SMTP_SERVER | SMTP server address
SMTP_PORT | SMTP server port
SMTP_USERNAME | SMTP user name
SMTP_PASSWORD | SMTP password
SMTP_ENCRYPTION | SMTP encryption type
INVITATION_MANDATORY | Defines if users have to be invited before the can signup
DISCORD_WEBHOOK_URL | Your discord webhook URL if you want updates pushed to a Discord channel

You will also have to manually run the following commands on the container:
```bash
composer update
```
```bash
yii migrate
```
