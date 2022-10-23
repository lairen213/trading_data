<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Curl\Curl;
use Mockery\Exception;

class ScriptsController extends Controller
{
    function isWeekend($date) {
        return (date('N', strtotime($date)) >= 6);
    }

    public function getWeekData($date_to, $date_from){
        $curl = new Curl();
        $curl->setUserAgent("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (HTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36");
        $curl->setHeaders(['Content-Type' => 'application/json']);
        try{
            $data = (array) $curl->get('https://www.alphavantage.co/query?function=FX_WEEKLY&from_symbol=EUR&to_symbol=USD&apikey=9ZF1G7Z2T8BYZ5A5&outputsize=full');
            $data = (array) $data['Time Series FX (Weekly)'];
        }catch (Exception $ex){
            return false;
        }
        $sum_percent = 0;
        while($date_to >= $date_from){
            if(array_key_exists($date_to, $data)){
                $info = (array) $data[$date_to];

                $sum = ($info['2. high'] + $info['3. low']) / 2;
                $minus = $info['2. high'] - $info['3. low'];
                $result = round(($minus/$sum) * 100, 2);

                $close_current = $info['4. close'];
                $close_previous = (array)next($data);
                $close_previous = $close_previous['4. close'];

                $sum = ($close_current + $close_previous) / 2;
                $minus = $close_current - $close_previous;
                $result_previous = round(($minus/$sum) * 100, 2);


                $info_date[] = ['date' => $date_to, 'previous_date' => $result_previous, 'high' => $info['2. high'], 'low' => $info['3. low'], 'percent' => $result];
                $sum_percent += $result;
            }

            $date_to = date('Y-m-d', strtotime("-1 days", strtotime($date_to)));
        }



        $avg_percent = round($sum_percent / count($info_date), 2);

        return ['info_date' => $info_date, 'avg_percent' => $avg_percent];
    }

    public function getDayData($date_to, $date_from){
        $curl = new Curl();
        $curl->setUserAgent("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (HTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36");
        $curl->setHeaders(['Content-Type' => 'application/json']);
        try{
            $data = (array) $curl->get('https://www.alphavantage.co/query?function=FX_DAILY&from_symbol=EUR&to_symbol=USD&apikey=9ZF1G7Z2T8BYZ5A5&outputsize=full');
            $data = (array) $data['Time Series FX (Daily)'];
        }catch (Exception $ex){
            return false;
        }
        $sum_percent = 0;
        while($date_to >= $date_from){
            if(!$this->isWeekend($date_to) && array_key_exists($date_to, $data)){
                $info = (array) $data[$date_to];

                $sum = ($info['2. high'] + $info['3. low']) / 2;
                $minus = $info['2. high'] - $info['3. low'];
                $result = round(($minus/$sum) * 100, 2);

                $close_current = $info['4. close'];
                $close_previous = (array)next($data);
                $close_previous = $close_previous['4. close'];

                $sum = ($close_current + $close_previous) / 2;
                $minus = $close_current - $close_previous;
                $result_previous = round(($minus/$sum) * 100, 2);

                $info_date[] = ['date' => $date_to, 'previous_date' => $result_previous, 'high' => $info['2. high'], 'low' => $info['3. low'], 'percent' => $result, 'day_of_week' => date('l', strtotime($date_to))];
                $sum_percent += $result;
            }

            $date_to = date('Y-m-d', strtotime("-1 days", strtotime($date_to)));
        }



        $avg_percent = round($sum_percent / count($info_date), 2);

        return ['info_date' => $info_date, 'avg_percent' => $avg_percent];
    }

    public function index(Request $request){
        $info_date = [];
        if($request->get('date_to')){
            $date_to_original = $date_to = $request->get('date_to');
        }else {
            $date_to_original = $date_to = date('Y-m-d');
        }

        if($request->get('date_from')){
            $date_from = $request->get('date_from');
        }else {
            $date_from = '2022-01-01';
        }

        $day_data = $this->getDayData($date_to_original, $date_from);
        $week_data = $this->getWeekData($date_to_original, $date_from);

        if(!$day_data || !$week_data){
            return view('scripts.index', ['data_week' => '','avg_percent_week' => '','data_day' => '', 'avg_percent_day' => '', 'date_to' => $date_to_original, 'date_from' => $date_from]);
        }

        return view('scripts.index', ['data_week' => $week_data['info_date'],'avg_percent_week' => $week_data['avg_percent'],'data_day' => $day_data['info_date'], 'avg_percent_day' => $day_data['avg_percent'], 'date_to' => $date_to_original, 'date_from' => $date_from]);
    }
}
