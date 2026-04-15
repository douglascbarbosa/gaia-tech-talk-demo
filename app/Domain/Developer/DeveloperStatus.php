<?php

namespace App\Domain\Developer;

/**
 * Represents a developer's availability for new work in the portfolio domain.
 *
 * Persisted values use the backed enum string cases.
 */
enum DeveloperStatus: string
{
    case OpenForNewJobs = 'open_for_new_jobs';
    case Working = 'working';
}
