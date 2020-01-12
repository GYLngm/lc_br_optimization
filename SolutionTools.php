<?php


/**
 *
 * Solution tools to resolve test problems.
 *  1) calCoef() to calculate coefficient cars_in_stocks/distance
 *  2) branch_selection() to choose branch to go
 *  3) collect_process_by_cars_per_distance() function to collect cars
 *  4) compute() function to offloading cars and compare with constrains
 *
 * */
class SolutionTools
{
    private $coefs = []; // matrix of cars stock of each branch per distance.
    private $configurations;
    private $total_time = 0;

    public function __construct($configuration)
    {
       $this->configurations = $configuration;
    }

    /**
     * Apply DFS and record each DFS calculation result until it reach constrains (stocks and time), offloading trunk in this function.
     * @param array $tbr Table of m*2 elements which are times needed to go to from one to another branch
     * @param array $tlc Array of times taken to go from each branch to Logistics center
     * @param array $brs_cars Array of cars stocked in branches with key as branch ids and quantities in branch as value
     * @param truck $truck current truck object
     * @param string $cur_branch current branch, 0 represent truck position
     * @return array Whole transport courses and maximum quantity of stock in logistics center.
     */
    public function compute($tbr, $tlc, &$brs_cars, truck &$truck, $cur_branch = '0')
    {
        $result = ['lc_capacity' => 0,'Logistic_info' => []];

        // Planning collecting path until all cars stock is empty.
        while(array_sum($brs_cars) > 0 && $this->configurations['time_constrain'] > $this->total_time * 60){
            // Recalculate coefficient each time when cars stock change
            $this->calCoef($tbr,$brs_cars,$truck->getTruckToBranches());

            // Choose collect path and record them to $result array
            $result['Logistic_info'][] = $this->collect_process_by_cars_per_distance($tbr, $tlc, $brs_cars,$truck, $cur_branch);

            // Offloading cars from trunk and empty trunk.
            $truck->offloading($result['lc_capacity']);
            printf("total_time %s\r\n",date("H:i:s",round($this->total_time*60)));
        }
        return $result;
    }


    /**
     * A recursive DFS function to choose optimized path.
     * @param array $tbr Table of m*2 elements which are times needed to go to from one to another branch
     * @param array $tlc Array of times taken to go from each branch to Logistics center
     * @param array $brs_cars Array of cars stocked in branches with key as branch ids and quantities in branch as value
     * @param truck $truck current truck object
     * @param string $cur_branch current branch, 0 represent truck position
     * @param array $res array to store informations
     * @return array transport course and travel time of one tour
     */
    public function collect_process_by_cars_per_distance(
        $tbr,
        $tlc,
        &$brs_cars,
        truck &$truck,
        $cur_branch = '0',
        &$res = [
            'close_path' => '', // Branches already collected, ex.: '015' represent 0(truck) -> branch 1 -> branch 5
            'time_passed' => 0,
        ]
    )
    {
        $res['close_path'] .= strval($cur_branch);

        // Check trunk capacity
        if($truck->getCapacity() >= 8) {
            $res['time_passed'] += $this->offloadingProcess();
            printf("    END".$cur_branch." -> LC! \r\n");
            return $res;
        }

        // Choose start branch
        if($cur_branch == '0'){
            printf("Start a new tour... \r\n");
            $next_branch = $this->branch_selection($res['close_path'], $cur_branch,$brs_cars);
            $this->collect_process_by_cars_per_distance($tbr, $tlc, $brs_cars, $truck, $next_branch, $res);
        }
        // Proceed loading process and choose next branch
        else {

            // Collect process: enter->collect->leave
            $branch_org = $brs_cars[$cur_branch];
            $brs_cars[$cur_branch] = $truck->loading($brs_cars[$cur_branch]);
            $res['time_passed'] += $this->loadingProcess(intval($branch_org - $brs_cars[$cur_branch]));
            printf("    branch %s has %s, %s loaded, %s left \r\n", $cur_branch, $branch_org, $branch_org - $brs_cars[$cur_branch], $brs_cars[$cur_branch]);

            // Select next branch
            $next_branch = $this->branch_selection($res['close_path'], $cur_branch, $brs_cars);


            // Check trunk capacity
            if($truck->getCapacity() >= 8 || array_sum($brs_cars) == 0) {
                $res['time_passed'] += $this->offloadingProcess();
                printf("    END ".$cur_branch." -> LC! \r\n");
                return $res;
            } else {
                printf("    branch ".$cur_branch." -> branch: ".$next_branch."\r\n");
                $this->collect_process_by_cars_per_distance($tbr, $tlc, $brs_cars, $truck, $next_branch, $res);
            }
        }

        return $res;
    }

    /**
     * Giving the id of next reachable branch by selecting highest number in $coefs.
     *
     * @param string $close_path Branches already collected, ex.: '015' represent 0(truck) -> branch 1 -> branch 5
     * @param string $cur_branch current branch, 0 represent truck position
     * @param array $brs_cars Array of cars stocked in branches with key as branch ids and quantities in branch as value
     * @return string Next branch id
     */
    public function branch_selection($close_path, $cur_branch, $brs_cars = []){
        $i = 0;
        arsort($this->coefs[strval($cur_branch)]);
        $next_branch = array_keys($this->coefs[strval($cur_branch)])[$i++];

        while(strpos($close_path,strval($next_branch)) != false && $brs_cars[$next_branch] == 0){
            $next_branch = array_keys($this->coefs[strval($cur_branch)])[$i++];
        }

        return $next_branch;
    }

    /**
     * Function to calculate $coefs matrix
     * @param array $tbr Table of m*2 elements which are times needed to go to from one to another branch
     * @param array $brs_cars Array of cars stocked in branches with key as branch ids and quantities in branch as value
     * @param array $truck_to_distance trucks' relative positions to all branches
     */
    public function calCoef($tbr, $brs_cars, $truck_to_distance){
        $this->coefs = [];
        array_unshift($tbr, $truck_to_distance);
        foreach($tbr as $value) {
            $temp = [];
            foreach($value as $key=>$v){
                if($v == 0)
                    $temp[$key] = 0;
                else
                    $temp[$key] = intval($brs_cars[$key])/intval($v);
            }
            $this->coefs[] = $temp;
        }
    }

    /**
     * Adding up times to truck enter branch, loading cars and leaving branch
     * @param int $car_loads quantity of cars loaded in branch
     * @return float|int
     */
    public function loadingProcess($car_loads)
    {
        $this->total_time += $this->configurations['branch_in_time']+$this->configurations['loading_time_per_car']*$car_loads+$this->configurations['branch_out_time'];
        return $this->configurations['branch_in_time']+$this->configurations['loading_time_per_car']*$car_loads+$this->configurations['branch_out_time'];
    }

    /**
     * Adding up times to truck enter logistics center, offloading cars and leaving logistics center
     * @return float|int
     * */
    public function offloadingProcess(){
        $this->total_time += $this->configurations['lc_in_time']+$this->configurations['offloading_time_lc']+$this->configurations['lc_out_time'];
        return $this->configurations['lc_in_time']+$this->configurations['offloading_time_lc']+$this->configurations['lc_out_time'];
    }

    public function printResult($result){
        printf("--------------------------------\r\n");
        printf("            Result              \r\n");
        printf("-------------------------------- \r\n");
        printf("Cars collected : %s \r\n",$result['lc_capacity']);
        printf("Tours today : %s\r\n", count($result['Logistic_info']));
        $total_time = 0.0;
        foreach($result['Logistic_info'] as $key=>$value){
            $total_time += $value['time_passed'];
            printf("    branch passed : ");
            $branch_passed = str_split($value['close_path']);
            foreach($branch_passed as $br)
                printf("%s -> ", $br);
            printf("Logistic Center, time : %s\r\n",$value['time_passed']);
        }
        printf("Total time : %s\r\n",$this->convertMinuteToHour($total_time));

    }

    public function convertMinuteToHour($minute,$format = '%02dh%02dm'){
        if ($minute < 1) {
            return;
        }
        $hours = floor($minute / 60);
        $minutes = ($minute % 60);
        return sprintf($format, $hours, $minutes);
    }

    public function getTotalTime()
    {
        return $this->total_time;
    }
}