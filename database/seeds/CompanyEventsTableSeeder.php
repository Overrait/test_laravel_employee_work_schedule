<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyEventsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('company_events')->insert(array(
            'name' => 'corporate',
            'begin' => '2018-01-10 15:00:00',
            'end' => '2018-01-11 00:00:00'
        ));
    }
}
