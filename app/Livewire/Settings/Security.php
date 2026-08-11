<?php

namespace App\Livewire\Settings;

use App\Actions\Auth\RevokeUserSession;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ThrottlesSensitiveActions;
use App\Models\User;
use Carbon\CarbonImmutable;
use Exception;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Passkeys\Actions\DeletePasskey;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Title('Security settings')]
class Security extends Component
{
    use PasswordValidationRules;
    use ThrottlesSensitiveActions;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $session_password = '';

    public bool $showSessionModal = false;

    #[Locked]
    public ?string $revokingSessionId = null;

    #[Locked]
    public bool $revokeAllOtherSessions = false;

    #[Locked]
    public bool $canManageTwoFactor;

    #[Locked]
    public bool $twoFactorEnabled;

    #[Locked]
    public bool $requiresConfirmation;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public bool $showModal = false;

    public bool $showVerificationStep = false;

    #[Validate('required|string|size:6', onUpdate: false)]
    public string $code = '';

    #[Locked]
    public bool $canManagePasskeys;

    /**
     * @var array<int, array{id: int, name: string, authenticator: string|null, created_at_diff: string, last_used_at_diff: string|null}>
     */
    #[Locked]
    public array $passkeys = [];

    public bool $showDeleteModal = false;

    #[Locked]
    public ?int $deletingPasskeyId = null;

    #[Locked]
    public string $deletingPasskeyName = '';

    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $this->canManageTwoFactor = Features::canManageTwoFactorAuthentication();

        if ($this->canManageTwoFactor) {
            if (Fortify::confirmsTwoFactorAuthentication() && is_null(auth()->user()->two_factor_confirmed_at)) {
                $disableTwoFactorAuthentication(auth()->user());
            }

            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
            $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        }

        $this->canManagePasskeys = Features::canManagePasskeys();

        if ($this->canManagePasskeys) {
            $this->loadPasskeys();
        }
    }

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        $this->throttleSensitiveAction('password-update');

        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        Flux::toast(variant: 'success', text: __('Password updated.'));
    }

    /**
     * @return array<int, array{id: string, ip_address: string, browser: string, platform: string, device_type: string, last_active: string, last_activity_at: string, is_current: bool}>
     */
    #[Computed]
    public function browserSessions(): array
    {
        $user = Auth::user();

        if (! $user instanceof User || config('session.driver') !== 'database') {
            return [];
        }

        $currentSessionId = Session::getId();
        $activeAfter = now()->subMinutes((int) config('session.lifetime', 120))->getTimestamp();
        $sessions = DB::table((string) config('session.table', 'sessions'))
            ->where('user_id', $user->id)
            ->where('last_activity', '>=', $activeAfter)
            ->latest('last_activity')
            ->get(['id', 'ip_address', 'user_agent', 'last_activity']);

        if (! $sessions->contains(fn (object $session): bool => hash_equals($currentSessionId, (string) $session->id))) {
            $sessions->prepend((object) [
                'id' => $currentSessionId,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'last_activity' => now()->getTimestamp(),
            ]);
        }

        return $sessions
            ->map(function (object $session) use ($currentSessionId): array {
                $userAgent = $this->describeUserAgent((string) ($session->user_agent ?? ''));
                $lastActivity = CarbonImmutable::createFromTimestamp((int) $session->last_activity);

                return [
                    'id' => (string) $session->id,
                    'ip_address' => (string) ($session->ip_address ?: __('Unknown IP')),
                    'browser' => $userAgent['browser'],
                    'platform' => $userAgent['platform'],
                    'device_type' => $userAgent['device_type'],
                    'last_active' => $lastActivity->diffForHumans(),
                    'last_activity_at' => $lastActivity->toIso8601String(),
                    'is_current' => hash_equals($currentSessionId, (string) $session->id),
                ];
            })
            ->values()
            ->all();
    }

    #[Computed]
    public function otherSessionCount(): int
    {
        return collect($this->browserSessions())
            ->where('is_current', false)
            ->count();
    }

    public function confirmSessionRevocation(string $sessionId): void
    {
        $session = collect($this->browserSessions())
            ->firstWhere('id', $sessionId);

        if ($session === null || $session['is_current']) {
            return;
        }

        $this->resetErrorBag();
        $this->reset('session_password');
        $this->revokingSessionId = $sessionId;
        $this->revokeAllOtherSessions = false;
        $this->showSessionModal = true;
    }

    public function confirmOtherSessionRevocation(): void
    {
        if ($this->otherSessionCount() === 0) {
            return;
        }

        $this->resetErrorBag();
        $this->reset('session_password');
        $this->revokingSessionId = null;
        $this->revokeAllOtherSessions = true;
        $this->showSessionModal = true;
    }

    public function revokeSessions(RevokeUserSession $revokeUserSession): void
    {
        $this->throttleSensitiveAction('session-revoke');

        try {
            $this->validate([
                'session_password' => $this->currentPasswordRules(),
            ]);
        } catch (ValidationException $exception) {
            $this->reset('session_password');

            throw $exception;
        }

        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        $currentSessionId = Session::getId();

        if ($this->revokeAllOtherSessions) {
            $revokedCount = $revokeUserSession->revokeOthers($user, $currentSessionId);
            $message = trans_choice('{0} No other sessions were active.|{1} One other session was signed out.|[2,*] :count other sessions were signed out.', $revokedCount, [
                'count' => $revokedCount,
            ]);
        } elseif ($this->revokingSessionId !== null) {
            $wasRevoked = $revokeUserSession->handle($user, $this->revokingSessionId, $currentSessionId);
            $message = $wasRevoked
                ? __('The selected session was signed out.')
                : __('That session is no longer active.');
        } else {
            return;
        }

        $this->closeSessionModal();

        Flux::toast(variant: 'success', text: $message);
    }

    public function closeSessionModal(): void
    {
        $this->showSessionModal = false;
        $this->revokingSessionId = null;
        $this->revokeAllOtherSessions = false;
        $this->reset('session_password');
        $this->resetErrorBag('session_password');
    }

    /** @return array{browser: string, platform: string, device_type: string} */
    private function describeUserAgent(string $userAgent): array
    {
        $browser = match (true) {
            str_contains($userAgent, 'Edg/') => 'Microsoft Edge',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Chrome/') => 'Chrome',
            str_contains($userAgent, 'Safari/') => 'Safari',
            default => __('Unknown browser'),
        };

        $platform = match (true) {
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'iPhone'), str_contains($userAgent, 'iPad') => 'iOS',
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Macintosh') => 'macOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => __('Unknown system'),
        };

        $deviceType = match (true) {
            str_contains($userAgent, 'iPad'), str_contains($userAgent, 'Tablet') => 'tablet',
            str_contains($userAgent, 'Mobile'), str_contains($userAgent, 'Android') => 'mobile',
            default => 'desktop',
        };

        return [
            'browser' => $browser,
            'platform' => $platform,
            'device_type' => $deviceType,
        ];
    }

    /**
     * Load the user's passkeys.
     */
    public function loadPasskeys(): void
    {
        $this->passkeys = Auth::user()->passkeys()
            ->select(['id', 'name', 'credential', 'created_at', 'last_used_at'])
            ->latest()
            ->get()
            ->map(fn ($passkey) => [
                'id' => $passkey->id,
                'name' => $passkey->name,
                'authenticator' => $passkey->authenticator,
                'created_at_diff' => $passkey->created_at->diffForHumans(),
                'last_used_at_diff' => $passkey->last_used_at?->diffForHumans(),
            ])
            ->all();
    }

    /**
     * Show the delete confirmation modal.
     */
    public function confirmDelete(int $passkeyId): void
    {
        $passkey = Auth::user()->passkeys()->findOrFail($passkeyId);

        $this->deletingPasskeyId = $passkey->id;
        $this->deletingPasskeyName = $passkey->name;
        $this->showDeleteModal = true;
    }

    /**
     * Delete the passkey.
     */
    public function deletePasskey(DeletePasskey $deletePasskey): void
    {
        if (! $this->deletingPasskeyId) {
            return;
        }

        $this->throttleSensitiveAction('passkey-delete');

        $user = Auth::user();
        $passkey = $user->passkeys()->findOrFail($this->deletingPasskeyId);

        $deletePasskey($user, $passkey);

        $this->closeDeleteModal();
        $this->loadPasskeys();
    }

    /**
     * Close the delete confirmation modal.
     */
    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingPasskeyId = null;
        $this->deletingPasskeyName = '';
    }

    /**
     * Enable two-factor authentication for the user.
     */
    public function enable(EnableTwoFactorAuthentication $enableTwoFactorAuthentication): void
    {
        $this->throttleSensitiveAction('two-factor-enable');

        $enableTwoFactorAuthentication(auth()->user());

        if (! $this->requiresConfirmation) {
            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
        }

        $this->loadSetupData();

        $this->showModal = true;
    }

    /**
     * Load the two-factor authentication setup data for the user.
     */
    private function loadSetupData(): void
    {
        $user = auth()->user();

        try {
            $this->qrCodeSvg = $user?->twoFactorQrCodeSvg();
            $this->manualSetupKey = decrypt($user->two_factor_secret);
        } catch (Exception) {
            $this->addError('setupData', 'Failed to fetch setup data.');

            $this->reset('qrCodeSvg', 'manualSetupKey');
        }
    }

    /**
     * Show the two-factor verification step if necessary.
     */
    public function showVerificationIfNecessary(): void
    {
        if ($this->requiresConfirmation) {
            $this->showVerificationStep = true;

            $this->resetErrorBag();

            return;
        }

        $this->closeModal();
    }

    /**
     * Confirm two-factor authentication for the user.
     */
    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->throttleSensitiveAction('two-factor-confirm');

        $this->validate();

        $confirmTwoFactorAuthentication(auth()->user(), $this->code);

        $this->closeModal();

        $this->twoFactorEnabled = true;
    }

    /**
     * Reset two-factor verification state.
     */
    public function resetVerification(): void
    {
        $this->reset('code', 'showVerificationStep');

        $this->resetErrorBag();
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disable(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $this->throttleSensitiveAction('two-factor-disable');

        $disableTwoFactorAuthentication(auth()->user());

        $this->twoFactorEnabled = false;
    }

    /**
     * Close the two-factor authentication modal.
     */
    public function closeModal(): void
    {
        $this->reset(
            'code',
            'manualSetupKey',
            'qrCodeSvg',
            'showModal',
            'showVerificationStep',
        );

        $this->resetErrorBag();

        if (! $this->requiresConfirmation) {
            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
        }
    }

    /**
     * Get the current modal configuration state.
     *
     * @return array{title: string, description: string, buttonText: string}
     */
    #[Computed]
    public function modalConfig(): array
    {
        if ($this->twoFactorEnabled) {
            return [
                'title' => __('Two-factor authentication enabled'),
                'description' => __('Two-factor authentication is now enabled. Scan the QR code or enter the setup key in your authenticator app.'),
                'buttonText' => __('Close'),
            ];
        }

        if ($this->showVerificationStep) {
            return [
                'title' => __('Verify authentication code'),
                'description' => __('Enter the 6-digit code from your authenticator app.'),
                'buttonText' => __('Continue'),
            ];
        }

        return [
            'title' => __('Enable two-factor authentication'),
            'description' => __('To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app.'),
            'buttonText' => __('Continue'),
        ];
    }
}
