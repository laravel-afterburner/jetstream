@component('mail::message', ['team' => $team])

You have been invited to join {{ $team->name }}!

@if(isset($invitation) && $invitation->roles && !empty($invitation->roles))
You have been invited with the following roles:
@foreach($invitation->roles as $roleSlug)
@php
$role = \App\Models\Role::where('slug', $roleSlug)->first();
@endphp
@if($role)
- **{{ $role->name }}**: {{ $role->description }}
@endif
@endforeach
@endif

@if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::registration()))
{{ __('If you do not have an account, you may create one by clicking the button below. After creating an account, you may click the invitation acceptance button in this email to accept the team invitation:') }}

@component('mail::button', ['url' => route('register')])
{{ __('Create Account') }}
@endcomponent

{{ __('If you already have an account, you may accept this invitation by clicking the button below:') }}

@else
{{ __('You may accept this invitation by clicking the button below:') }}
@endif


@component('mail::button', ['url' => $acceptUrl])
{{ __('Accept Invitation') }}
@endcomponent

If you did not expect to receive an invitation to {{ $team->name }}, you may discard this email.
@endcomponent
