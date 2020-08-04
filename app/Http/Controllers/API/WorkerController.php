<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;

use App\Worker;
use App\ProductionCalendar;
use App\CompanyEvents;

class WorkerController extends Controller
{
    public function view_schedule(Request $request, $id) {
        $data = array();
        //на ближайшие 2 дня
        $startDate = new \DateTime();
        $endDate = new \DateTime('+ 2 day');

        //получить диапозон дат из реквеста
        if (
            $request->has('start_date')
            && $request->has('end_date')
        ) {
            $startDate = new \DateTime($request->get('start_date'));
            $endDate = new \DateTime($request->get('end_date'));

            if ($endDate < $startDate) {
                $startDate = new \DateTime();
                $endDate = new \DateTime('+ 2 day');
            }
        }

        //проверим есть ли записи по интересующему нас году
        $periodYear = new \DatePeriod($startDate, new \DateInterval('P1Y') ,$endDate);
        foreach ($periodYear as $year) {
            $productionCalendar = ProductionCalendar::whereBetween(
                'day',
                array(
                    (new \DateTime('first day of January ' . $year->format('Y')))->format('Y-m-d'),
                    (new \DateTime('last day of December ' . $year->format('Y')))->format('Y-m-d')
                )
            )->exists();

            //запросим данные по нужному году с сервиса по курлу
            if (!$productionCalendar) {
                $response = Curl::to('https://isdayoff.ru/api/getdata?year='.$year->format('Y').'&pre=1')
                    ->get();
                $length = strlen($response);
                $arProductionCalendar = array();
                for ($index = 0; $index < $length; $index++) {
                    $arProductionCalendar[] = array(
                        'day' => (new \DateTime('first day of January '. $year->format('Y')))
                            ->add(new \DateInterval('P'.$index.'D'))
                            ->format('Y-m-d H:i:s'),
                        'working' => (int)$response[$index] === 0,
                        'holiday' => (int)$response[$index] === 1,
                        'shortened' => (int)$response[$index] === 2,
                    );
                }
                ProductionCalendar::insert($arProductionCalendar);
            }
        }
        unset($productionCalendar);

        //получить список рабочих дней на период дат
        $productionCalendar = ProductionCalendar::whereBetween(
            'day',
            array(
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            )
        )->get();

        if ($productionCalendar->count() < 1) {
            $productionCalendar = collect(array());
            $periodDay = new \DatePeriod($startDate, new \DateInterval('P1D') ,$endDate);
            foreach ($periodDay as $day) {
                $productionCalendar->push(array(
                    'day' => $day->format('Y-m-d'),
                    'working' => $day->format('N') != 6 && $day->format('N') != 7,
                    'holiday' => $day->format('N') == 6 || $day->format('N') == 7,
                    'shortened' => false,
                ));
            }
        }

        //получить работника
        $worker = Worker::findOrFail($id);

        //рабочий день + обед
        $arWorkerDate = array(
            'startDay' => new \DateTime('now today ' . $worker->work_time_start),
            'startDinner' => new \DateTime('now today ' . $worker->dinner_time_start),
            'EndDinner' => new \DateTime('now today ' . $worker->dinner_time_end),
            'EndDay' => new \DateTime('now today ' . $worker->work_time_end)
        );

        //отпуска
        $arVacations = array();
        foreach ($worker->vacations as $vacation) {
            $arVacations[] = array(
                'start' => new \DateTime($vacation->begin),
                'end' => new \DateTime($vacation->end)
            );
        }

        // события компании
        $corporateEvents = CompanyEvents::where('begin', '<', (clone $endDate)->add(new \DateInterval('P1D')))
            ->where('end', '>=', $startDate)
            ->get();
        $arCorporateEvents = array();
        foreach ($corporateEvents as $corparateEvent) {
            $arCorporateEvents[] = array(
                'start' => new \DateTime($corparateEvent->begin),
                'end' => new \DateTime($corparateEvent->end),
                'startTime' => new \DateTime('now today ' . (new \DateTime($corparateEvent->gegin))->format('H:i:s')),
                'endTime' => new \DateTime('now today ' . (new \DateTime($corparateEvent->end))->format('H:i:s')),
            );
        }

        //формируем итоговый массив
        foreach ($productionCalendar as $day) {
            if (!$day->holiday) {
                $obDay = new \DateTime($day->day);
                //наложить отпуска
                foreach ($arVacations as $vacation) {
                    if ($vacation['start'] <= $obDay && $obDay <= $vacation['end']) {
                        continue 2;
                    }
                }

                $endTimeRange = ($day->shortened)
                    ? (clone $arWorkerDate['EndDay'])->modify('-1 hour')
                    : $arWorkerDate['EndDay'];

                $ranges = array(
                    array(
                        'start' => new \DateTime($obDay->format('Y-m-d') . ' ' . $arWorkerDate['startDay']->format('H:i:s')),
                        'end' => new \DateTime($obDay->format('Y-m-d') . ' ' . $arWorkerDate['startDinner']->format('H:i:s')),
                    ),
                    array(
                        'start' => new \DateTime($obDay->format('Y-m-d') . ' ' . $arWorkerDate['EndDinner']->format('H:i:s')),
                        'end' => new \DateTime($obDay->format('Y-m-d') . ' ' . $endTimeRange->format('H:i:s')),
                    )
                );
                $rangeInterval = new \DateInterval('PT1H');

                //наложить события компании
                foreach ($arCorporateEvents as $corporateEvent) {
                    //совпал старт или совпал конец
                    if (
                        new \DateTime($corporateEvent['start']->format('Y-m-d')) == $obDay
                        || $obDay == new \DateTime($corporateEvent['end']->format('Y-m-d'))) {

                        $availableHours = array();

                        foreach ($ranges as $range) {
                            $period = new \DatePeriod($range['start'], $rangeInterval, $range['end']);
                            foreach($period as $iteration) {
                                if (!($corporateEvent['start'] <= $iteration && $iteration < $corporateEvent['end'])) {
                                    $availableHours[] = $iteration;
                                }
                            }
                        }
                        $newRange = null;
                        $ranges = array();

                        foreach ($availableHours as $hour) {
                            if (is_null($newRange)) {
                                $newRange = array(
                                    'start' => $hour,
                                    'end' => (clone $hour)->add($rangeInterval)
                                );
                            } else {
                                if ($newRange['end'] == $hour) {
                                    $newRange['end']->add($rangeInterval);
                                } else {
                                    $ranges[] = $newRange;
                                    $newRange = array(
                                        'start' => $hour,
                                        'end' => (clone $hour)->add($rangeInterval)
                                    );
                                }
                            }
                        }
                        $ranges[] = $newRange;
                    }
                    //попал в промежуток
                    else if ($corporateEvent['start'] < $obDay && $obDay < $corporateEvent['end']) {
                        continue 2;
                    }
                }

                if (count($ranges) > 0) {
                    $dataRange = array();
                    foreach ($ranges as $range) {
                        $dataRange[] = array(
                            'start' => $range['start']->format('H:i'),
                            'end' => $range['end']->format('H:i')
                        );
                    }
                    $data[] = array(
                        'day' => $obDay->format('Y-m-d'),
                        'timeRange' => $dataRange
                    );
                }
            }
        }

        return response()->json($data);
    }
}
