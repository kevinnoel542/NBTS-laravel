@php
    $photo = $record->profile_photo_path
        ? (str_starts_with($record->profile_photo_path, 'http://') || str_starts_with($record->profile_photo_path, 'https://')
            ? $record->profile_photo_path
            : asset('storage/' . $record->profile_photo_path))
        : 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=FDEBEC&color=9F2F2D';
@endphp

<div class="nbts-user-header-avatar" title="{{ $record->name }}">
    <img src="{{ $photo }}" alt="{{ $record->name }} profile photo">
</div>
