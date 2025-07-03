@component('mail::message')
# Email verification token

Hi, thank you for registering to {{ config('app.name') }}, we look forward to giving you a good experience.<br>

Your email verification token is: <b>{{ $token }}</b>

Thanks,<br>
{{ config('app.name') }}
@endcomponent
