<?php
class Statistics
{
    public static function quartile($data, $quartile)
    {
        sort($data);
        $pos = (count($data) - 1) * $quartile;
        if (fmod($pos, 1) == 0) {
            return $data[$pos];
        }
        $fraction = $pos - floor($pos);
        $lower_num = $data[floor($pos) - 1];
        $upper_num = $data[ceil($pos) - 1];
        $difference = $upper_num - $lower_num;
        return $lower_num + ($difference * $fraction);
    }

    public static function quartile25($data)
    {
        return self::quartile($data, 0.25);
    }

    public static function quartile50($data)
    {
        return self::quartile($data, 0.5);
    }

    public static function quartile75($data)
    {
        return self::quartile($data, 0.75);
    }

    public static function median($data)
    {
        return self::quartile50($data);
    }

    public static function average($data)
    {
        return array_sum($data) / count($data);
    }

    public static function stdDev($data)
    {
        if (count($data) < 2) {
          return;
        }
        $avg = self::average($data);
        $sum = 0;
        foreach($data as $value) {
            $sum += pow($value - $avg, 2);
        }
        return sqrt((1 / (count($data) - 1)) * $sum);
    }
}