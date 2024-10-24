<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveDuplicateUsers extends Command
{
    // The name and signature of the console command
    protected $signature = 'users:remove-duplicates';

    // The console command description
    protected $description = 'Remove duplicate rows from the users table based on email or username';

    public function handle(): int
    {
        // Define which columns should be unique, such as 'email'
        $columns = ['user_id', 'store', 'branch', 'type', 'time','coefficient', 'notified'];

        // Step 2: Find duplicates based on all columns except the primary key (usually 'id')
        $duplicates = DB::table('orders')
            ->select(DB::raw('MIN(id) as id'), ...$columns)
            ->groupBy(...$columns)
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        // Step 3: Delete duplicates, keeping the first occurrence (the row with MIN(id))
        if ($duplicates->count() > 0) {
            foreach ($duplicates as $duplicate) {
                DB::table('orders')
                    ->where('id', '>', $duplicate->id)
                    ->where(function ($query) use ($columns, $duplicate) {
                        // Match all column values to identify duplicate rows
                        foreach ($columns as $column) {
                            $query->where($column, $duplicate->$column);
                        }
                    })
                    ->delete();
            }

            $this->info('Duplicate users removed successfully.');
        } else {
            $this->info('No duplicate users found.');
        }

        return 0;
    }

}
