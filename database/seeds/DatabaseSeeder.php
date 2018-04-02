
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
        DB::table('user_roles')->insert([
            'id_user' => '1',
            'id_role' => '1'
        ]);
        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'admin@tudip.com',
            'password' => bcrypt('tudip123'),
            'status' => 'Active',

            'image' => 'default.png',
            'phone_no' => '9826555777'
        ]);

        DB::table('roles')->insert(
            array(
                array(
                    'name' => 'Admin'
                ),
                array(
                    'name' => 'HR'
                ),
                array(
                    'name' => 'Candidate',
                ),
            ));

    }

}
