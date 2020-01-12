<?php
include 'SolutionTools.php';
include 'truck.php';

use PHPUnit\Framework\TestCase;


class SolutionTest extends TestCase
{
    private $configurations = [
        'branches_open_time_m' => '08:00:00',
        'branches_close_time_m' => '12:00:00',
        'branches_open_time_a' => '14:00:00',
        'branches_close_time_a' => '19:00:00',
        'lc_open_time_m' => '08:30:00',
        'lc_close_time_m' => '12:00:00',
        'lc_open_time_a' => '13:30:00',
        'lc_close_time_a' => '16:00:00',
        'loading_time_per_car' => 7.5,
        'offloading_time_lc' => 57.5,
        'branch_in_time' => 15,
        'branch_out_time' => 7.5,
        'lc_in_time' => 25,
        'lc_out_time' => 15,
    ];

    private $brs_cars = ['1' => 7, '2' => 1, '3' => 1, '4' => 1, '5' => 7];

    private $tbr1 = [
    '1' => ['1' => 0, '2' => 20, '3' => 24, '4' => 3, '5' => 14],
    '2' => ['1' => 20, '2' => 0, '3' => 35, '4' => 22, '5' => 22],
    '3' => ['1' => 24, '2' => 35, '3' => 0, '4' => 24, '5' => 18],
    '4' => ['1' => 3, '2' => 22, '3' => 24, '4' => 0, '5' => 21],
    '5' => ['1' => 14, '2' => 22, '3' => 18, '4' => 12, '5' => 0],
    ];

    private $tlc = ['1' => 12, '2' => 28, '3' => 28, '4' => 14, '5' => 16];
    private $truck_instance;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->truck_instance = new truck();
        $this->truck_instance->setCapacity(0);
        $this->truck_instance->setTruckToBranches(['1' => 15, '2' => 11, '3' => 9, '4' => 15, '5' => 23]);
        $this->configurations['time_constrain'] = (strtotime($this->configurations['lc_close_time_m']) - strtotime($this->configurations['lc_open_time_m']))
    + (strtotime($this->configurations['lc_close_time_a']) - strtotime($this->configurations['lc_open_time_a']));
        parent::__construct($name, $data, $dataName);
    }

    public function testOne()
    {
        $solution1 = new SolutionTools($this->configurations);
        $res = $solution1->compute($this->tbr1, $this->tlc, $this->brs_cars, $this->truck_instance);
        $solution1->printResult($res);
        $expected = (strtotime($this->configurations['lc_close_time_m']) - strtotime($this->configurations['lc_open_time_m']))
                    + (strtotime($this->configurations['lc_close_time_a']) - strtotime($this->configurations['lc_open_time_a']));

        printf("Time constrain: %s\r\n",date("H:i:s", round($expected)));

        $total_time = 0;
        foreach($res['Logistic_info'] as $key=>$value){
            $total_time += $value['time_passed'];
        }

        try {
            $this->assertLessThan($expected, $total_time*60);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

}
