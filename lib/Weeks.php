<?php

class Weeks
{
    public function getWeek($weekNumber)
    {
        /*
        Sunday:1
        Saturday:7
         */
        static $WeekArr = array("sunday", "monday", "tuesday", "wednesday", "thursday", "friday", "saturday");
        return $WeekArr[($weekNumber)];
    }

    public function getWeekNum($week)
    {
        /*
        1:Sunday
        7:Saturday
        */
        static $WeekArr = ["sunday", "monday", "tuesday", "wednesday", "thursday", "friday", "saturday"];
        if (($result = array_search($week, $WeekArr)) !== false) {
            return $result + 1;
        } else {
            return false;
        }
    }
}
