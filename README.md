# Monitorizare Vot - Rest API

[![GitHub contributors](https://img.shields.io/github/contributors/code4romania/monitorizare-vot-votanti-api.svg?style=for-the-badge)](https://github.com/code4romania/monitorizare-vot-votanti-api/graphs/contributors) [![GitHub last commit](https://img.shields.io/github/last-commit/code4romania/monitorizare-vot-votanti-api.svg?style=for-the-badge)](https://github.com/code4romania/monitorizare-vot-votanti-api/commits/master) [![License: MPL 2.0](https://img.shields.io/badge/license-MPL%202.0-brightgreen.svg?style=for-the-badge)](https://opensource.org/licenses/MPL-2.0)

[See the project live](http://monitorizarevot.ro/)

Monitorizare Vot is a mobile app for monitoring elections by authorized observers. They can use the app in order to offer a real-time snapshot on what is going on at polling stations and they can report on any noticeable irregularities. 

The NGO-s with authorized observers for monitoring elections have real time access to the data the observers are transmitting therefore they can report on how voting is evolving and they can quickly signal to the authorities where issues need to be solved. 

Moreover, where it is allowed, observers can also photograph and film specific situations and send the images to the NGO they belong to. 

The app also has a web version, available for every citizen who wants to report on election irregularities. Monitorizare Vot was launched in 2016 and it has been used for the Romanian parliamentary elections so far, but it is available for further use, regardless of the type of elections or voting process. 

[Contributing](#contributing) | [Built with](#built-with) | [Repos and projects](#repos-and-projects) | [Deployment](#deployment) | [Feedback](#feedback) | [License](#license) | [About Code4Ro](#about-code4ro)

## Contributing

This project is built by amazing volunteers and you can be one of them! Here's a list of ways in [which you can contribute to this project](.github/CONTRIBUTING.MD).

## Built With

Uses Laravel 5.2

Main packages:
* JWT-Auth - tymondesigns/jwt-auth
* Dingo API - dingo/api
* Laravel-CORS barryvdh/laravel-cors

### Requirements

* PHP 7+ (with mbstring, openSSL extensions);
* Apache or Nginx (mod_rewrite required);
* MySQL;

## Repos and projects

Related projects:

- client app - https://github.com/code4romania/monitorizare-vot-votanti-client/
- admin app - https://github.com/code4romania/monitorizare-vot-votanti-admin

Other MV related repos:

- https://github.com/code4romania/monitorizare-vot-admin
- https://github.com/code4romania/monitorizare-vot-ong
- https://github.com/code4romania/monitorizare-vot
- https://github.com/code4romania/monitorizare-vot-android
- https://github.com/code4romania/monitorizare-vot-ios
- https://github.com/code4romania/monitorizare-vot-docs

## Deployment

### Services
In /tools/docker you can find a docker compose file that starts a php server with apache, a mysql server and a phpmyadmin instance.
You will need to have **docker** and **docker-compose**(https://docs.docker.com/compose/) installed.

To start the services, go to the tools/docker folder and run:

```bash
docker-compose up -d
```

### Project setup

* Install prerequisites
```bash
composer install
```

* Configurations

Initial `.env` setup
```bash
cp .env.docker .env
```

Add new local keys
```bash
php artisan key:generate //Not sure if really neccesary
php artisan jwt:generate
```

Run DB scrips
```bash
php artisan migrate --seed
```

* Run the local server with

```bash
php artisan serve
```

* Test your storage folders structure

    Sometimes these folders need to be created manually if the user doesn't have the rights to do so:
    * /storage/framework/cache
    * /storage/framework/sessions
    * /storage/framework/views
    * /storage/logs

Access [http://localhost:8000](http://localhost:8000) or [http://localhost:8000/api/check](http://localhost:8000/api/check) in the browser to test the project. For documentation you can access [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)

* (Optional) Swagger
 
To generate the swagger files
```bash
php artisan l5-swagger:publish
php artisan l5-swagger:generate
```

Once the files are generated you can access the swagger documentation at:
[http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)

### Testing it works

Once everything is built and started you can access the webservice at http://localhost:3200 and the phpmyadmin at http://localhost:3201 .
If you are running Linux then you can use the direct IPs as well ( this does not work for Mac or Windows ).

To list the container do:
```bash
docker ps
```

The containers can be accessed by:
```bash
docker exec -it <container_name> bash
```

You can run composer and php commands from inside the container.

To add special configs to the PHP ini inside the container you can modify [the config file](./tools/docker/web/config/custom-php-configs.ini).

## Project structure

* Controllers in /app/Api/V1/Controllers
* Routing in /app/Http/api_routes.php
* See Laravel and Dingo API documentation for more.
* Also: https://www.sitepoint.com/how-to-build-an-api-only-jwt-powered-laravel-app/

## Main Features

### AuthController

* _login()_;
* _signup()_;
* _recovery()_;
* _reset()_;

You will need:

* _login_: just email and password;
* _signup_: whatever you like: you can specify it in the config file;
* _recovery_: just the user email address;
* _reset_: token, email, password and password confirmation;

### IncidentController Endpoint

* _GET /api/incidents_ ( get last 20 incidents )
* _GET /api/incidents?limit=100_ (Get more incidents)
* _GET /api/incidents?limit=10&page=3+ (limit parameter is optional, will default to 20)
* _GET /api/incidents?status[]=Pending_ (filter by state)
* _GET /api/incidents?status[]=Pending&status[]=Rejected_ (get pending and rejected)
* _POST /api/incidents_ (Create incident)

* _PUT /api/incidents/5/approve_ (Approve incident - ADMIN)
* _PUT /api/incidents/5/reject_ (Reject incident - ADMIN)
* _DELETE /api/incidents/5_ (Delete incident - ADMIN) 

### A Separate File for Routes

You can specify your routes in the `api_routes.php` file, that will be automatically loaded. In this file you will find many examples of routes.

### Secrets Generation

Every time you create a new project starting from this repository, the _php artisan jwt:generate_ command will be executed.

### Configuration

As I already told before, this boilerplate is based on _dingo/api_ and _tymondesigns/jwt-auth_ packages. So, you can find many informations about configuration <a href="https://github.com/tymondesigns/jwt-auth/wiki/Configuration" target="_blank">here</a> and <a href="https://github.com/dingo/api/wiki/Configuration">here</a>.

However, there are some extra options that I placed in a _config/boilerplate.php_ file.

* **signup_fields**: you can use this option to specify what fields you want to use to create your user;
* **signup_fields_rules**: you can use this option to specify the rules you want to use for the validator instance in the signup method;
* **signup_token_release**: if "true", an access token will be released from the signup endpoint if everything goes well. Otherwise, you will just get a _201 Created_ response;
* **reset_token_release**: if "true", an access token will be released from the signup endpoint if everything goes well. Otherwise, you will just get a _200_ response;
* **recovery_email_subject**: here you can specify the subject for your recovery data email;

### Creating Endpoints

You can create endpoints in the same way you could to with using the single _dingo/api_ package. You can <a href="https://github.com/dingo/api/wiki/Creating-API-Endpoints" target="_blank">read its documentation</a> for details.

After all, that's just a boilerplate! :)

### Cross Origin Resource Sharing

If you want to enable CORS for a specific route or routes group, you just have to use the _cors_ middleware on them.

Thanks to the _barryvdh/laravel-cors_ package, you can handle CORS easily. Just check <a href="https://github.com/barryvdh/laravel-cors" target="_blank">the docs at this page</a> for more info.

## Feedback

* Request a new feature on GitHub.
* Vote for popular feature requests.
* File a bug in GitHub Issues.
* Email us with other feedback contact@code4.ro

## License

This project is licensed under the MPL 2.0 License - see the [LICENSE](LICENSE) file for details

## About Code4Ro

Started in 2016, Code for Romania is a civic tech NGO, official member of the Code for All network. We have a community of over 500 volunteers (developers, ux/ui, communications, data scientists, graphic designers, devops, it security and more) who work pro-bono for developing digital solutions to solve social problems. #techforsocialgood. If you want to learn more details about our projects [visit our site](https://www.code4.ro/en/) or if you want to talk to one of our staff members, please e-mail us at contact@code4.ro.

Last, but not least, we rely on donations to ensure the infrastructure, logistics and management of our community that is widely spread across 11 timezones, coding for social change to make Romania and the world a better place. If you want to support us, [you can do it here](https://code4.ro/en/donate/).
