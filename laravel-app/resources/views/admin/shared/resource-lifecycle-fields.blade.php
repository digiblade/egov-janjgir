<div class="admin-form-grid">
    <div class="form-field">
        <label for="status">{{ __('ui.admin_content.status') }}</label>
        <select id="status" name="status" required>
            @foreach (['draft', 'published', 'scheduled', 'archived'] as $status)
                @if (! in_array($status, ['published', 'scheduled'], true) || auth()->user()->can($feature.'.publish') || ($item->exists && $item->status === $status))
                    <option value="{{ $status }}" @selected(old('status', $item->status ?? 'draft') === $status)>{{ __('ui.admin_content.statuses.'.$status) }}</option>
                @endif
            @endforeach
        </select>
        @error('status')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field"><label for="display_order">{{ __('ui.admin_content.display_order') }}</label><input id="display_order" name="display_order" type="number" min="0" max="100000" value="{{ old('display_order', $item->display_order ?? 0) }}" required>@error('display_order')<p class="field-error">{{ $message }}</p>@enderror</div>
    <div class="form-field"><label for="published_at">{{ __('ui.admin_content.published_at') }}</label><input id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at', $item->published_at?->format('Y-m-d\\TH:i')) }}">@error('published_at')<p class="field-error">{{ $message }}</p>@enderror</div>
    <div class="form-field"><label for="expires_at">{{ __('ui.admin_content.expires_at') }}</label><input id="expires_at" name="expires_at" type="datetime-local" value="{{ old('expires_at', $item->expires_at?->format('Y-m-d\\TH:i')) }}">@error('expires_at')<p class="field-error">{{ $message }}</p>@enderror</div>
</div>
