<?php

use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class WorkersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $id = DB::table('workers')->insertGetId(array(
            'name' => 'Worker 1',
            'work_time_start' => '10:00:00',
            'work_time_end' => '19:00:00',
            'dinner_time_start' => '13:00:00',
            'dinner_time_end' => '14:00:00'
        ));
        DB::table('vacations')->insert(array(
            array(
                'worker_id' => $id,
                'begin' => '2018-01-11',
                'end' => '2018-01-25'
            ),
            array(
                'worker_id' => $id,
                'begin' => '2018-02-01',
                'end' => '2018-02-15'
            ),
        ));
        unset($id);
        $id2 = DB::table('workers')->insertGetId(array(
            'name' => 'Worker 2',
            'work_time_start' => '09:00:00',
            'work_time_end' => '18:00:00',
            'dinner_time_start' => '12:00:00',
            'dinner_time_end' => '13:00:00'
        ));

        DB::table('vacations')->insert(array(
            'worker_id' => $id2,
            'begin' => '2018-02-01',
            'end' => '2018-03-01'
        ));
        unset($id2);
    }
}
