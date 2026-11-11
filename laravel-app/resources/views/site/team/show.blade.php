@extends('layouts.public')
@section('content')
    <section class="page-hero page-hero--small"><div class="site-container"><p class="eyebrow">{{ $teamMember->category->localized('name') }}</p><h1>{{ $teamMember->localized('full_name') }}</h1></div></section>
    <article class="site-container content-page detail-page team-profile">@if ($teamMember->photograph?->visibility === 'public')<img class="team-profile__photo" src="{{ route('media.public', $teamMember->photograph) }}" alt="{{ $teamMember->localized('full_name') }}">@endif
        <p class="team-profile__designation">{{ $teamMember->localized('designation') }}</p>@if ($teamMember->office_block)<p>{{ $teamMember->office_block }}</p>@endif
        @if ($teamMember->registration_number)<p>{{ __('ui.catalog.reference') }}: {{ $teamMember->registration_number }}</p>@endif
        @if ($teamMember->email_public && $teamMember->email)<p><a href="mailto:{{ $teamMember->email }}">{{ $teamMember->email }}</a></p>@endif
        @if ($teamMember->contact_number_public && $teamMember->contact_number)<p><a href="tel:{{ $teamMember->contact_number }}">{{ $teamMember->contact_number }}</a></p>@endif
        @if ($teamMember->localized('responsibilities'))<section class="detail-section"><h2>{{ __('ui.catalog.responsibilities') }}</h2><div class="detail-copy">{{ $teamMember->localized('responsibilities') }}</div></section>@endif
        <p><a class="text-link" href="{{ route('team.index') }}">← {{ __('ui.catalog.back') }}</a></p>
    </article>
@endsection
