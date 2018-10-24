<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Users
        $user = new \App\Models\User();
        $user->name = "admin";
        $user->email = str_random(10).'@tageso.de';
        $user->password = hash('sha512', 'secret');
        $user->status = "active";
        $user->admin = true;
        $user->mailStatus = "disabled";
        $user->saveOrFail();

        $userProfile = new \App\Models\UserProfile();
        $userProfile->user_id = $user->id;
        $userProfile->username = $user->name;
        $userProfile->saveOrFail();

        $adminUser = $user;

        for($i = 0; $i <= 200; $i++) {
            $user = new \App\Models\User();
            $user->name = str_random(10);
            $user->email = str_random(10).'@tageso.de';
            $user->password = hash('sha512', 'secret');
            $user->status = "active";
            $user->admin = true;
            $user->mailStatus = "disabled";
            $user->saveOrFail();

            $userProfile = new \App\Models\UserProfile();
            $userProfile->user_id = $user->id;
            $userProfile->username = $user->name;
            $userProfile->saveOrFail();
        }

        // Organisations
        for($i = 0; $i <= 100; $i ++ ) {
            $organisation = new \App\Models\Organisations();
            $organisation->user_id = $adminUser->id;
            $organisation->name = str_random(random_int(10,30));
            $organisation->saveOrFail();

            $userOrganisation = new \App\Models\UserOrganisations();
            $userOrganisation->user_id = $adminUser->id;
            $userOrganisation->organisation_id = $organisation->id;
            $userOrganisation->access = True;
            $userOrganisation->read = random_int(0, 1);
            $userOrganisation->comment = random_int(0, 1);
            $userOrganisation->edit = random_int(0, 1);
            $userOrganisation->protocol = random_int(0, 1);
            $userOrganisation->admin = random_int(0, 1);
            $userOrganisation->notification_protocol = False;
            $userOrganisation->saveOrFail();
        }

        // Categories
        for($i = 0; $i <= 500; $i++) {
            $category = new \App\Models\Categories();
            $category->name = str_random(random_int(10, 30));
            $category->status = "active";
            $category->organisation_id = random_int(1, 100);
            $category->calculateNexFreePosition();
            $category->user_id = 1;
            $category->saveOrFail();
        }

    }
}
