<?php

namespace FredBradley\SocsPupilCalendar;

use Carbon\Carbon;
use Exception;
use ICal\Event;
use Illuminate\Support\Collection;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event as SpatieEvent;

class MakeCalendar
{
    public string $key;

    public Calendar $calendar;

    public function for(string $misId): self
    {
        $pairs = include 'config/pairs.php';

        $this->key = $pairs->{$misId};

        return $this;
    }

    public function asString(): string
    {
        return $this->calendar->get();
    }

    /**
     * @throws Exception
     */
    public function make(): self
    {
        $ical = new PupilCalendar($this->key, Carbon::now()->subMonth(), Carbon::now()->addMonth());
        $lessons = $ical->filterByCategory(['Music Lesson']);

        $this->calendar = Calendar::create()
            ->name($ical->tidyTitle.' Music Lessons')
            ->description("Music Lessons Calendar for {$ical->tidyTitle}")
            ->event(
                $this->mapEvents($lessons)->toArray()
            )
            ->refreshInterval(5);

        return $this;
    }

    private function mapEvents(Collection $lessons, ?string $name = null): Collection
    {
        return $lessons
            ->ensure(Event::class)
            ->map(fn (Event $event) => SpatieEvent::create(trim($name.' '.$event->summary))
                ->startsAt(Carbon::parse($event->dtstart))
                ->endsAt(Carbon::parse($event->dtend))
            );
    }
}
