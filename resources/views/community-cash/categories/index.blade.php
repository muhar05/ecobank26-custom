<x-layouts.dashboard title="Kategori Dana">
    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Kategori Dana</h2>
            <a href="{{ route('community-cash.categories.create') }}" class="bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">Tambah Kategori</a>
        </div>

        @if(session('success'))
            <div class="p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg">{{ session('success') }}</div>
        @endif

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Nama</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Target</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Terkumpul</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Progress</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($categories as $category)
                            @php
                                $collected = $category->contributions_sum_amount ?? 0;
                                $target = $category->target_amount;
                                $hasTarget = $target && $target > 0;
                                $percentage = $hasTarget ? min(round(($collected / $target) * 100), 100) : null;                            
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $category->name }}</div>
                                    @if($category->description)
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $category->description }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-right text-slate-700 dark:text-slate-300">
                                    @if($hasTarget)
                                        Rp {{ number_format($target, 0, ',', '.') }}
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-medium text-emerald-700 dark:text-emerald-400">
                                    Rp {{ number_format($collected, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($hasTarget)
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full {{ $percentage >= 100 ? 'bg-emerald-500' : 'bg-emerald-400' }}" style="width: {{ min($percentage, 100) }}%"></div>
                                            </div>
                                            <span class="text-xs font-medium text-slate-600 dark:text-slate-300 w-10 text-right">{{ $percentage }}%</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 dark:text-slate-500">Belum ada target</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $category->is_active ? 'bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-300' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-300' }}">
                                        {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    <a href="{{ route('community-cash.categories.edit', $category) }}" class="text-emerald-700 dark:text-emerald-400 hover:underline">Edit</a>
                                    <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('community-cash.categories.destroy', $category) }}'})" class="text-red-600 dark:text-red-400 hover:underline">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-4 text-center text-slate-500 dark:text-slate-400">Belum ada kategori dana.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">
            {{ $categories->links() }}
        </div>
    </div>

    <x-confirm-delete-modal />
</x-layouts.dashboard>
