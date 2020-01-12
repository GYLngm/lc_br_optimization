<?php

include 'truck.php';
include 'SolutionTools.php';

// Initialize configuration
$configurations = [
    'branches_open_time_m' => '08:00:00',
    'branches_close_time_m' => '12:00:00',
    'branches_open_time_a' => '14:00:00',
    'branches_close_time_a' => '19:00:00',
    'lc_open_time_m' => '08:30:00',
    'lc_close_time_m' => '12:00:00',
    'lc_open_time_a' => '13:30:00',
    'lc_close_time_a' => '16:00:00',
    'time_constrain' => (strtotime($this->configurations['lc_close_time_m']) - strtotime($this->configurations['lc_open_time_m']))
        + (strtotime($this->configurations['lc_close_time_a']) - strtotime($this->configurations['lc_open_time_a'])),
    'loading_time_per_car' => 7.5,
    'offloading_time_lc' => 57.5,
    'branch_in_time' => 15,
    'branch_out_time' => 7.5,
    'lc_in_time' => 25,
    'lc_out_time' => 15,
];

// Array of cars stocked in branches with key as branch ids and quantities in branch as value
$brs_cars = ['1' => 7, '2' => 1, '3' => 1, '4' => 1, '5' => 7];

// Table of m*2 elements which are times needed to go to from one to another branch，using time need to arrive to represent distance
$tbr1 = [
    '1' => ['1' => 0, '2' => 20, '3' => 24, '4' => 3, '5' => 14],
    '2' => ['1' => 20, '2' => 0, '3' => 35, '4' => 22, '5' => 22],
    '3' => ['1' => 24, '2' => 35, '3' => 0, '4' => 24, '5' => 18],
    '4' => ['1' => 3, '2' => 22, '3' => 24, '4' => 0, '5' => 21],
    '5' => ['1' => 14, '2' => 22, '3' => 18, '4' => 12, '5' => 0],
];

// Table of times taken to go from each branch to Logistics center
$tlc = ['1' => 12, '2' => 28, '3' => 28, '4' => 14, '5' => 16];

// Initialize a truck with initial cars in trunk and relative position to all branches, represented by time
$truck1 = new truck();
$truck1->setCapacity(0);
$truck1->setTruckToBranches(['1' => 15, '2' => 11, '3' => 9, '4' => 15, '5' => 23]);

// Solution tools object
$solution = new SolutionTools($configurations);
$res = $solution->compute($tbr1, $tlc, $brs_cars, $truck1);
$solution->printResult($res);
