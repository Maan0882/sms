<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $programApps = $this->getApplicationsByProgram();
        $gradeDist = $this->getGradeDistribution();
    @endphp

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
          @if(isset($stats['institutes']))
        <x-filament::section>
            <div class="flex items-center gap-4">
                <x-heroicon-o-building-office-2 class="w-8 h-8 text-primary-500" />
                <div>
                    <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Institutes</h2>
                    <p class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ $stats['institutes'] }}</p>
                </div>
            </div>
        </x-filament::section>
        @endif
        
        <x-filament::section>
            <div class="flex items-center gap-4">
                <x-heroicon-o-users class="w-8 h-8 text-primary-500" />
                <div>
                    <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Students</h2>
                    <p class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ $stats['total_students'] }}</p>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex items-center gap-4">
                <x-heroicon-o-academic-cap class="w-8 h-8 text-primary-500" />
                <div>
                    <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Mentors</h2>
                    <p class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ $stats['total_mentors'] }}</p>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex items-center gap-4">
                <x-heroicon-o-book-open class="w-8 h-8 text-primary-500" />
                <div>
                    <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Programs</h2>
                    <p class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ $stats['total_programs'] }}</p>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex items-center gap-4">
                <x-heroicon-o-star class="w-8 h-8 text-primary-500" />
                <div>
                    <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Avg Score / Pass</h2>
                    <p class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ $stats['avg_score'] }} <span class="text-sm text-gray-500">/ {{ $stats['pass_rate'] }}%</span></p>
                </div>
            </div>
        </x-filament::section>

      
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <x-filament::section heading="Applications Pipeline">
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Total Applications</span>
                    <span class="font-medium">{{ $stats['total_applications'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-amber-500">Pending</span>
                    <span class="font-medium text-amber-500">{{ $stats['pending_apps'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-emerald-500">Approved</span>
                    <span class="font-medium text-emerald-500">{{ $stats['approved_apps'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-danger-500">Rejected</span>
                    <span class="font-medium text-danger-500">{{ $stats['rejected_apps'] }}</span>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section heading="Applications by Program">
            <div class="flex flex-col gap-4">
                @forelse ($programApps as $program)
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">{{ $program['name'] }}</span>
                        <span class="font-medium">{{ $program['count'] }}</span>
                    </div>
                @empty
                    <div class="text-sm text-gray-500 dark:text-gray-400">No program applications found.</div>
                @endforelse
            </div>
        </x-filament::section>
    </div>

    @if(!empty($gradeDist))
        <x-filament::section heading="Grade Distribution">
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-6">
                @foreach ($gradeDist as $grade => $count)
                    <div class="flex flex-col items-center justify-center p-4 rounded-xl bg-gray-50 dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10">
                        <span class="text-2xl font-bold text-primary-500">{{ $grade }}</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $count }} Student(s)</span>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
