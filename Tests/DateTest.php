<?php

namespace Bytes\Helpers\Tests;


use Bytes\Helpers\DateTime\Date;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;


class DateTest extends TestCase
{
    /**
     * @dataProvider provideDate
     * @param DateTimeImmutable $date
     * @return void
     */
    public function testGetDateParts($date): void
    {
        $this->assertSame(2020, Date::getYearFromDate($date));
        $this->assertSame(3, Date::getMonthFromDate($date));
        $this->assertSame(30, Date::getDayFromDate($date));
        $this->assertSame(0, Date::getHourFromDate($date));
        $this->assertSame(0, Date::getMinuteFromDate($date));
        $this->assertSame(0, Date::getSecondFromDate($date));
        $this->assertSame(1, Date::getDayOfWeekFromDate($date));
        $this->assertSame(1, Date::getDayOfWeekFromDate($date));
        $this->assertSame('Z', Date::getTimezoneFromDate($date));
        $this->assertNull(Date::getNonexistantFromDate($date));
    }

    public function testGetTimeZoneUTC()
    {
        $this->assertEquals(new DateTimeZone('UTC'), Date::getTimeZoneUTC());
    }

    public function testNow()
    {
        ClockMock::register(__CLASS__);
        ClockMock::withClockMock(true);

        $this->assertEquals(DateTimeImmutable::createFromFormat('U', time()), Date::getNowUTC());

        ClockMock::withClockMock(false);
    }

    public function testNowAdd()
    {
        ClockMock::register(__CLASS__);
        ClockMock::withClockMock(true);

        $interval = new DateInterval('PT1H');

        $now = DateTimeImmutable::createFromFormat('U', time());
        $add = $now->add($interval);

        $this->assertEquals($add, Date::nowAdd($interval));
        $this->assertEquals($add, Date::nowAdd($interval, $now));

        ClockMock::withClockMock(false);
    }

    public function testToImmutable()
    {
        ClockMock::register(__CLASS__);
        ClockMock::withClockMock(true);

        $now = DateTime::createFromFormat('U', time())->setTimezone(new DateTimeZone('UTC'));
        $alsoNow = DateTime::createFromFormat('U', time())->setTimezone(new DateTimeZone('America/New_York'));

        $this->assertEquals($alsoNow->setTimezone(new DateTimeZone('UTC')), Date::toImmutableUTC($now));

        ClockMock::withClockMock(false);
    }

    /**
     * @dataProvider provideDateReduce
     * @param $now
     * @param $reduce
     * @return void
     * @throws Exception
     */
    public function testReduceMinutesNoTensToValue($now, $reduce)
    {
        $this->assertEquals($reduce, Date::reduceMinutesNoTensToValue($now, [5]));
        $this->assertEquals($reduce, Date::reduceMinutesNoTensToValue($reduce, [5]));
    }

    /**
     * @dataProvider provideDateReduce
     * @param $now
     * @param $reduce
     * @param $increase
     * @return void
     * @throws Exception
     */
    public function testIncreaseMinutesNoTensToValue($now, $reduce, $increase)
    {
        $this->assertEquals($increase, Date::increaseMinutesNoTensToValue($now, [5]));
        $this->assertEquals($increase, Date::increaseMinutesNoTensToValue($increase, [5]));
    }

    /**
     * @dataProvider provideWeekdays
     * @param $start
     * @param $end
     * @param $count
     * @return void
     */
    public function testCountWeekdaysInRange($start, $end, $count): void
    {
        $this->assertSame($count, Date::countWeekdaysInRange(new DateTimeImmutable($start), new DateTimeImmutable($end)));
    }

    /**
     * @return Generator
     */
    public function provideWeekdays(): Generator
    {
        yield '3/30 - 4/30' => ['start' => '2020-03-30', 'end' => '2020-04-30', 'count' => 23];
        yield '3/28 - 3/28' => ['start' => '2020-03-28', 'end' => '2020-03-28', 'count' => 0];
        yield '3/28 - 3/29' => ['start' => '2020-03-28', 'end' => '2020-03-29', 'count' => 0];
        yield '3/28 - 3/30' => ['start' => '2020-03-28', 'end' => '2020-03-30', 'count' => 0];
        yield '3/28 - 3/31' => ['start' => '2020-03-28', 'end' => '2020-03-31', 'count' => 1];
        yield '3/29 - 3/28' => ['start' => '2020-03-29', 'end' => '2020-03-28', 'count' => 0];
    }

    /**
     * @return Generator
     */
    public function provideDateReduce(): Generator
    {
        yield '16:36' => ['now' => new DateTimeImmutable('2020-03-30 16:36'), 'reduce' => new DateTimeImmutable('2020-03-30 16:35'), 'increase' => new DateTimeImmutable('2020-03-30 16:45')];
        yield '16:01' => ['now' => new DateTimeImmutable('2020-03-30 16:01'), 'reduce' => new DateTimeImmutable('2020-03-30 15:55'), 'increase' => new DateTimeImmutable('2020-03-30 16:05')];
        yield '0:01' => ['now' => new DateTimeImmutable('2020-03-30 0:01'), 'reduce' => new DateTimeImmutable('2020-03-29 23:55'), 'increase' => new DateTimeImmutable('2020-03-30 0:05')];
        yield '23:59' => ['now' => new DateTimeImmutable('2020-03-29 23:59'), 'reduce' => new DateTimeImmutable('2020-03-29 23:55'), 'increase' => new DateTimeImmutable('2020-03-30 0:05')];
    }

    /**
     * @return Generator
     */
    public function provideDate(): Generator
    {
        yield '3/30/2020' => ['date' => new DateTimeImmutable('3/30/2020', timezone: new DateTimeZone('UTC'))];
    }
}
