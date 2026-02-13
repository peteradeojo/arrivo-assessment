# Arrivo Savings API

## Setup

Run the following commands to setup this project

```bash
$ git clone https://github.com/peteradeojo/arrivo-assessment.git
$ cd arrivo-assessment
$ cp .env.example .env
$ composer install
$ php artisan key:generate
$ php artisan migrate
$ php artisan db:seed
```

## Testing
To run tests

```bash
$ php artisan test
$ php artisan test --coverage // to view coverage report, requires XDebug (or any other php debugger)
```

## Notes on Security

- **Authentication** - [Laravel Sanctum](https://laravel.com/docs/12.x/sanctum) for provisioning JWT authentication
- **Authorization** - Role-based access implemented using [Laravel Permissions](https://spatie.be/docs/laravel-permission/v6/introduction)
- **Rate Limiting** - Critical API endpoints throttled using the `throttle` middleware

## Notes on Performance
- **NB: Response caching (with Redis) was not implemented as a part of this assessment due to time constraints.**
- For Admin GET endpoints, used pagination to improve response time. NB: Consider using cursor pagination in place of OFFSET pagination
- Indexes defined on the following table columns to ensure integrity and improve performance

    | Table | Column(s) | Index |
    |------|-----|---|
    | friendship | user_id, friend_id | Unique | 
    | saving_group_members | user_id, group_id | Unique | 
