<div
    class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="flex items-center gap-3">
        <div class="flex-1">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $greeting }}, {{ $user->name }}!
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $date }} - {{ $time }} WIB
            </p>
        </div>
    </div>
</div>
