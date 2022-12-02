<?php

namespace Bytes\Helpers\DateTime;

use BadMethodCallException;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Illuminate\Support\Arr;
use function Symfony\Component\String\u;

/**
 * @method static int getYearFromDate(DateTimeInterface $dateTime, ?string $datePart = null)
 * @method static int getMonthFromDate(DateTimeInterface $dateTime, ?string $datePart = null)
 * @method static int getDayFromDate(DateTimeInterface $dateTime, ?string $datePart = null)
 * @method static int getHourFromDate(DateTimeInterface $dateTime, ?string $datePart = null)
 * @method static int getMinuteFromDate(DateTimeInterface $dateTime, ?string $datePart = null)
 * @method static int getSecondFromDate(DateTimeInterface $dateTime, ?string $datePart = null)
 * @method static mixed getTimezoneFromDate(DateTimeInterface $dateTime, ?string $datePart = null)
 * @method static int getDayOfWeekFromDate(DateTimeInterface $dateTime, ?string $datePart = null)
 */
class Date
{
    /**
     * @var string
     */
    const FORMAT_YMD = 'Y-m-d';

    /**
     * @var string
     */
    const FORMAT_DOCTRINE = 'Y-m-d H:i:s';

    /**
     * @var string
     */
    const FORMAT_EASYADMIN_FILTER = 'Y-m-d\TH:i';

    /**
     * @var string
     */
    const FORMAT_EASYADMIN_SHORT = 'n/j @ g:i a T';

    /**
     * @var string
     */
    const TIMEZONE_UTC = 'UTC';

    /**
     * @param DateInterval $interval
     * @param DateTimeInterface|null $now
     * @return DateTimeImmutable
     */
    public static function nowAdd(DateInterval $interval, ?DateTimeInterface $now = null): DateTimeImmutable
    {
        if (!is_null($now)) {
            $now = DateTimeImmutable::createFromInterface($now);
        } else {
            $now = static::getNowUTC();
        }

        return $now->add($interval);
    }

    /**
     * @return DateTimeImmutable
     */
    public static function getNowUTC(): DateTimeImmutable
    {
        return (DateTimeImmutable::createFromFormat('U', time()))->setTimezone(static::getTimeZoneUTC());
    }

    /**
     * @return DateTimeZone
     */
    public static function getTimeZoneUTC(): DateTimeZone
    {
        return new DateTimeZone(static::TIMEZONE_UTC);
    }

    /**
     * is triggered when invoking inaccessible methods in a static context.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @link https://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.methods
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $getFromDatePart = u($name)->before('FromDate')->after('get')->lower()->toString();
        $toInt = true;
        switch ($getFromDatePart) {
            case 'year':
            case 'years':
                $part = 'Y';
                break;
            case 'month':
            case 'months':
                $part = 'm';
                break;
            case 'day':
            case 'days':
                $part = 'd';
                break;
            case 'hour':
            case 'hours':
                $part = 'G';
                break;
            case 'minute':
            case 'minutes':
                $part = 'i';
                break;
            case 'second':
            case 'seconds':
                $part = 's';
                break;
            case 'timezone':
                $part = 'p';
                $toInt = false;
                break;
            case 'dayofweek':
                $part = 'w';
                break;
            default:
                return null;
        }

        /** @var DateTimeInterface $date */
        $date = $arguments[0];
        $formatted = $date->format($part);
        if ($toInt) {
            return (int)$formatted;
        } else {
            return $formatted;
        }
    }

    /**
     * @param int $fromValue
     * @param string $fromType
     * @param string $toType
     * @return int
     */
    public static function convertTimeTypeToTimeType(int $fromValue, string $fromType, string $toType): int
    {
        switch (u($fromType)->trim()->lower()->slice(0, 1)->toString()) {
            case 'd':
                $fromValue *= 24 * 3600;
                break;
            case 'h':
                $fromValue *= 3600;
                break;
            case 'm':
                $fromValue *= 60;
                break;
            case 's':
                break;
            default:
                throw new BadMethodCallException();
                break;
        }

        switch (u($toType)->trim()->lower()->slice(0, 1)->toString()) {
            case 'd':
                $fromValue /= 24 * 3600;
                break;
            case 'h':
                $fromValue /= 3600;
                break;
            case 'm':
                $fromValue /= 60;
                break;
            case 's':
                break;
            default:
                throw new BadMethodCallException();
                break;
        }

        return round($fromValue);
    }

    /**
     * @param DateTimeInterface|null $since
     * @return DateTimeImmutable|null
     */
    public static function toImmutableUTC(?DateTimeInterface $since): ?DateTimeImmutable
    {
        return static::toImmutable($since)?->setTimezone(static::getTimeZoneUTC());
    }

    /**
     * @param DateTimeInterface|null $since
     * @return DateTimeImmutable|null
     */
    public static function toImmutable(?DateTimeInterface $since): ?DateTimeImmutable
    {
        return !is_null($since) ? DateTimeImmutable::createFromInterface($since) : null;
    }

    /**
     * @param DateTimeInterface $dateTime
     * @param int[] $allowedMinutes
     * @return DateTimeInterface
     * @throws Exception
     */
    public static function reduceMinutesNoTensToValue(DateTimeInterface $dateTime, array $allowedMinutes): DateTimeInterface
    {
        $minute = static::getMinuteFromDate($dateTime) % 10;
        if (in_array($minute, $allowedMinutes)) {
            return $dateTime;
        }

        $offset = 0;

        do {
            $offset++;
            $minute--;
            if ($minute < 0) {
                $minute = 9;
            }
        } while (!in_array($minute, $allowedMinutes));

        return $dateTime->sub(new DateInterval('PT' . $offset . 'M'));
    }

    /**
     * @param DateTimeInterface $dateTime
     * @param int[] $allowedMinutes
     * @return DateTimeInterface
     * @throws Exception
     */
    public static function increaseMinutesNoTensToValue(DateTimeInterface $dateTime, array $allowedMinutes): DateTimeInterface
    {
        $minute = static::getMinuteFromDate($dateTime) % 10;
        if (in_array($minute, $allowedMinutes)) {
            return $dateTime;
        }

        $offset = 0;

        do {
            $offset++;
            $minute++;
            if ($minute > 9) {
                $minute = 0;
            }
        } while (!in_array($minute, $allowedMinutes));

        return $dateTime->add(new DateInterval('PT' . $offset . 'M'));
    }

    /**
     * @param DateTimeInterface $start
     * @param DateTimeInterface $end
     * @return int
     */
    public static function countWeekdaysInRange(DateTimeInterface $start, DateTimeInterface $end): int
    {
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($start, $interval, $end);

        return count(Arr::where(iterator_to_array($period), function (DateTimeInterface $dt) {
            return !in_array(static::getDayOfWeekFromDate($dt), [0, 6]);
        }) ?? []);
    }
}
