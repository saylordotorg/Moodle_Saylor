![Accredible Logo](https://s3.amazonaws.com/accredible-cdn/accredible_logo_sm.png)

# Accredible Moodle Activity Plugin

## Overview
The Accredible platform enables organizations to create, manage and distribute digital credentials as digital certificates or open badges.

An example digital certificate and badge can be viewed here: https://www.credential.net/10000005

This plugin enables you to issue dynamic, digital certificates or open badges on your Moodle instance. They act as a replacement for the PDF certificates normally generated for your courses.

Here's a video showing a tutorial on how to install and start using the plugin: https://youtu.be/h0ORng5TBnU

## Example Output
![Example Digital Certificate](https://s3.amazonaws.com/accredible-cdn/example-digital-certificate.png)

![Example Open Badge](https://s3.amazonaws.com/accredible-cdn/example-digital-badge.png)

## Compatability

This plugin has been tested and is working on Moodle 2.7+ and Moodle 3.1+.

---

## Plugin Installation

There are two installation methods that are available. Follow one of these, then log into your Moodle site as an administrator and visit the notifications page to complete the install.

#### Git

If you have git installed, simply visit the Moodle /mod directory and clone this repo:

    git clone https://github.com/accredible/moodle-mod_accredible.git accredible

#### Download the zip

1. Visit https://github.com/accredible/moodle-mod_accredible and download the zip. 
2. Extract the zip file's contents and **rename it 'accredible'**. You have to rename it for the plugin to work.
3. Place the folder in your /mod folder, inside your Moodle directory.

#### Get your API key

Make sure you have your API key from Accredible. It's available from the settings page on our dashboard: [https://dashboard.accredible.com](https://dashboard.accredible.com).

#### Continue Moodle set up

Start by installing the new plugin (go to Site Administration > Notifications if your Moodle doesn't ask you to install automatically).

![install-image](https://s3.amazonaws.com/accredible-moodle-instructions/install_plugin.png "Installing the plugin")

After clicking 'Upgrade Moodle database now', this is when you'll enter your API key from Accredible.

![api-image](https://s3.amazonaws.com/accredible-moodle-instructions/set_api_key.png "Enter your Accredible API key")

## Creating a Certificate or Badge

#### Add an Activity

Go to the course you want to issue certificates or badges for and add an Accredible activity. First select add activity:

![add-activity](https://s3.amazonaws.com/accredible-moodle-instructions/add_activity1.png)

then select Accredible:

![select-accredible](https://s3.amazonaws.com/accredible-moodle-instructions/add_activity2.png)

Issuing a certificate or badge is easy - choose from 3 issuing options:

- Pick student names and manually issue credentials. Only students that don't already have a credential will show a checkbox.
- Choose the Quiz Activity that represents the **final exam**, and set a minimum grade requirement. Certificates/Badges will get issued as soon as the student receives a grade above the threshold.
- Choose for a student to receive their certificate/badge when they complete the course if you've setup completion tracking.

![settings-image](https://s3.amazonaws.com/accredible-moodle-instructions/activity_settings2.png "Choose how to issue certificates")

*Note: if you set both types of auto-issue criteria, completing either will issue a certificate/badge.*

*Note: Make sure you don't allow students to set their completion of the Accredible activity or they'll be able to issue their own certificates/badges.*

Once you've added the activity to your course we'll auto-create a Group on your Accredible account where these credentials will belong. You'll see this on your dashboard.

![new-group](https://s3.amazonaws.com/accredible-moodle-instructions/new_group.png "Group on Accredible")

Then select a certificate design and or badge design to be able to send out credentails in this group.

![credentials-list](https://s3.amazonaws.com/accredible-moodle-instructions/credentials_list.png "List of certificates and badges")

From now on new certificates and badges will be automatically sent to recipients based upon the criteria you chose.

You are able to add, edit and remove your badges and certificates at any time through the platform.

**Contact us at support@accredible.com if you have issues or ideas on how we can make this integration better.**

### Bug reports

If you discover any bugs, feel free to create an issue on GitHub. Please add as much information as possible to help us fixing the possible bug. We also encourage you to help even more by forking and sending us a pull request.

https://github.com/accredible/acms-php-api/issues

## FAQs

#### Why is nothing showing up? I can't see a certificate.

A certificate isn't created until you've either manually created one or had a student go through the criteria you set on the activity. For example if you select some required activities then a certificate won't be created until an enrolled student has completed them. Completing an activity or quiz as a course admin won't create a certificate.

---

## Development Information

### Coding style

This plugin is trying to be consistent and follow the recommendations according to [the Moodle coding style](http://docs.moodle.org/dev/Coding_style).

### Development setup

#### Prerequisites

- [Docker](https://www.docker.com/)
- [An Accredible account](https://www.accredible.com/)

#### Step 1: Initial installation

Run `docker-compose.yml` without any plugins for the first time to successfully complete initial installation.

```
docker-compose up -d
```

If the initial installation is successfully completed, `==> ** Moodle setup finished! **` will be displayed in the docker log and you will be able to access the moodle instance at `http://127.0.0.1:8080`.

After the installation, you can stop the containers to re-run them with the Accredible plugin.

```
docker-compose down
```

#### Step 2: Run Moodle with the Accredible plugin

Run the Moodle instance with the Accredible plugin in your local repo.

```
docker-compose -f docker-compose.yml -f docker-compose.plugin.yml up -d
```

If you are using your Accredible account in the production, you need to set an empty value in `ACCREDIBLE_DEV_API_ENDPOINT` in `docker-compose.plugin.yml`. Otherwise, `http://127.0.0.1:3000/v1/` is used for the API calls.

#### /opt/bitnami/moodle/config.php: No such file or directory

The following error is raised if the moodle service has not completed the initial installation:

```
moodle_1      | grep: /opt/bitnami/moodle/config.php: No such file or directory
```

Please make sure if the initial installation has been completed.

If the error keeps happening, it would be better to clear the containers with the following commands:

```
docker-compose down -v
rm -rf .docker/volumes/mariadb/data
```

and set it up again from the beginning.

### Moodle instance

You can access the Moodle instance at `http://127.0.0.1:8080` and log into the admin page with:

```
MOODLE_USERNAME: user
MOODLE_PASSWORD: bitnami
```

### phpMyAdmin

You can access the phpMyAdmin at `http://127.0.0.1:8081` and log into it with:

```
MOODLE_DATABASE_USER : bn_moodle
MOODLE_DATABASE_PASSWORD: (No password)
```

### Environment variables

You can find available environment variables on [README.md](https://github.com/bitnami/bitnami-docker-moodle) of the original docker-compose repository from bitnami.
