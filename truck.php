<?php

class truck
{
    const MAX_CAPACITY = 8;
    private $capacity = 0;
    private $coordinate = [0, 0];
    private $truck_to_branches = [0,0,0,0,0];

    public function loading($branch_cap){
        $place_left = self::MAX_CAPACITY - $this->capacity;
        $branch_left = $branch_cap;
        if($place_left == 0){
            return $branch_cap;
        }
        if($place_left < $branch_cap){
            $this->capacity += $place_left;
            $branch_left = $branch_cap - $place_left;
        }
        if($place_left >= $branch_cap){
            $this->capacity += $branch_cap;
            $branch_left = 0;
        }
        return $branch_left;
    }

    public function offloading(&$lc_capacity){
        $lc_capacity += $this->capacity;
        $this->capacity = 0;
    }

    public function getCapacity() : int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity)
    {
        $this->capacity = $capacity;
    }

    public function getCoordinate() : array
    {
        return $this->coordinate;
    }

    public function setCoordinate(array $coordinate)
    {
        $this->coordinate = $coordinate;
    }


    public function getTruckToBranches() : array
    {
        return $this->truck_to_branches;
    }

    public function setTruckToBranches(array $truck_to_branches)
    {
        $this->truck_to_branches = $truck_to_branches;
    }


}