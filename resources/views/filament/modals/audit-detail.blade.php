<div class="space-y-4 p-2">

    {{-- Who & When --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">User</p>
            <p class="text-sm font-semibold mt-1">{{ $audit->user?->name ?? 'System' }}</p>
            <p class="text-xs text-gray-400">{{ $audit->user?->email ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">When</p>
            <p class="text-sm font-semibold mt-1">{{ $audit->created_at->format('d M Y') }}</p>
            <p class="text-xs text-gray-400">{{ $audit->created_at->format('h:i:s A') }}</p>
        </div>
    </div>

    {{-- Action & Model --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Action</p>
            <p class="text-sm mt-1">
                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                    {{ $audit->event === 'created' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $audit->event === 'updated' ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $audit->event === 'deleted' ? 'bg-red-100 text-red-700' : '' }}
                ">
                    {{ ucfirst($audit->event) }}
                </span>
            </p>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Model</p>
            <p class="text-sm font-semibold mt-1">{{ class_basename($audit->auditable_type) }}</p>
            <p class="text-xs text-gray-400">ID: {{ $audit->auditable_id }}</p>
            <p class="text-xs text-gray-500 mt-1">Name: <span class="text-gray-700 font-medium">{{ $audit->auditable?->name ?? '—' }}</span></p>
        </div>
    </div>

    {{-- IP & User Agent --}}
    <div>
        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">IP Address</p>
        <p class="text-sm font-mono mt-1">{{ $audit->ip_address ?? '—' }}</p>
    </div>

    {{-- Changes --}}
    @if($audit->old_values || $audit->new_values)
    <div>
        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider mb-2">Changes</p>
        <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-3 py-2 text-xs font-medium text-gray-500">Field</th>
                    <th class="text-left px-3 py-2 text-xs font-medium text-gray-500">Old Value</th>
                    <th class="text-left px-3 py-2 text-xs font-medium text-gray-500">New Value</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @php
                    $values = $audit->event === 'deleted' ? $audit->old_values : $audit->new_values;
                    if (!is_array($values)) $values = [];
                @endphp
                @foreach($values as $field => $value)
                @php
                    $oldVal = $audit->old_values[$field] ?? '—';
                    $newVal = $audit->event === 'deleted' ? 'deleted' : $value;
                    if ($audit->event === 'created') $oldVal = 'empty';
                @endphp
                <tr>
                    <td class="px-3 py-2 font-mono text-xs text-gray-600">{{ $field }}</td>
                    <td class="px-3 py-2 text-red-500 text-xs">
                        {{ is_array($oldVal) ? json_encode($oldVal) : $oldVal }}
                    </td>
                    <td class="px-3 py-2 text-green-600 text-xs font-medium">
                        {{ is_array($newVal) ? json_encode($newVal) : $newVal }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>