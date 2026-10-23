<?php

namespace FredBradley\SocsPupilCalendar;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Exception;
use FredBradley\SocsPupilCalendar\Exceptions\CalendarNotAvailableException;
use ICal\Event;
use ICal\ICal;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PupilCalendar
{
    public const string BASE_URL = 'https://calsync-pupil.socscms.com/CalendarSyncPupil.ashx';

    public ICal $calendar;

    public array $events = [];

    public string $url;

    public string $tidyTitle;

    private function setUrl(string $key): void
    {
        $this->url = self::BASE_URL.'?'.http_build_query([
            'key' => $key,
        ]);
    }

    private function setDefaultDateRanges(): void
    {
        $now = CarbonImmutable::now();
        $this->rangeStart = $this->rangeStart ?? $now->subWeeks(2);
        $this->rangeEnd = $this->rangeEnd ?? $now->addWeeks(2);
    }

    /**
     * @throws Exception
     */
    protected function loadCalendar(): ICal
    {
        return new ICal($this->url, [
            'defaultSpan' => 5,
            'defaultTimeZone' => 'UTC',
            'httpUserAgent' => 'Cranleigh School Pastoral Module',
        ]);
    }

    /**
     * @throws CalendarNotAvailableException
     * @throws Exception
     */
    public function __construct(
        protected string $key,
        public ?CarbonInterface $rangeStart = null,
        public ?CarbonInterface $rangeEnd = null
    ) {
        $this->setUrl($this->key);

        $this->setDefaultDateRanges();

        $this->calendar = $this->loadCalendar();

        // SOCS responds with a plain-text error body for unknown or expired keys
        $title = $this->calendar->cal['VCALENDAR']['X-WR-CALNAME'] ?? null;

        if ($title === null) {
            throw CalendarNotAvailableException::forUrl($this->url);
        }

        $this->events = $this->calendar->eventsFromRange($this->rangeStart, $this->rangeEnd);

        $this->tidyTitle = Str::replace('-', ' ', $title);
    }

    public function asCollection(): Collection
    {
        return collect($this->events);
    }

    public function filterByCategory(array|string $category): Collection
    {
        $categories = is_array($category) ? $category : [$category];

        return $this->asCollection()->filter(function (Event $event) use ($categories) {
            return in_array($event->additionalProperties['categories'], $categories);
        });
    }

    public function excludeByCategory(array|string $category): Collection
    {
        $categories = is_array($category) ? $category : [$category];

        return $this->asCollection()->reject(function (Event $event) use ($categories) {
            return in_array($event->additionalProperties['categories'], $categories);
        });
    }
}
