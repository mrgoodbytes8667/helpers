<?php

namespace Bytes\Helpers\Tests;

use BadMethodCallException;
use Bytes\Helpers\Random\Random;
use Generator;
use PHPUnit\Framework\TestCase;


class RandomTest extends TestCase
{
    /**
     * @return void
     */
    public function testGet(): void
    {
        $this->assertSame(1, Random::getRandomWeightedNumber(1, 1));
    }

    /**
     * @dataProvider provideRanges
     * @param $expected
     * @param $min
     * @param $max
     * @param $weightedHigher
     * @return void
     */
    public function testWeights($expected, $min, $max, $weightedHigher): void
    {
        $this->assertSame($expected, Random::getRandomWeightedNumberRange($min, $max, weightedHigher: $weightedHigher));
        $this->assertSame($expected, Random::getWeightedArray([$min, $max], weightedHigher: $weightedHigher));
    }

    /**
     * @return Generator
     */
    public function provideRanges(): Generator
    {
        yield '1, 2 higher' => ['expected' => [1, 1, 2, 2, 2], 'min' => 1, 'max' => 2, 'weightedHigher' => true];
        yield '1, 2 lower' => ['expected' => [2, 2, 1, 1, 1], 'min' => 1, 'max' => 2, 'weightedHigher' => false];
    }
    
    /**
     * @return void
     */
    public function testMaxLessThanMin(): void
    {
        $this->expectException(BadMethodCallException::class);
        Random::getRandomWeightedNumber(5, 1);
    }
}
