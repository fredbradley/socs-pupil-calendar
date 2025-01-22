<?php

use FredBradley\SocsPupilCalendar\PupilCalendar;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->key = 'A4DDDED5-37F8-470B-B43E-B582A815AC9D';
    $this->pupilCalendar = new PupilCalendar($this->key);
});

test('it sets the URL correctly', function () {
    $expectedUrl = PupilCalendar::BASE_URL.'?key='.$this->key;
    expect($this->pupilCalendar->url)->toBe($expectedUrl);
});

test('it returns events as a collection', function () {
    expect($this->pupilCalendar->asCollection())->toBeInstanceOf(Collection::class);
});

test('it filters events by category', function () {
    $category = 'Music Lesson';
    $filteredEvents = $this->pupilCalendar->filterByCategory($category);
    expect($filteredEvents)->toBeInstanceOf(Collection::class);
});

test('it excludes events by category', function () {
    $category = 'Music Lesson';
    $excludedEvents = $this->pupilCalendar->excludeByCategory($category);
    expect($excludedEvents)->toBeInstanceOf(Collection::class);
});
