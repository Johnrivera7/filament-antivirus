@php
    $engine = $this->engineStatus;
    $available = $engine['available'] ?? false;
@endphp

<x-filament-panels::page>
    <div class="fav-dashboard">
        <section class="fav-hero">
            <div class="fav-badges">
                <span class="fav-badge">
                    <span class="fav-badge-dot"></span>
                    {{ __('filament-antivirus::plugin.file_protection') }}
                </span>
                <span class="fav-badge {{ $available ? 'fav-badge--live' : 'fav-badge--warn' }}">
                    <span class="fav-badge-dot {{ $available ? 'fav-badge-dot--live' : 'fav-badge-dot--warn' }}"></span>
                    {{ $available ? __('filament-antivirus::plugin.engine_online') : __('filament-antivirus::plugin.engine_offline') }}
                </span>
                <span class="fav-badge">
                    {{ __('filament-antivirus::plugin.driver') }}: {{ $engine['driver'] ?? '—' }}
                </span>
            </div>

            <div class="fav-hero-top">
                <div>
                    <div class="fav-hero-title">
                        <x-filament::icon icon="heroicon-o-shield-exclamation" class="h-6 w-6 text-danger-500" />
                        {{ __('filament-antivirus::plugin.dashboard_title') }}
                    </div>
                    <p class="fav-hero-sub">
                        {{ __('filament-antivirus::plugin.engine_message', ['message' => $engine['message'] ?? '—']) }}
                    </p>
                </div>

                <div class="fav-engine-block">
                    <div class="fav-engine-version">{{ $engine['version'] ?? '—' }}</div>
                    <div class="fav-engine-label">{{ __('filament-antivirus::plugin.clamav_version') }}</div>
                </div>
            </div>

            <div class="fav-stats">
                <div class="fav-stat fav-stat--warn">
                    <div class="fav-stat-k">{{ __('filament-antivirus::plugin.stat_infected_24h') }}</div>
                    <div class="fav-stat-v">{{ $this->infected24h }}</div>
                </div>
                <div class="fav-stat fav-stat--info">
                    <div class="fav-stat-k">{{ __('filament-antivirus::plugin.stat_incidents_24h') }}</div>
                    <div class="fav-stat-v">{{ $this->incidents24h }}</div>
                </div>
                <div class="fav-stat fav-stat--danger">
                    <div class="fav-stat-k">{{ __('filament-antivirus::plugin.stat_quarantined') }}</div>
                    <div class="fav-stat-v">{{ $this->quarantinedTotal }}</div>
                </div>
            </div>
        </section>

        <section class="fav-incidents">
            <h2 class="fav-section-title">{{ __('filament-antivirus::plugin.recent_incidents') }}</h2>

            @if ($this->recentIncidents === [])
                <div class="fav-empty">{{ __('filament-antivirus::plugin.no_incidents') }}</div>
            @else
                <div class="fav-table-wrap">
                    <table class="fav-table">
                        <thead>
                            <tr>
                                <th>{{ __('filament-antivirus::plugin.col_when') }}</th>
                                <th>{{ __('filament-antivirus::plugin.col_verdict') }}</th>
                                <th>{{ __('filament-antivirus::plugin.col_signature') }}</th>
                                <th>{{ __('filament-antivirus::plugin.col_path') }}</th>
                                <th>{{ __('filament-antivirus::plugin.col_source') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->recentIncidents as $incident)
                                <tr>
                                    <td>{{ $incident->created_at?->locale(app()->getLocale())->isoFormat('LLL') }}</td>
                                    <td>
                                        <span class="fav-tag fav-tag--{{ $incident->verdict === 'infected' ? 'bad' : 'ok' }}">
                                            {{ $incident->verdict }}
                                        </span>
                                    </td>
                                    <td>{{ $incident->signature ?? '—' }}</td>
                                    <td class="fav-path" title="{{ $incident->original_path }}">{{ $incident->original_path }}</td>
                                    <td>{{ $incident->source }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>

</x-filament-panels::page>
