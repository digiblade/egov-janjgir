<div class="admin-form-grid">
    <div class="form-field">
        <label for="title_en">{{ __('ui.admin_content.title_en') }}</label>
        <input id="title_en" name="title_en" value="{{ old('title_en', $item->title_en) }}" required maxlength="255" autocomplete="off" @if ($errors->has('title_en')) aria-invalid="true" aria-describedby="title-en-error" @endif>
        @error('title_en')<p id="title-en-error" class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field">
        <label for="title_hi">{{ __('ui.admin_content.title_hi') }}</label>
        <input id="title_hi" name="title_hi" value="{{ old('title_hi', $item->title_hi) }}" required maxlength="255" lang="hi" autocomplete="off" @if ($errors->has('title_hi')) aria-invalid="true" aria-describedby="title-hi-error" @endif>
        @error('title_hi')<p id="title-hi-error" class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field">
        <label for="slug">{{ __('ui.admin_content.slug') }}</label>
        <input id="slug" name="slug" value="{{ old('slug', $item->slug) }}" maxlength="180" pattern="[A-Za-z0-9_-]*" @if ($errors->has('slug')) aria-invalid="true" aria-describedby="slug-help slug-error" @else aria-describedby="slug-help" @endif>
        <p id="slug-help" class="field-help">{{ __('ui.admin_content.slug_help') }}</p>
        @error('slug')<p id="slug-error" class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field">
        <label for="priority">{{ __('ui.admin_content.priority') }}</label>
        <select id="priority" name="priority" required @if ($errors->has('priority')) aria-invalid="true" aria-describedby="priority-error" @endif>
            @foreach (['low', 'normal', 'high', 'urgent'] as $priority)
                <option value="{{ $priority }}" @selected(old('priority', $item->priority ?? 'normal') === $priority)>{{ __('ui.admin_content.priorities.'.$priority) }}</option>
            @endforeach
        </select>
        @error('priority')<p id="priority-error" class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field form-field--wide">
        <label for="description_en">{{ __('ui.admin_content.description_en') }}</label>
        <textarea id="description_en" name="description_en" rows="6" maxlength="20000" lang="en" @if ($errors->has('description_en')) aria-invalid="true" aria-describedby="description-en-error" @endif>{{ old('description_en', $item->description_en) }}</textarea>
        @error('description_en')<p id="description-en-error" class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field form-field--wide">
        <label for="description_hi">{{ __('ui.admin_content.description_hi') }}</label>
        <textarea id="description_hi" name="description_hi" rows="6" maxlength="20000" lang="hi" @if ($errors->has('description_hi')) aria-invalid="true" aria-describedby="description-hi-error" @endif>{{ old('description_hi', $item->description_hi) }}</textarea>
        @error('description_hi')<p id="description-hi-error" class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field">
        <label for="status">{{ __('ui.admin_content.status') }}</label>
        <select id="status" name="status" required @if ($errors->has('status')) aria-invalid="true" aria-describedby="status-error" @endif>
            @foreach (['draft', 'published', 'scheduled', 'archived'] as $status)
                @if (! in_array($status, ['published', 'scheduled'], true) || auth()->user()->can($item instanceof \App\Models\Notice ? 'notices.publish' : 'announcements.publish') || ($item->exists && $item->status === $status))
                    <option value="{{ $status }}" @selected(old('status', $item->status ?? 'draft') === $status)>{{ __('ui.admin_content.statuses.'.$status) }}</option>
                @endif
            @endforeach
        </select>
        @error('status')<p id="status-error" class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field">
        <label for="display_order">{{ __('ui.admin_content.display_order') }}</label>
        <input id="display_order" name="display_order" type="number" min="0" max="100000" value="{{ old('display_order', $item->display_order ?? 0) }}" required @if ($errors->has('display_order')) aria-invalid="true" aria-describedby="display-order-error" @endif>
        @error('display_order')<p id="display-order-error" class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field">
        <label for="published_at">{{ __('ui.admin_content.published_at') }}</label>
        <input id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at', $item->published_at?->format('Y-m-d\\TH:i')) }}" @if ($errors->has('published_at')) aria-invalid="true" aria-describedby="published-at-error" @endif>
        @error('published_at')<p id="published-at-error" class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field">
        <label for="expires_at">{{ __('ui.admin_content.expires_at') }}</label>
        <input id="expires_at" name="expires_at" type="datetime-local" value="{{ old('expires_at', $item->expires_at?->format('Y-m-d\\TH:i')) }}" @if ($errors->has('expires_at')) aria-invalid="true" aria-describedby="expires-at-error" @endif>
        @error('expires_at')<p id="expires-at-error" class="field-error">{{ $message }}</p>@enderror
    </div>
</div>
