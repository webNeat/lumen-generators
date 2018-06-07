#!/usr/bin/env bash
parent_path=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )

cd "$parent_path"

# models
rm ./app/*.php 2> /dev/null

# migrations
rm ./database/migrations/*.php 2> /dev/null

# routes
echo "<?php

\$app->get(\"/\", function () use (\$app) {
    return \$app->welcome();
});" > ./app/Http/routes.php

echo "<?php
/*
|------------------------------------------
|   ***** DUMMY ROUTES FOR TESTING ONLY *****
|------------------------------------------
*/
" > ./routes/api.php

# Controllers
rm ./app/Http/Controllers/*.php 2> /dev/null
echo "<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    //
}
" > ./app/Http/Controllers/Controller.php

# factories
echo "<?php

\$factory->define(App\User::class, function (\$faker) {
    return [
        'name' => \$faker->name,
        'email' => \$faker->email,
        'password' => str_random(10),
        'remember_token' => str_random(10),
    ];
});
" > ./database/factories/ModelFactory.php

# database
rm ./database/database.sqlite 2> /dev/null
touch ./database/database.sqlite
