<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => bcrypt(str_random(10)),
        'remember_token' => str_random(10),
        'default_game_type_id' => function () {
            return factory(App\GameType::class)->create()->id;
        },
    ];
});

$factory->define(App\GameType::class, function (Faker\Generator $faker) {
    return [
        'type_name' => str_random(10),
        'time_on_turn' => rand(15, 60),
        'is_rating' => $faker->boolean(),
    ];
});

$factory->define(App\Game::class, function (Faker\Generator $faker) {
    return [
        'game_type_id' => function () {
            return factory(App\GameType::class)->create()->id;
        },
        'private' => $faker->boolean(),
        'winner' => $faker->boolean(),
        'bonus' => 0,
    ];
});

$factory->define(App\UserIngameInfo::class, function (Faker\Generator $faker) {
    return [
        'game_type_id' => function () {
            return factory(App\GameType::class)->create()->id;
        },
        'user_id' => function () {
            return factory(App\User::class)->create()->id;
        },
        'game_rating'=>rand(100, 2000),
        'games'=> rand(5, 20),
        'wins'=> rand(5, 20),
    ];
});
