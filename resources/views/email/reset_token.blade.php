@component('mail::message')
# You requested a password reset token

Use this token to reset your {{ config('app.name') }} password: <b>{{ $token }}</b><br>

NB: This token will expire in 2 hours, and you will be required to request a new one if you still
want to reset your password.<br>

Ignore this message if you did not request for a password reset.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
