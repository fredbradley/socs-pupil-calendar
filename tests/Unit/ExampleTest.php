<?php

use Carbon\Carbon;
use FredBradley\SocsPupilCalendar\Exceptions\CalendarNotAvailableException;
use FredBradley\SocsPupilCalendar\PupilCalendar;
use ICal\ICal;
use Illuminate\Support\Collection;

function fakePupilCalendar(string $key, string $contents): PupilCalendar
{
    return new class($key, $contents) extends PupilCalendar
    {
        public function __construct(string $key, private string $contents)
        {
            parent::__construct($key);
        }

        protected function loadCalendar(): ICal
        {
            $ical = new ICal(false, ['defaultTimeZone' => 'UTC']);

            return $ical->initString($this->contents);
        }
    };
}

beforeEach(function () {
    Carbon::setTestNow('2026-10-23 12:00:00');

    $this->key = 'A4DDDED5-37F8-470B-B43E-B582A815AC9D';
    $this->pupilCalendar = fakePupilCalendar($this->key, file_get_contents(__DIR__.'/../Fixtures/pupil-calendar.ics'));
});

afterEach(function () {
    Carbon::setTestNow();
});

test('it sets the URL correctly', function () {
    $expectedUrl = PupilCalendar::BASE_URL.'?key='.$this->key;
    expect($this->pupilCalendar->url)->toBe($expectedUrl);
});

test('it tidies the calendar title', function () {
    expect($this->pupilCalendar->tidyTitle)->toBe('Joe Bloggs');
});

test('it returns events as a collection', function () {
    expect($this->pupilCalendar->asCollection())->toBeInstanceOf(Collection::class);
});

test('it only includes events within the default date range', function () {
    expect($this->pupilCalendar->asCollection()->pluck('summary')->all())->toBe(['Piano', 'Rugby']);
});

test('it filters events by category', function () {
    $filteredEvents = $this->pupilCalendar->filterByCategory('Music Lesson');

    expect($filteredEvents)->toBeInstanceOf(Collection::class)
        ->and($filteredEvents->pluck('summary')->values()->all())->toBe(['Piano']);
});

test('it excludes events by category', function () {
    $excludedEvents = $this->pupilCalendar->excludeByCategory('Music Lesson');

    expect($excludedEvents)->toBeInstanceOf(Collection::class)
        ->and($excludedEvents->pluck('summary')->values()->all())->toBe(['Rugby']);
});

test('it throws when SOCS does not return a calendar', function () {
    fakePupilCalendar($this->key, 'Calendar not available');
})->throws(CalendarNotAvailableException::class);
