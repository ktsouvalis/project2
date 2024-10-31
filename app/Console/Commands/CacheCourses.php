<?php

namespace App\Console\Commands;

use App\Models\Course;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheCourses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cache-courses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Caches courses for 1 hour';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Cache::put('courses.all', Course::all(), now()->addHours(1));
        $this->info('Courses cached successfully.');
    }
}
