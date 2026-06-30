@php
    $state = $this->form->getRawState();
    $title = $state['title'] ?? 'Notification title';
    $body = $state['body'] ?? 'Your message preview will appear here as you type.';
    $type = $state['type'] ?? 'custom';
    $priority = $state['priority'] ?? 'normal';
    $actionUrl = $state['action_url'] ?? null;
    $firebase = $this->firebaseStatus;
@endphp

<x-filament-panels::page>
    <div class="nbts-notification-page">
        <section class="nbts-notification-metrics">
            <article>
                <span>Audience</span>
                <strong>{{ number_format($this->recipientEstimate) }}</strong>
                <small>selected recipient accounts</small>
            </article>
            <article>
                <span>Push Ready</span>
                <strong>{{ number_format($this->pushReadyEstimate) }}</strong>
                <small>users with phone tokens</small>
            </article>
            <article>
                <span>Firebase</span>
                <strong>{{ $firebase['enabled'] && $firebase['credentials_found'] ? 'Ready' : 'Check' }}</strong>
                <small>{{ $firebase['project_id'] ?: 'No project configured' }}</small>
            </article>
            <article>
                <span>Tokens</span>
                <strong>{{ number_format($firebase['registered_tokens']) }}</strong>
                <small>registered FCM tokens</small>
            </article>
        </section>

        <div class="nbts-notification-layout">
            <form wire:submit="send" class="nbts-notification-card">
                <div class="nbts-notification-card__header">
                    <div>
                        <p>Compose</p>
                        <h3>Send app notification</h3>
                    </div>
                </div>

                <div class="nbts-notification-card__body">
                    {{ $this->form }}
                </div>

                <div class="nbts-notification-card__footer">
                    <x-filament::button type="submit" icon="heroicon-o-paper-airplane">
                        Send notification
                    </x-filament::button>
                </div>
            </form>

            <aside class="nbts-notification-side">
                <section class="nbts-notification-card">
                    <div class="nbts-notification-card__header">
                        <div>
                            <p>Preview</p>
                            <h3>Mobile message</h3>
                        </div>
                        <span class="nbts-mini-badge">{{ str($priority)->title() }}</span>
                    </div>

                    <div class="nbts-phone-preview">
                        <div class="nbts-phone-preview__icon">N</div>
                        <div>
                            <strong>{{ $title }}</strong>
                            <p>{{ $body }}</p>
                            <small>{{ str($type)->replace('_', ' ')->title() }}{{ $actionUrl ? ' · ' . $actionUrl : '' }}</small>
                        </div>
                    </div>
                </section>

                <section class="nbts-notification-card">
                    <div class="nbts-notification-card__header">
                        <div>
                            <p>Recent</p>
                            <h3>Last sent messages</h3>
                        </div>
                    </div>

                    <div class="nbts-notification-list">
                        @forelse ($this->recentNotifications as $notification)
                            <article>
                                <div>
                                    <strong>{{ $notification->title }}</strong>
                                    <p>{{ str($notification->body)->limit(90) }}</p>
                                    <small>{{ $notification->user?->name ?? 'Unknown user' }} · {{ $notification->created_at->diffForHumans() }}</small>
                                </div>
                                <span class="nbts-mini-badge">{{ str($notification->type)->title() }}</span>
                            </article>
                        @empty
                            <div class="nbts-notification-empty">No notifications have been sent yet.</div>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-filament-panels::page>
