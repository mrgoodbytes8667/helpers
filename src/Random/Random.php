<?php

namespace Bytes\Helpers\Random;

use BadMethodCallException;
use Illuminate\Support\Arr;

class Random
{
    /**
     * @param int $min
     * @param int $max
     * @param bool $weightedHigher
     * @return int
     */
    public static function getRandomWeightedNumber(int $min, int $max, bool $weightedHigher = true)
    {
        return Arr::random(static::getRandomWeightedNumberRange($min, $max, $weightedHigher));
    }

    /**
     * @param int $min
     * @param int $max
     * @param bool $weightedHigher
     * @return int[]
     */
    public static function getRandomWeightedNumberRange(int $min, int $max, bool $weightedHigher = true): array
    {
        if($min === $max) {
            return [$min];
        }

        if ($max < $min) {
            throw new BadMethodCallException('Max must be lower than min');
        }

        if(!$weightedHigher) {
            $temp = $min;
            $min = $max;
            $max = $temp;
            unset($temp);
        }

        $weightedRandom = [];
        foreach (range($min, $max) as $index => $value) {

            foreach (range(0, $index + 1) as $q) {
                $weightedRandom[] = $value;
            }
        }

        return $weightedRandom;
    }

    /**
     * @param array $values
     * @param bool $weightedHigher
     * @return array
     */
    public static function getWeightedArray(array $values, bool $weightedHigher = true): array
    {
        $weightedRandom = [];
        if(!$weightedHigher) {
            $values = array_values(array_reverse($values));
        }

        foreach ($values as $index => $value) {

            foreach (range(0, $index + 1) as $q) {
                $weightedRandom[] = $value;
            }
        }

        return $weightedRandom;
    }
}
